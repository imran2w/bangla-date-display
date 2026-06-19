<?php

defined( 'ABSPATH' )or die( 'Stop! You can not do this!' );

function bddp_options_page() {
	?>
	<div class="wrap">
		<h1 class="wp-heading-inline">Bangla Date Display Settings</h1>
		<a href="#how_to_use" class="page-title-action">How to use?</a>
		<hr class="wp-header-end">

		<form method="post" action="options.php">

			<?php

			function rplc_symbol( $symbol ) {
				$symbol = str_replace( '"', '&#34;', $symbol );
				return $symbol;
			}

			settings_fields( 'bddp-settings-group' );

			$bddp_options = get_option( "bddp_options" );
			if ( !is_array( $bddp_options ) ) {
				$bddp_options = array(
					'trans_dt' => '0',
					'trans_cmnt' => '0',
					'trans_num' => '0',
					'trans_cal' => '0',
					'bangla_calendar' => 'BD',
					'hijri_calendar' => 'umalqura',
					'ord_suffix' => '1',
					'separator' => ', ',
					'last_word' => '1',
					'hijri_adjust' => '0',
					'cal_wgt' => '0' );
			}
	
			$last_word  = ( $bddp_options['last_word'] ?? '0' ) === '1';
			$ord_suffix = ( $bddp_options['ord_suffix'] ?? '0' ) === '1';

			?>
			
			<h2>Post/Page & Comment Section</h2>
			<table class="form-table" role="presentation">
				<tbody>
					<tr>
						<th scope="row"><label>Language settings</label></th>
						<td>
							<fieldset>
								<legend class="screen-reader-text"><span>Language settings</span></legend>
								<label for="bddp_options[trans_dt]"><input id="bddp_options[trans_dt]" type="checkbox" name="bddp_options[trans_dt]" value="1" <?php checked( $bddp_options['trans_dt'] ?? '0', '1' ); ?>/> Show post/page/comment's date and time in Bangla language</label>
								<br>
								<label for="bddp_options[trans_cmnt]"><input id="bddp_options[trans_cmnt]" type="checkbox" name="bddp_options[trans_cmnt]" value="1" <?php checked( $bddp_options['trans_cmnt'] ?? '0', '1' ); ?>/> Show comment count in Bangla language</label>
							</fieldset>
						</td>
					</tr>
				</tbody>
			</table>
			
			<h2>Date and Time (Shortcode/Widget)</h2>
			<table class="form-table" role="presentation">
				<tbody>
					<tr>
						<th scope="row"><label for="bangla_calendar">Bangla Calendar</label></th>
						<td>
							<select id="bangla_calendar" name="bddp_options[bangla_calendar]">
								<?php
									$calendars = array(
										'BD' => 'Bangladesh',
										'IN' => 'India'
									);
									foreach($calendars as $key=>$value) {
										$selected = ($bddp_options['bangla_calendar'] == $key) ? 'selected' : '';
										echo '<option value="'.$key.'" '.$selected.'>'.$value.'</option>';
									}
								?>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="hijri_calendar">Hijri Calendar</label></th>
						<td>
							<select id="hijri_calendar" name="bddp_options[hijri_calendar]">
								<?php
									$hijri_variants = array(
										'umalqura' => 'Umm al-Qura (Saudi Arabia)',
										'tbla' => 'Tabular (Astronomical)',
										'civil' => 'Civil (Tabular)',
									);
									foreach($hijri_variants as $key=>$value) {
										$selected = ($bddp_options['hijri_calendar'] == $key) ? 'selected' : '';
										echo '<option value="'.$key.'" '.$selected.'>'.$value.'</option>';
									}
								?>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="hijri_adjust">Adjust Hijri Date</label></th>
						<td>
							<select id="hijri_adjust" name="bddp_options[hijri_adjust]">
								<?php
									$hijri_adjst_options = array(
										'-3 days' => '-3',
										'-2 days' => '-2',
										'-1 day' => '-1',
										'±0 day' => '0',
										'+1 day' => '1',
										'+2 days' => '2',
										'+3 days' => '3',
									);
									foreach($hijri_adjst_options as $key=>$value) {
										if($bddp_options[ 'hijri_adjust'] == $value) {
											echo '<option value="'.$value.'" selected>'.$key.'</option>';
										} else {
											echo '<option value="'.$value.'">'.$key.'</option>';
										}
									}
								?>
							</select>
							<p class="description">Hijri month can have 29 or 30 days depending on the visibility of the moon. Adjust it manually.</p>
						</td>
					</tr>
				</tbody>
			</table>
			
			<h2>Date Formatting (Shortcode/Widget)</h2>
			<table class="form-table" role="presentation">
				<tbody>
					<tr>
						<th scope="row"><label>Date separator</label></th>
						<td>
							<p><input type="radio" id="sep1" name="bddp_options[separator]" value=", " <?php if($bddp_options[ 'separator']==", " ) { echo " checked"; } ?>> <label for="sep1">Comma</label>
							</p>
							<p><input type="radio" id="sep2" name="bddp_options[separator]" value=" " <?php if($bddp_options[ 'separator']==" " ) { echo " checked"; } ?>> <label for="sep2">Space</label>
							</p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label>More options</label></th>
						<td>
							<fieldset>
								<legend class="screen-reader-text"><span>More options</span></legend>
								<label for="bddp_options[ord_suffix]"><input type="checkbox" id="bddp_options[ord_suffix]" name="bddp_options[ord_suffix]" value="1" <?php checked( $ord_suffix, true ); ?>/> Show ordinal suffix <span style="color:green;">(Eg. ১লা, ২রা)</span></label>
								<br>
								<label for="bddp_options[last_word]"><input type="checkbox" id="bddp_options[last_word]" name="bddp_options[last_word]" value="1" <?php checked( $last_word, true ); ?>/> Show last word <span style="color:green;">(Eg. খ্রিস্টাব্দ/বঙ্গাব্দ/হিজরি)</span></label>
							</fieldset>
						</td>
					</tr>
				</tbody>
			</table>

			<?php submit_button(); ?>
		</form>


		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'bddp_restore_defaults' ); ?>
			<input type="hidden" name="action" value="bddp_restore_defaults">
			<input type="submit" value="Restore Default Settings" class="button button-secondary">
		</form>
		<br/>

		<a name="how_to_use"></a>
		<div class="postbox">
			<h3 class="hndle" style="padding: 10px; margin: 0;"><span>How to use?</span></h3>
			<div class="inside">
				<p><strong>Go to: Appearance > <a href="<?php echo esc_url( admin_url( 'widgets.php' ) ); ?>">Widgets</a> to use following widgets:</strong>
				</p>
				<ul style="list-style-type: square; margin-left: 10px;">
					<li>Bangla Date Display</li>
					<li>Advanced Archive Calendar</li>
				</ul>

				<hr/>

				<p><strong>OR, Use following shortcodes:</strong>
				</p>
				<table style="border-collapse:collapse;" width="100%">
					<tr>
						<th style="border: 1px solid silver; background-color: #CCC;">Item</th>
						<th style="border: 1px solid silver; background-color: #CCC;">Shortcode</th>
						<th style="border: 1px solid silver; background-color: #CCC;">PHP Code</th>
					</tr>
					<tr>
						<td style="border: 1px solid silver; padding-left: 5px;">Bangla date:</td>
						<td style="border: 1px solid silver; padding-left: 5px;"><code><span style="color: #000000"><span style="color: #0000BB"> &#91;bangla_date&#93;</span></span></code>
						</td>
						<td style="border: 1px solid silver; padding-left: 5px;"><code><span style="color: #000000"><span style="color: #0000BB">   &#60;&#63;php echo do_shortcode&#40;&#39;&#91;bangla_date&#93;&#39;&#41;; </span><span style="color: #0000BB">&#63;&#62;</span></span>
