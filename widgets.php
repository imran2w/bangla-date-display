<?php
/**
 * Bangla Date Display Widget
 *
 * @package Bangla_Date_Display
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the widget.
 */
function bddp_register_widgets() {
	register_widget( 'Widget_Bangla_Date_Display' );
	register_widget( 'Widget_Ajax_Archive_Calendar' );
}
add_action( 'widgets_init', 'bddp_register_widgets' );

/**
 * Widget class for Bangla Date Display.
 */
class Widget_Bangla_Date_Display extends WP_Widget {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			'bangla_date_display',
			__( 'Bangla Date Display', 'bddp' ),
			array(
				'description' => __( 'Displays Bangla, Gregorian & Hijri date, time, day and season name.', 'bddp' ),
				'classname'   => 'widget-bangla-date-display',
			)
		);
	}

	/**
	 * Default widget arguments.
	 *
	 * @var array
	 */
	public $args = array(
		'before_title'  => '<h4 class="widgettitle">',
		'after_title'   => '</h4>',
		'before_widget' => '<div class="widget-wrap">',
		'after_widget'  => '</div></div>',
	);

	/**
	 * Outputs the widget content.
	 *
	 * @param array $args     Display arguments.
	 * @param array $instance Settings for the current widget instance.
	 */
	public function widget( $args, $instance ) {
		$bool_keys = array( 'day', 'time', 'en_date', 'hijri_date', 'bn_date', 'season' );
		foreach ( $bool_keys as $key ) {
			if ( empty( $instance[ $key ] ) ) {
				$instance[ $key ] = '';
			}
		}

		$plugin = Bangla_Date_Display::instance();

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $args['before_widget'];
		
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}

		echo '<div class="textwidget">';
		echo '<ul>';

		if ( '1' === $instance['day'] || '1' === $instance['time'] ) {
			echo '<li>';
		}
		if ( '1' === $instance['day'] ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $plugin->render_bangla_day();
		}
		if ( '1' === $instance['time'] ) {
			echo ' (';
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $plugin->render_bangla_clock();
			echo ')';
		}
		if ( '1' === $instance['day'] || '1' === $instance['time'] ) {
			echo '</li>';
		}

		if ( '1' === $instance['en_date'] ) {
			echo '<li>';
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $plugin->render_gregorian_date();
			echo '</li>';
		}

		if ( '1' === $instance['hijri_date'] ) {
			echo '<li>';
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $plugin->render_hijri_date();
			echo '</li>';
		}

		if ( '1' === $instance['bn_date'] || '1' === $instance['season'] ) {
			echo '<li>';
		}
		if ( '1' === $instance['bn_date'] ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $plugin->render_bangla_date();
		}
		if ( '1' === $instance['season'] ) {
			echo ' (';
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $plugin->render_bangla_season();
			echo ')';
		}
		if ( '1' === $instance['bn_date'] || '1' === $instance['season'] ) {
			echo '</li>';
		}

		echo '</ul>';
		echo '</div>';
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $args['after_widget'];
	}
	
	/**
	 * Get field attributes for form rendering.
	 *
	 * @param array  $instance Widget instance settings.
	 * @param string $key      Field key.
	 * @return array Field attributes.
	 */
	private function get_field_attr( $instance, $key ) {
		$instance_defaults = array(
			'title'      => 'আজকের দিন-তারিখ',
			'day'        => '1',
			'time'       => '1',
			'en_date'    => '1',
			'hijri_date' => '1',
			'bn_date'    => '1',
			'season'     => '1',
		);

		if ( ! isset( $instance[ $key ] ) ) {
			$instance[ $key ] = $instance_defaults[ $key ];
		}

		return array(
			'value' => ! empty( $instance[ $key ] ) ? $instance[ $key ] : '',
			'id'    => esc_attr( $this->get_field_id( $key ) ),
			'name'  => esc_attr( $this->get_field_name( $key ) ),
		);
	}

	/**
	 * Outputs the settings form for the widget.
	 *
	 * @param array $instance Current widget instance settings.
	 */
	public function form( $instance ) {
		$title      = $this->get_field_attr( $instance, 'title' );
		$day        = $this->get_field_attr( $instance, 'day' );
		$time       = $this->get_field_attr( $instance, 'time' );
		$en_date    = $this->get_field_attr( $instance, 'en_date' );
		$hijri_date = $this->get_field_attr( $instance, 'hijri_date' );
		$bn_date    = $this->get_field_attr( $instance, 'bn_date' );
		$season     = $this->get_field_attr( $instance, 'season' );
		?>
		<p>
			<label for="<?php echo esc_attr( $title['id'] ); ?>">
				<?php esc_html_e( 'Title:', 'bddp' ); ?>
			</label>
			<input class="widefat" id="<?php echo esc_attr( $title['id'] ); ?>" name="<?php echo esc_attr( $title['name'] ); ?>" type="text" value="<?php echo esc_attr( $title['value'] ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $day['id'] ); ?>">
				<input type="checkbox" id="<?php echo esc_attr( $day['id'] ); ?>" name="<?php echo esc_attr( $day['name'] ); ?>" value="1" <?php checked( $day['value'], '1' ); ?> />
				<?php esc_html_e( 'Day', 'bddp' ); ?>
			</label>
		</p>
		<p>
			<label for="<?php echo esc_attr( $time['id'] ); ?>">
				<input type="checkbox" id="<?php echo esc_attr( $time['id'] ); ?>" name="<?php echo esc_attr( $time['name'] ); ?>" value="1" <?php checked( $time['value'], '1' ); ?> />
				<?php esc_html_e( 'Time', 'bddp' ); ?>
			</label>
		</p>
		<p>
			<label for="<?php echo esc_attr( $en_date['id'] ); ?>">
				<input type="checkbox" id="<?php echo esc_attr( $en_date['id'] ); ?>" name="<?php echo esc_attr( $en_date['name'] ); ?>" value="1" <?php checked( $en_date['value'], '1' ); ?> />
				<?php esc_html_e( 'Gregorian Date', 'bddp' ); ?>
			</label>
		</p>
		<p>
			<label for="<?php echo esc_attr( $hijri_date['id'] ); ?>">
				<input type="checkbox" id="<?php echo esc_attr( $hijri_date['id'] ); ?>" name="<?php echo esc_attr( $hijri_date['name'] ); ?>" value="1" <?php checked( $hijri_date['value'], '1' ); ?> />
				<?php esc_html_e( 'Hijri Date', 'bddp' ); ?>
			</label>
		</p>
		<p>
			<label for="<?php echo esc_attr( $bn_date['id'] ); ?>">
				<input type="checkbox" id="<?php echo esc_attr( $bn_date['id'] ); ?>" name="<?php echo esc_attr( $bn_date['name'] ); ?>" value="1" <?php checked( $bn_date['value'], '1' ); ?> />
				<?php esc_html_e( 'Bangla Date', 'bddp' ); ?>
			</label>
		</p>
		<p>
			<label for="<?php echo esc_attr( $season['id'] ); ?>">
				<input type="checkbox" id="<?php echo esc_attr( $season['id'] ); ?>" name="<?php echo esc_attr( $season['name'] ); ?>" value="1" <?php checked( $season['value'], '1' ); ?> />
				<?php esc_html_e( 'Season Name', 'bddp' ); ?>
			</label>
		</p>
		<?php
	}

	/**
	 * Updates a particular instance of the widget.
	 *
	 * @param array $new_instance New settings for this instance.
	 * @param array $old_instance Old settings for this instance.
	 * @return array Updated settings.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();

		$instance['title']      = ! empty( $new_instance['title'] ) ? sanitize_text_field( $new_instance['title'] ) : 'আজকের দিন-তারিখ';
		$instance['day']        = ! empty( $new_instance['day'] ) ? '1' : '0';
		$instance['time']       = ! empty( $new_instance['time'] ) ? '1' : '0';
		$instance['en_date']    = ! empty( $new_instance['en_date'] ) ? '1' : '0';
		$instance['hijri_date'] = ! empty( $new_instance['hijri_date'] ) ? '1' : '0';
		$instance['bn_date']    = ! empty( $new_instance['bn_date'] ) ? '1' : '0';
		$instance['season']     = ! empty( $new_instance['season'] ) ? '1' : '0';

		return $instance;
	}
}

/**
 * Widget class for Ajax Archive Calendar.
 */
