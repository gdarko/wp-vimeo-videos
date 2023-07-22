<?php
/********************************************************************
 * Copyright (C) 2023 Darko Gjorgjijoski (https://darkog.com/)
 * Copyright (C) 2023 IDEOLOGIX MEDIA Dooel (https://ideologix.com/)
 *
 * This file is property of IDEOLOGIX MEDIA Dooel (https://ideologix.com)
 * This file is part of Vimeify Plugin - https://wordpress.org/plugins/wp-vimeo-videos/
 *
 * Vimeify - Formerly "WP Vimeo Videos" is free software: you can redistribute
 * it and/or modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation, either version 2 of the License,
 * or (at your option) any later version.
 *
 * Vimeify - Formerly "WP Vimeo Videos" is distributed in the hope that it
 * will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this plugin. If not, see <https://www.gnu.org/licenses/>.
 *
 * Code developed by Darko Gjorgjijoski <dg@darkog.com>.
 **********************************************************************/

namespace Vimeify\Core\Utilities;

/**
 * @since 1.9.3
 */
class ScreenOptions {

	/**
	 * List of options
	 * @var array
	 */
	private $options;

	/**
	 * Constructor
	 *
	 * @param  array  $options
	 */
	public function __construct( $options ) {

		$this->options = $options;

		foreach ( $this->options as $admin_page => $choices ) {
			add_action( "load-media_page_$admin_page", [ $this, 'get_screen_options' ] );
		}

		add_filter( 'screen_settings', [ $this, 'show_screen_options' ], 10, 2 );
		add_filter( 'set-screen-option', [ $this, 'set_option' ], 11, 3 );

	}

	/**
	 * Array of screen options to display.
	 *
	 * @return array The screen option function names.
	 */
	private function screen_options() {
		$screen_options = [];

		foreach ( $this->options as $page_name => $options ) {
			foreach ( $options as $option_key => $option_name ) {
				$screen_options[] = [
					'option' => sprintf( '%s_%s_page', $page_name, $option_key ),
					'title'  => $option_name,
				];
			}
		}

		return $screen_options;
	}

	/**
	 * Register the screen options.
	 */
	public function get_screen_options() {

		$screen = get_current_screen();


		if ( ! is_object( $screen ) ) {
			return;
		}

		$page_id = str_replace( 'media_page_', '', $screen->id );
		if ( ! array_key_exists( $page_id, $this->options ) ) {
			return;
		}

		// Loop through all the options and add a screen option for each.
		foreach ( $this->options[ $page_id ] as $option_key => $option_name ) {
			$key = sprintf( '%s_%s', $page_id, $option_key );
			add_screen_option( $key, [
				'option' => $option_name,
				'value'  => true,
			] );
		}
	}

	/**
	 * The HTML markup to wrap around each option.
	 */
	public function before() {
		?>
        <fieldset><input type="hidden" name="wp_screen_options_nonce" value="<?php echo esc_textarea( wp_create_nonce( 'wp_screen_options_nonce' ) ); ?>">
        <legend><?php esc_html_e( 'Screen Options', 'wp-vimeo-videos' ); ?></legend>        <div class="metabox-prefs">
        <div><input type="hidden" name="wp_screen_options[option]" value="dgv_screen_options_page"/></div>
        <div><input type="hidden" name="wp_screen_options[value]" value="yes"/></div>        <div class="dgv_screen_options_custom_fields">
		<?php
	}

	/**
	 * The HTML markup to close the options.
	 */
	public function after() {
		$button = get_submit_button( __( 'Apply', 'wp-vimeo-videos' ), 'button', 'screen-options-apply', false );
		?>
        </div><!-- dgv_screen_options_custom_fields -->        </div><!-- metabox-prefs -->        </fieldset>        <br class="clear">
		<?php
		echo $button; // WPCS: XSS ok.
	}

	/**
	 * Display a screen option.
	 *
	 * @param  string  $title  The title to display.
	 * @param  string  $option  The name of the option we're displaying.
	 */
	public function show_option( $title, $option ) {

		$screen    = get_current_screen();
		$id        = "dgv_screen_options_$option";
		$user_meta = get_user_meta( get_current_user_id(), 'dgv_screen_options_page', true );

		// Check if the screen options have been saved. If so, use the saved value. Otherwise, use the default values.
		if ( $user_meta ) {
			$checked = array_key_exists( $option, $user_meta );
		} else {
			$checked = $screen->get_option( $id, 'value' ) ? true : false;
		}

		?>

        <label for="<?php echo esc_textarea( $id ); ?>"><input type="checkbox" name="dgv_screen_options[<?php echo esc_textarea( $option ); ?>]" class="wordpress-screen-options-demo" id="<?php echo esc_textarea( $id ); ?>" <?php checked( $checked ); ?>/> <?php echo esc_html( $title ); ?></label>

		<?php
	}

	/**
	 * Render the screen options block.
	 *
	 * @param  string  $status  The screen options markup.
	 * @param  object  $args  An object of screen options data.
	 *
	 * @return string         The filtered screen options block.
	 */
	public function show_screen_options( $status, $args ) {

		if ( empty( $args->base ) ) {
			return $status;
		}

		$page_id = str_replace( 'media_page_', '', $args->base );

		if ( ! array_key_exists( $page_id, $this->options ) ) {
			return $status;
		}

		ob_start();

		$this->before();
		foreach ( $this->screen_options() as $screen_option ) {
			$this->show_option( $screen_option['title'], $screen_option['option'] );
		}
		$this->after();

		return ob_get_clean();
	}

	/**
	 * Save the screen option setting.
	 *
	 * @param  string  $status  The default value for the filter. Using anything other than false assumes you are handling saving the option.
	 * @param  string  $option  The option name.
	 * @param  array  $value  Whatever option you're setting.
	 */
	public function set_option( $screen_option, $option, $value ) {

		if ( isset( $_POST['wp_screen_options_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wp_screen_options_nonce'] ) ), 'wp_screen_options_nonce' ) ) {
			if ( 'dgv_screen_options_page' === $option ) {
				$screen_option = isset( $_POST['dgv_screen_options'] ) && is_array( $_POST['dgv_screen_options'] ) ? $_POST['dgv_screen_options'] : []; // WPCS: Sanitization ok.
			}
		}

		return $screen_option;
	}

	/**
	 * Get the screen options
	 *
	 * @param $option
	 * @param $screen
	 * @param $default
	 *
	 * @return mixed|null
	 */
	public static function get_option( $option, $screen = null, $default = null ) {
		if ( is_null( $screen ) ) {
			$screen = get_current_screen();
		}
		$screen_options = get_user_meta( get_current_user_id(), 'dgv_screen_options_page', true );
		$option_id      = str_replace( 'media_page_', '', $screen->id ) . '_' . $option . '_page';

		return isset( $screen_options[ $option_id ] ) ? $screen_options[ $option_id ] : $default;

	}
}