</code>
						</td>
					</tr>
					<tr>
						<td style="border: 1px solid silver; padding-left: 5px;">Gregorian date:</td>
						<td style="border: 1px solid silver; padding-left: 5px;"><code><span style="color: #000000"><span style="color: #0000BB"> &#91;gregorian_date&#93;</span></span></code>
						</td>
						<td style="border: 1px solid silver; padding-left: 5px;"><code><span style="color: #000000"><span style="color: #0000BB">   &#60;&#63;php echo do_shortcode&#40;&#39;&#91;gregorian_date&#93;&#39;&#41;; </span><span style="color: #0000BB">&#63;&#62;</span></span>
</code>
						</td>
					</tr>
					<tr>
						<td style="border: 1px solid silver; padding-left: 5px;">Hijri date:</td>
						<td style="border: 1px solid silver; padding-left: 5px;"><code><span style="color: #000000"><span style="color: #0000BB"> &#91;hijri_date&#93;</span></span></code>
						</td>
						<td style="border: 1px solid silver; padding-left: 5px;"><code><span style="color: #000000"><span style="color: #0000BB">   &#60;&#63;php echo do_shortcode&#40;&#39;&#91;hijri_date&#93;&#39;&#41;; </span><span style="color: #0000BB">&#63;&#62;</span></span>
</code>
						</td>
					</tr>
					<tr>
						<td style="border: 1px solid silver; padding-left: 5px;">Current time:</td>
						<td style="border: 1px solid silver; padding-left: 5px;"><code><span style="color: #000000"><span style="color: #0000BB"> &#91;bangla_time&#93;</span></span></code>
						</td>
						<td style="border: 1px solid silver; padding-left: 5px;"><code><span style="color: #000000"><span style="color: #0000BB">   &#60;&#63;php echo do_shortcode&#40;&#39;&#91;bangla_time&#93;&#39;&#41;; </span><span style="color: #0000BB">&#63;&#62;</span></span>
