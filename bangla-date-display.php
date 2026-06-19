<?php
/*
Plugin Name: Bangla Date Display
Plugin URI: https://imran.link
Description: Displays Bangla, Gregorian and Hijri date in bangla language via widgets and shortcodes! Options for displaying post/page's time, date, comment count, archive calendar etc in Bangla language.
Author: ALI IMRAN
Version: 10.0.0
Author URI: https://imran.link
*/

/*
This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or ( at your option) any later version. This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of ERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details. You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA. Online: http://www.gnu.org/licenses/gpl.txt;
*/

// Bismillah...

defined( 'ABSPATH' ) or die( 'Stop! You can not do this!' );

define( 'BDDP_VERSION', '10.0.0' );

require __DIR__ . '/ajax-archive-calendar.php';

class Bangla_Date_Display {

	private static ?self $instance = null;
	private array $options;

	private function __construct() {
		$defaults = [
			'cal_wgt'         => '0',
			'trans_dt'        => '0',
			'trans_cmnt'      => '0',
			'trans_num'       => '0',
			'trans_cal'       => '0',
			'bangla_calendar' => 'BD',
			'hijri_calendar'  => 'umalqura',
			'ord_suffix' => '1',
			'separator' => ', ',
			'last_word' => '1',
			'hijri_adjust' => '0',
		];
		$raw = get_option( 'bddp_options', $defaults );
		$this->options = is_array( $raw ) ? $raw : $defaults;

		$this->register_hooks();
	}

	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function register_hooks(): void {
		add_shortcode( 'bangla_time',    [ $this, 'render_bangla_clock' ] );
		add_shortcode( 'bangla_day',     [ $this, 'render_bangla_day' ] );
		add_shortcode( 'bangla_date',    [ $this, 'render_bangla_date' ] );
		add_shortcode( 'bangla_season',  [ $this, 'render_bangla_season' ] );
		add_shortcode( 'gregorian_date', [ $this, 'render_gregorian_date' ] );
		add_shortcode( 'english_date',   [ $this, 'render_gregorian_date' ] ); // backward compatibility
		add_shortcode( 'hijri_date',     [ $this, 'render_hijri_date' ] );

		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), [ $this, 'action_links' ] );

		if ( ! is_admin() || wp_doing_ajax() ) {
			if ( ( $this->options['trans_dt'] ?? '0' ) === '1' ) {
				if ( function_exists( 'wp_date' ) ) {
					add_filter( 'wp_date', [ $this, 'en_to_bn' ], 10, 2 ); // WP 5.3+
				} else {
					add_filter( 'date_i18n', [ $this, 'en_to_bn' ], 10, 2 ); // WP 5.2-
				}
			}
			if ( ( $this->options['trans_cmnt'] ?? '0' ) === '1' ) {
				add_filter( 'comments_number', [ $this, 'en_to_bn' ] );
				add_filter( 'get_comment_count', [ $this, 'en_to_bn' ] );
			}
			if ( ( $this->options['trans_num'] ?? '0' ) === '1' ) {
				add_filter( 'number_format_i18n', [ $this, 'en_to_bn' ], 10, 1 );
			}
		}
	}

	public function en_to_bn( string $str ): string {
		$en_months = [ 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec' ];
		$en_weeks  = [ 'Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Sat', 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri' ];
		$bn_months = [ 'জানুয়ারি', 'ফেব্রুয়ারি', 'মার্চ', 'এপ্রিল', 'মে', 'জুন', 'জুলাই', 'আগস্ট', 'সেপ্টেম্বর', 'অক্টোবর', 'নভেম্বর', 'ডিসেম্বর', 'জানু', 'ফেব্রু', 'মার্চ', 'এপ্রি', 'মে', 'জুন', 'জুলা', 'আগ', 'সেপ্টে', 'অক্টো', 'নভে', 'ডিসে' ];
		$bn_weeks  = [ 'শনিবার', 'রবিবার', 'সোমবার', 'মঙ্গলবার', 'বুধবার', 'বৃহস্পতিবার', 'শুক্রবার', 'শনি', 'রবি', 'সোম', 'মঙ্গল', 'বুধ', 'বৃহঃ', 'শুক্র' ];

		$search  = array_merge( $en_months, $en_weeks, [ 'am', 'pm', 'st', 'th', 'nd', 'rd', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9' ] );
		$replace = array_merge( $bn_months, $bn_weeks, [ 'পূর্বাহ্ণ', 'অপরাহ্ণ', '', '', '', '', '০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯' ] );

		return str_ireplace( $search, $replace, $str );
	}

	public function render_bangla_clock(): string {
		$this->enqueue_datetime_assets();
		return '<span class="bangla-time"></span>';
	}

	public function render_bangla_day(): string {
		$this->enqueue_datetime_assets();
		return '<span class="bangla-day"></span>';
	}

	public function render_bangla_date(): string {
		$this->enqueue_datetime_assets();
		$bangla_calendar = $this->options['bangla_calendar'] ?? 'BD';
		$separator = $this->options['separator'] ?? ', ';
		$ord_suffix = $this->options['ord_suffix'] ?? '0';
		$last_word = $this->options['last_word'] ?? '0';
		return '<span class="bangla-date" data-calendar="' . esc_attr( $bangla_calendar ) . '" data-separator="' . esc_attr( $separator ) . '" data-ord-suffix="' . esc_attr( $ord_suffix ) . '" data-last-word="' . esc_attr( $last_word ) . '"></span>';
	}

	public function render_bangla_season(): string {
		$this->enqueue_datetime_assets();
		return '<span class="bangla-season"></span>';
	}

	public function render_gregorian_date(): string {
		$this->enqueue_datetime_assets();
		$separator = $this->options['separator'] ?? ', ';
		$ord_suffix = $this->options['ord_suffix'] ?? '0';
		$last_word = $this->options['last_word'] ?? '0';
		return '<span class="bangla-gregorian-date" data-separator="' . esc_attr( $separator ) . '" data-ord-suffix="' . esc_attr( $ord_suffix ) . '" data-last-word="' . esc_attr( $last_word ) . '"></span>';
	}

	public function render_hijri_date(): string {
		$this->enqueue_datetime_assets();
		$hijri_calendar = $this->options['hijri_calendar'] ?? 'umalqura';
		$hijri_adjust = $this->options['hijri_adjust'] ?? '0';
		$separator = $this->options['separator'] ?? ', ';
		$ord_suffix = $this->options['ord_suffix'] ?? '0';
		$last_word = $this->options['last_word'] ?? '0';
		return '<span class="bangla-hijri-date" data-calendar="' . esc_attr( $hijri_calendar ) . '" data-adjust="' . esc_attr( $hijri_adjust ) . '" data-separator="' . esc_attr( $separator ) . '" data-ord-suffix="' . esc_attr( $ord_suffix ) . '" data-last-word="' . esc_attr( $last_word ) . '"></span>';
	}

	public function enqueue_scripts(): void {
		wp_register_script(
			'bddp-date-time',
			plugin_dir_url( __FILE__ ) . 'assets/js/date-time.js',
			[],
			BDDP_VERSION,
			true
		);
	}

	private function enqueue_datetime_assets(): void {
		wp_enqueue_script( 'bddp-date-time' );
	}

	public function action_links( array $links ): array {
		$links[] = '<a href="' . get_admin_url( null, 'options-general.php?page=bangla-date-display' ) . '">Settings</a>';
		return $links;
	}
}

Bangla_Date_Display::instance();

//================== Widgets ========================
require __DIR__ . '/widgets.php';

// ============ Settings ========================
if ( is_admin() ) {
	include __DIR__ . '/bddp_admin.php';
}