class Widget_Ajax_Archive_Calendar extends WP_Widget {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			'ajax_archive_calendar',
			__( 'Ajax Archive Calendar', 'bddp' ),
			array(
				'description' => __( 'Displays an AJAX-powered archive calendar for posts.', 'bddp' ),
				'classname'   => 'widget-ajax-archive-calendar',
			)
		);
	}

	/**
	 * Outputs the widget content.
	 *
	 * @param array $args     Display arguments.
	 * @param array $instance Settings for the current widget instance.
	 */
	public function widget( $args, $instance ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $args['before_widget'];

		if ( ! empty( $instance['title'] ) ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}

		if ( function_exists( 'cal_widget_render' ) ) {
			$language   = ! empty( $instance['language'] ) && in_array( $instance['language'], array( 'bn', 'en' ), true ) ? $instance['language'] : 'bn';
			$start_year = ! empty( $instance['ac_start_year'] ) ? absint( $instance['ac_start_year'] ) : (int) gmdate( 'Y' );

			cal_widget_render(
				array(
					'language'   => $language,
					'start_year' => $start_year,
				)
			);
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $args['after_widget'];
	}

	/**
	 * Outputs the settings form for the widget.
	 *
	 * @param array $instance Current widget instance settings.
	 */
	public function form( $instance ) {
		$title      = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Archive Calendar', 'bddp' );
		$language   = ! empty( $instance['language'] ) && in_array( $instance['language'], array( 'bn', 'en' ), true ) ? $instance['language'] : 'bn';
		$start_year = ! empty( $instance['ac_start_year'] ) ? absint( $instance['ac_start_year'] ) : (int) gmdate( 'Y' );
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
				<?php esc_html_e( 'Title:', 'bddp' ); ?>
			</label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'language' ) ); ?>">
				<?php esc_html_e( 'Language:', 'bddp' ); ?>
			</label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'language' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'language' ) ); ?>">
				<option value="bn" <?php selected( $language, 'bn' ); ?>><?php esc_html_e( 'Bangla', 'bddp' ); ?></option>
				<option value="en" <?php selected( $language, 'en' ); ?>><?php esc_html_e( 'English', 'bddp' ); ?></option>
			</select>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'ac_start_year' ) ); ?>">
				<?php esc_html_e( 'Start Year:', 'bddp' ); ?>
			</label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'ac_start_year' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'ac_start_year' ) ); ?>" type="number" min="1" step="1" value="<?php echo esc_attr( $start_year ); ?>">
		</p>
		<?php
	}

	/**
	 * Updates a particular instance of the widget.
	 *
	 * @param array $new_instance New settings for this instance.
	 * @param array $old_instance Old settings for this instance.
	 * @return array Updated settings.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance          = array();
		$instance['title'] = ! empty( $new_instance['title'] ) ? sanitize_text_field( $new_instance['title'] ) : __( 'Archive Calendar', 'bddp' );
		$instance['language'] = ( ! empty( $new_instance['language'] ) && in_array( $new_instance['language'], array( 'bn', 'en' ), true ) ) ? $new_instance['language'] : 'bn';

		$start_year_raw = ! empty( $new_instance['ac_start_year'] ) ? absint( $new_instance['ac_start_year'] ) : 0;
		$instance['ac_start_year'] = $start_year_raw > 0 ? $start_year_raw : (int) gmdate( 'Y' );

		return $instance;
	}
}