</code>
						</td>
					</tr>
					<tr>
						<td style="border: 1px solid silver; padding-left: 5px;">Day name:</td>
						<td style="border: 1px solid silver; padding-left: 5px;"><code><span style="color: #000000"><span style="color: #0000BB"> &#91;bangla_day&#93;</span></span></code>
						</td>
						<td style="border: 1px solid silver; padding-left: 5px;"><code><span style="color: #000000"><span style="color: #0000BB">   &#60;&#63;php echo do_shortcode&#40;&#39;&#91;bangla_day&#93;&#39;&#41;; </span><span style="color: #0000BB">&#63;&#62;</span></span>
</code>
						</td>
					</tr>
					<tr>
						<td style="border: 1px solid silver; padding-left: 5px;">Season name:</td>
						<td style="border: 1px solid silver; padding-left: 5px;"><code><span style="color: #000000"><span style="color: #0000BB"> &#91;bangla_season&#93;</span></span></code>
						</td>
						<td style="border: 1px solid silver; padding-left: 5px;"><code><span style="color: #000000"><span style="color: #0000BB">   &#60;&#63;php echo do_shortcode&#40;&#39;&#91;bangla_season&#93;&#39;&#41;; </span><span style="color: #0000BB">&#63;&#62;</span></span>
</code>
						</td>
					</tr>
				</table>
			</div>
		</div>

		<a name="credits"></a>
		<div class="postbox">
			<h3 class="hndle" style="padding: 10px; margin: 0;"><span>Credits</span></h3>
			<div class="inside">
				<table class="form-table">
					<tr valign="top">
						<td style="width: 80px;">
							<a href="https://facebook.com/imran2w" target="_blank"><img src="https://www.gravatar.com/avatar/<?php echo md5( "imran4dev@gmail.com" ); ?>" /></a>
						</td>
						<td>
							<p>Developer: <a href="https://facebook.com/imran2w" target="_blank">ALI IMRAN</a><br/>Facebook: <a href="https://facebook.com/imran2w" target="_blank">https://facebook.com/imran2w</a><br/> E-Mail: <a href="mailto:imran4dev@gmail.com">imran4dev@gmail.com</a><br/> Web: <a href="https://imran.link" target="_blank">https://www.imran.link</a>
							</p>
						</td>
					</tr>
				</table>
			</div>
		</div>

	</div>

<?php
	}


	add_action( 'admin_menu', 'bddp_admin' );

	function bddp_admin() {
		add_options_page( 'Bangla Date Display Settings', 'Bangla Date Display', 'manage_options', 'bangla-date-display', 'bddp_options_page' );
	}

	// Register settings --------------------------------
	add_action( 'admin_init', 'register_bddp_settings' );

	function register_bddp_settings() {
		register_setting(
			'bddp-settings-group',
			'bddp_options',
			array(
				'sanitize_callback' => 'bddp_sanitize_options',
			)
		);
	}

	function bddp_sanitize_options( $input ) {
		$input = is_array( $input ) ? $input : array();

		$checkbox_keys = array( 'trans_dt', 'trans_cmnt', 'trans_num', 'trans_cal', 'ord_suffix', 'last_word', 'cal_wgt' );
		$output = array();

		foreach ( $checkbox_keys as $key ) {
			$output[ $key ] = ! empty( $input[ $key ] ) ? '1' : '0';
		}

		$allowed_bangla_calendars = array( 'BD', 'IN' );
		$output['bangla_calendar'] = in_array( $input['bangla_calendar'] ?? 'BD', $allowed_bangla_calendars, true ) ? $input['bangla_calendar'] : 'BD';

		$allowed_hijri_calendars = array( 'umalqura', 'tbla', 'civil' );
		$output['hijri_calendar'] = in_array( $input['hijri_calendar'] ?? 'umalqura', $allowed_hijri_calendars, true ) ? $input['hijri_calendar'] : 'umalqura';

		$separator = isset( $input['separator'] ) ? (string) $input['separator'] : ', ';
		$output['separator'] = in_array( $separator, array( ', ', ' ' ), true ) ? $separator : ', ';

		$hijri_adjust = isset( $input['hijri_adjust'] ) ? (int) $input['hijri_adjust'] : 0;
		if ( abs( $hijri_adjust ) > 3 && 0 === $hijri_adjust % 24 ) {
			$hijri_adjust = (int) ( $hijri_adjust / 24 );
		}
		$hijri_adjust = max( -3, min( 3, $hijri_adjust ) );
		$output['hijri_adjust'] = (string) $hijri_adjust;

		return $output;
	}

	add_action( 'admin_post_bddp_restore_defaults', 'bddp_restore_defaults' );

	function bddp_restore_defaults() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Sorry, you are not allowed to do this.', 'bddp' ) );
		}

		check_admin_referer( 'bddp_restore_defaults' );
		delete_option( 'bddp_options' );

		wp_safe_redirect( admin_url( 'options-general.php?page=bangla-date-display' ) );
		exit;
	}
?>