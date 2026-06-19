<?php
/**
 * Ajax Calendar Widget
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/* ── Constants ──────────────────────────────────────── */

define( 'CAL_WIDGET_DIR', plugin_dir_path( __FILE__ ) );
define( 'CAL_WIDGET_URL', plugin_dir_url( __FILE__ ) );
define( 'CAL_WIDGET_VER', '1.0.0' );

/* ── Enqueue assets ─────────────────────────────────── */

add_action( 'wp_enqueue_scripts', 'cal_widget_register_assets' );

function cal_widget_register_assets() {
    wp_register_style(
        'ajax-cal-widget',
        CAL_WIDGET_URL . 'assets/css/calendar.css',
        [],
        CAL_WIDGET_VER
    );

    wp_register_script(
        'ajax-cal-widget',
        CAL_WIDGET_URL . 'assets/js/calendar.js',
        [],
        CAL_WIDGET_VER,
        true // load in footer
    );
}

function cal_widget_enqueue_assets() {
    wp_enqueue_style( 'ajax-cal-widget' );
    wp_enqueue_script( 'ajax-cal-widget' );

    // Passed to JS as window.calWidgetConfig.
    wp_localize_script(
        'ajax-cal-widget',
        'calWidgetConfig',
        [
            'url'    => admin_url( 'admin-ajax.php' ),
            'nonce'  => wp_create_nonce( 'cal_widget_nonce' ),
            'action' => 'cal_get_post_days',
            'spinnerUrl' => admin_url( 'images/spinner.gif' ),
        ]
    );
}

/* ── Template helper ────────────────────────────────── */

add_shortcode( 'ajax_calendar', 'cal_widget_shortcode_render' );

function cal_widget_shortcode_render( $atts = [] ) {
    $atts = shortcode_atts(
        [
            'language'   => 'en',
            'start_year' => (int) gmdate( 'Y' ) - 10,
        ],
        $atts,
        'ajax_calendar'
    );

    ob_start();
    cal_widget_render(
        [
            'language'   => $atts['language'],
            'start_year' => $atts['start_year'],
        ]
    );

    return ob_get_clean();
}

function cal_widget_render( $settings = [] ) {
    cal_widget_enqueue_assets();

    $defaults = [
        'language'   => 'bn',
        'start_year' => (int) gmdate( 'Y' ) - 10,
    ];

    $settings = wp_parse_args( $settings, $defaults );
    $language = in_array( $settings['language'], [ 'bn', 'en' ], true ) ? $settings['language'] : 'bn';
    $start_year = absint( $settings['start_year'] );
    if ( 0 === $start_year ) {
        $start_year = (int) gmdate( 'Y' ) - 10;
    }

    ?>
    <div class="ajax-cal-widget" data-language="<?php echo esc_attr( $language ); ?>" data-start-year="<?php echo esc_attr( $start_year ); ?>">
        <div class="cal-dropdowns">
            <select class="cal-month" aria-label="<?php esc_attr_e( 'Select month', 'cal-widget' ); ?>"></select>
            <select class="cal-year"  aria-label="<?php esc_attr_e( 'Select year',  'cal-widget' ); ?>"></select>
        </div>
        <div class="cal-header">
            <button class="cal-prev" aria-label="<?php esc_attr_e( 'Previous month', 'cal-widget' ); ?>">&#8249;</button>
            <span class="cal-title-wrap">
                <span class="cal-title"></span>
            </span>
            <button class="cal-next" aria-label="<?php esc_attr_e( 'Next month', 'cal-widget' ); ?>">&#8250;</button>
        </div>
        <div class="cal-body-wrap">
            <table class="cal-table" role="grid" aria-label="<?php esc_attr_e( 'Monthly calendar', 'cal-widget' ); ?>">
                <thead>
                    <tr>
                        <th scope="col" abbr="Sunday">Su</th>
                        <th scope="col" abbr="Monday">Mo</th>
                        <th scope="col" abbr="Tuesday">Tu</th>
                        <th scope="col" abbr="Wednesday">We</th>
                        <th scope="col" abbr="Thursday">Th</th>
                        <th scope="col" abbr="Friday">Fr</th>
                        <th scope="col" abbr="Saturday">Sa</th>
                    </tr>
                </thead>
                <tbody class="cal-body"></tbody>
            </table>
            <span class="cal-spinner" aria-hidden="true"></span>
        </div>
    </div>
    <?php
}

/* ── AJAX handler ───────────────────────────────────── */

add_action( 'wp_ajax_cal_get_post_days',        'cal_widget_get_post_days' );
add_action( 'wp_ajax_nopriv_cal_get_post_days', 'cal_widget_get_post_days' );

function cal_widget_get_post_days() {
    check_ajax_referer( 'cal_widget_nonce', 'nonce' );

    $year  = isset( $_POST['year'] )  ? intval( $_POST['year'] )  : 0;
    $month = isset( $_POST['month'] ) ? intval( $_POST['month'] ) : 0;

    if ( ! $year || $month < 1 || $month > 12 ) {
        wp_send_json_error( 'Invalid parameters.' );
    }

    $first_day = sprintf( '%04d-%02d-01', $year, $month );
    $last_day  = gmdate( 'Y-m-t', mktime( 0, 0, 0, $month, 1, $year ) );

    $query = new WP_Query( [
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'no_found_rows'  => true, // skip pagination count for performance
        'date_query'     => [ [
            'after'     => $first_day . ' 00:00:00',
            'before'    => $last_day  . ' 23:59:59',
            'inclusive' => true,
        ] ],
    ] );

    $post_days = [];

    if ( $query->have_posts() ) {
        foreach ( $query->posts as $post ) {
            // post_date is always "Y-m-d H:i:s" — extract day directly
            $day = (int) substr( $post->post_date, 8, 2 );

            $post_count[ $day ] = ( $post_count[ $day ] ?? 0 ) + 1;

            if ( $post_count[ $day ] === 1 ) {
                // First post of the day — store its permalink for now
                $post_days[ $day ] = get_permalink( $post->ID );
            } elseif ( $post_count[ $day ] === 2 ) {
                // Second post found — switch to the day archive link
                $post_days[ $day ] = get_day_link( $year, $month, $day );
            }
            // 3+ posts: day archive link already set, nothing to update
        }
    }

    wp_reset_postdata();
    wp_send_json_success( (object) $post_days );
}
