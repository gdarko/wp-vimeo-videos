<?php
/********************************************************************
 * Copyright (C) 2023 Darko Gjorgjijoski (https://codeverve.com)
 *
 * This file is part of Video Uploads for Vimeo
 *
 * Video Uploads for Vimeo is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * Video Uploads for Vimeo is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Video Uploads for Vimeo. If not, see <https://www.gnu.org/licenses/>.
 **********************************************************************/

/**
 * Class WP_DGV_Admin_Screen_Options
 *
 * Main class for initializing the admin functionality
 *
 * @license GPLv2
 * @copyright Darko Gjorgjijoski <info@codeverve.com>
 * @since 1.9.2
 */
class WP_DGV_Admin_Screen_Options {

	/**
	 * The ajax action
	 */
	const ACTION = 'dgv_sopts_save';

	/**
	 * Our nonce name
	 */
	const NONCE = 'dgv_sopts_save';

	/**
	 * Init function.  Called from outside the class.  Adds actions and such.
	 *
	 * @access public
	 * @return null
	 */
	public function init() {
		add_action(
			'load-post-new.php',
			array( $this, 'load' )
		);

		add_action(
			'wp_ajax_' . self::ACTION,
			array( $this, 'ajax' )
		);
	}

	/**
	 * Hooked into `load-post-new.php`.  Adds an option and
	 * hooks into a few other actions/filters
	 */
	public function load() {
		add_filter( 'screen_settings', array( $this, 'add_field' ), 10, 2 );
		add_action( 'admin_head', array( $this, 'head' ) );
		add_filter( 'enter_title_here', array( $this, 'title' ) );
	}

	/**
	 * Hooked into `screen_settings`.  Adds the field to the settings area
	 *
	 * @access public
	 * @return string The settings fields
	 */
	public function add_field( $rv, $screen ) {
		$val = get_user_option(
			sprintf( 'default_title_%s', sanitize_key( $screen->id ) ),
			get_current_user_id()
		);
		$rv  .= '<div class="pmg-sotut-container">';
		$rv  .= '<h5>' . __( 'Default Title' ) . '</h5>';
		$rv  .= '<p><input type="text" class="normal-text" id="pmg-sotut-field" ' .
		        'value="' . esc_attr( $val ) . '" /></p>';
		$rv  .= wp_nonce_field( self::NONCE, self::NONCE, false, false );
		$rv  .= '</div>';

		return $rv;
	}

	/**
	 * Hooked into `admin_head`.  Spits out some JS to save the info
	 *
	 * @access public
	 * @return null
	 */
	public function head() {
		?>
        <script type="text/javascript">
            jQuery(document).ready(function () {
                jQuery('input#pmg-sotut-field').blur(function () {
                    jQuery.post(
                        ajaxurl,
                        {
                            title: jQuery(this).val(),
                            nonce: jQuery('input#<?php echo esc_js( self::NONCE ); ?>').val(),
                            screen: '<?php echo esc_js( get_current_screen()->id ); ?>',
                            action: '<?php echo self::ACTION; ?>'
                        }
                    );
                });
            });
        </script>
		<?php
	}

	/**
	 * Hooked into `wp_ajax_self::ACTION`  Handles saving the fields and such
	 *
	 * @access public
	 * @return null
	 */
	public function ajax() {
		check_ajax_referer( self::NONCE, 'nonce' );
		$screen = isset( $_POST['screen'] ) ? $_POST['screen'] : false;
		$title  = isset( $_POST['title'] ) ? $_POST['title'] : false;

		if ( ! $screen || ! ( $user = wp_get_current_user() ) ) {
			die( 0 );
		}

		if ( ! $screen = sanitize_key( $screen ) ) {
			die( 0 );
		}

		update_user_option(
			$user->ID,
			"default_title_{$screen}",
			esc_attr( strip_tags( $title ) )
		);
		die( '1' );
	}

	/**
	 * Hooked into `enter_title_here`.  Replaces the title with the user's
	 * preference (if it exists).
	 *
	 * @access public
	 * @return string The Default title
	 */
	public function title( $t ) {
		if ( ! $user = wp_get_current_user() ) {
			return $t;
		}
		$id = sanitize_key( get_current_screen()->id );
		if ( $title = get_user_option( "default_title_{$id}", $user->ID ) ) {
			$t = esc_attr( $title );
		}

		return $t;
	}
}