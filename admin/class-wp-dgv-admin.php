<?php
/********************************************************************
 * Copyright (C) 2020 Darko Gjorgjijoski (https://codeverve.com)
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
 * Class WP_DGV_Admin
 *
 * Main class for initializing the admin functionality
 *
 * @license GPLv2
 * @copyright Darko Gjorgjijoski <info@codeverve.com>
 * @since 1.0.0
 */
class WP_DGV_Admin {

	const PAGE_VIMEO = 'dgv-library';
	const PAGE_SETTINGS = 'dgv-settings';

	/**
	 * The settings helper
	 * @var WP_DGV_Settings_Helper
	 */
	public $settings_helper = null;

	/**
	 * The vimeo api helper
	 * @var WP_DGV_Api_Helper
	 */
	public $api_helper = null;

	/**
	 * The database helper
	 * @var null
	 */
	public $db_helper = null;

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param  string  $plugin_name  The name of this plugin.
	 * @param  string  $version  The version of this plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name     = $plugin_name;
		$this->version         = $version;
		$this->api_helper      = new WP_DGV_Api_Helper();
		$this->db_helper       = new WP_DGV_Db_Helper();
		$this->settings_helper = new WP_DGV_Settings_Helper();
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( 'dgv-admin' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		if ( ! function_exists( 'get_current_screen' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/screen.php' );
		}

		// Validate the current screen
		$current_screen           = get_current_screen();
		$is_edit_screen           = isset( $_GET['post'] ) && is_numeric( $_GET['post'] );
		$is_create_screen         = $current_screen->action === 'add' && $current_screen->base === 'post';
		$is_gutenberg_active      = wvv_is_gutenberg_active();
		$is_create_or_edit_screen = $is_create_screen || $is_edit_screen;

		// Sweet alert
		$this->enqueue_sweetalert();

		// Uploader
		$this->enqueue_vimeo_uploader();
		$this->enqueue_vimeo_upload_modal();

		// Admin
		$this->enqueue_admin_scripts();

		// Gutenbrg block
		if ( $is_gutenberg_active && $is_create_or_edit_screen ) {
			$this->enqueue_gutenberg_block();
			//wp_enqueue_style( 'dgv-dropzone' );
		}
	}

	/**
	 * Enqueue Sweet alert
	 */
	public function enqueue_sweetalert() {
		wp_enqueue_script( 'dgv-swal' );
	}

	/**
	 * Enqueue Vimeo Uploader
	 */
	public function enqueue_vimeo_uploader() {
		wp_enqueue_script( 'dgv-tus' );
		wp_enqueue_script( 'dgv-uploader' );
	}

	/**
	 * Enqueue the Upload modal
	 */
	public function enqueue_vimeo_upload_modal() {
		wp_enqueue_script( 'dgv-upload-modal' );
		wp_enqueue_style( 'dgv-upload-modal' );
	}

	/**
	 * Enqueue gutenberg block
	 */
	public function enqueue_gutenberg_block() {

		$current_user_uploads = ! current_user_can( 'administrator' ) && (int) $this->settings_helper->get( 'dgv_local_current_user_only' );

		$uploads = $this->db_helper->get_uploaded_videos( $current_user_uploads );

		wp_enqueue_script( 'wvv-vimeo-upload-block' );
		wp_localize_script( 'wvv-vimeo-upload-block', 'DGVGTB', array(
			'nonce'               => wp_create_nonce( 'dgvsecurity' ),
			'access_token'        => $this->settings_helper->get( 'dgv_access_token' ),
			'enable_vimeo_search' => true,
			'default_privacy'     => 'anybody',
			'ajax_url'            => admin_url( 'admin-ajax.php' ),
			'uploads'             => $uploads,
			'methods'             => wvv_get_editor_insert_methods(),
			'words'               => array(
				'title'        => __( 'Title', 'wp-vimeo-videos' ),
				'desc'         => __( 'Description', 'wp-vimeo-videos' ),
				'file'         => __( 'File', 'wp-vimeo-videos' ),
				'uploading3d'  => __( 'Uploading...', 'wp-vimeo-videos' ),
				'upload'       => __( 'Upload', 'wp-vimeo-videos' ),
				'search'       => __( 'Search', 'wp-vimeo-videos' ),
				'sorry'        => __( 'Sorry', 'wp-vimeo-videos' ),
				'privacy_view' => __( 'Who can view this video?', 'wp-vimeo-videos' ),
			),
			'phrases'             => array(
				'upload_invalid_file'               => __( 'Please select valid video file.', 'wp-vimeo-videos' ),
				'invalid_search_phrase'             => __( 'Invalid search phrase. Please enter valid search phrase.', 'wp-vimeo-videos' ),
				'enter_phrase'                      => __( 'Enter phrase', 'wp-vimeo-videos' ),
				'select_video'                      => __( 'Select video', 'wp-vimeo-videos' ),
				'upload_success'                    => __( 'Video uploaded successfully!', 'wp-vimeo-videos' ),
				'block_title'                       => __( 'Insert Vimeo Video', 'wp-vimeo-videos' ),
				'existing_not_visible_current_user' => __( '= Uploaded by someone else, not visible to you =', 'wp-vimeo-videos' )
			),
			'upload_form_options' => array(
				'enable_privacy_option' => false,
				'privacy_view'          => $this->api_helper->get_view_privacy_options_for_forms( 'admin' ),
			)
		) );
		wp_enqueue_style( 'wvv-vimeo-upload-block' );
	}

	/**
	 * Enqueue admin scripts
	 */
	public function enqueue_admin_scripts() {

		if ( ! wp_script_is( 'select2', 'enqueued' ) ) {
			wp_enqueue_script( 'dgv-select2' );
		}
		if ( ! wp_style_is( 'select2', 'enqueued' ) ) {
			wp_enqueue_style( 'dgv-select2' );
		}


		wp_enqueue_script( 'dgv-admin' );

		wp_localize_script( 'dgv-admin', 'DGV', array(
			'nonce'                         => wp_create_nonce( 'dgvsecurity' ),
			'ajax_url'                      => admin_url( 'admin-ajax.php' ),
			'access_token'                  => $this->settings_helper->get( 'dgv_access_token' ),
			'default_privacy'               => $this->settings_helper->get_default_admin_view_privacy(),
			'sorry'                         => __( 'Sorry', 'wp-vimeo-videos' ),
			'upload_invalid_file'           => __( 'Please select valid video file.', 'wp-vimeo-videos' ),
			'delete_not_allowed'            => __( 'Delete is not allowed because your account doesn\'t have the correct delete scope required by Vimeo.' ),
			'delete_confirm_title'          => __( 'Are you sure?', 'wp-vimeo-videos' ),
			'delete_confirm_desc'           => __( 'Are you sure you want to delete this video? This action deletes the video from the Vimeo and can not be reversed.', 'wp-vimeo-videos' ),
			'delete_whitelist_domain_error' => __( 'Sorry, the domain could not be deleted.', 'wp-vimeo-videos' ),
			'http_error'                    => __( 'Sorry there was a HTTP error. Please check the server logs or contact support.', 'wp-vimeo-videos' ),
			'success'                       => __( 'Success', 'wp-vimeo-videos' ),
			'cancel'                        => __( 'Cancel', 'wp-vimeo-videos' ),
			'confirm'                       => __( 'Confirm', 'wp-vimeo-videos' ),
			'close'                         => __( 'Close', 'wp-vimeo-videos' ),
			'delete_confirmation'           => __( 'Are you sure you want to delete this video?', 'wp-vimeo-videos' ),
			'delete_confirmation_yes'       => __( 'Yes, please', 'wp-vimeo-videos' ),
			'title'                         => __( 'Title', 'wp-vimeo-videos' ),
			'description'                   => __( 'Description', 'wp-vimeo-videos' ),
			'upload'                        => __( 'Upload', 'wp-vimeo-videos' ),
			'upload_to_vimeo'               => __( 'Upload to vimeo', 'wp-vimeo-videos' ),
			'correct_errors'                => __( 'Please correct the following errors', 'wp-vimeo-videos' ),
			'privacy_view'                  => __( 'Who can view this video?', 'wp-vimeo-videos' ),
			'problem_solution'              => __( 'Problem solution' ),
			'upload_form_options'           => array(
				'enable_privacy_option' => (int) $this->settings_helper->get( 'dgv_enable_privacy_option_media', 0 ),
				'privacy_view'          => $this->api_helper->get_view_privacy_options_for_forms( 'admin' ),
			)
		) );
	}

	/**
	 * Register the admin menus
	 *
	 * @since 1.0.0
	 */
	public function register_admin_menu() {
		$hook = add_media_page(
			__( 'WP Vimeo Library', 'wp-vimeo-videos' ),
			'Vimeo',
			'upload_files',
			self::PAGE_VIMEO, array(
			$this,
			'render_vimeo_page'
		) );
		add_options_page(
			__( 'WP Vimeo Settings', 'wp-vimeo-videos' ),
			'Vimeo',
			'manage_options',
			self::PAGE_SETTINGS, array(
			$this,
			'render_settings_page'
		) );
	}

	/**
	 * Renders the vimeo pages
	 */
	public function render_vimeo_page() {
		echo wvv_get_view( 'admin/partials/library', array(
			'vimeo_helper' => $this->api_helper,
			'db_helper'    => $this->db_helper,
			'settings_helper' => $this->settings_helper,
		) );
	}

	/**
	 * Renders the settings page
	 */
	public function render_settings_page() {
		echo wvv_get_view( 'admin/partials/settings', array(
			'vimeo_helper' => $this->api_helper,
			'db_helper'    => $this->db_helper,
		) );
	}

	/**
	 * Unset third party notices.
	 */
	public function do_admin_notices() {
		if ( $this->is_any_page() ) {
			\remove_all_actions( 'admin_notices' );
		}
		if ( ! $this->is_list_page() && ! $this->is_edit_page() ) {
			$this->instructions();
		}
	}

	/**
	 * Add instructions view
	 */
	public function instructions() {
		if ( ! current_user_can( 'install_plugins' ) ) {
			return;
		}

		// Verify dismiss
		$dismiss_key = 'dgv_instructions_dismissed';
		if ( isset( $_GET['wvv_dismiss_instructions'] ) && isset( $_GET['wvv_nonce'] ) ) {
			if ( wp_verify_nonce( $_GET['wvv_nonce'], 'wvv_instructions_dismiss' ) ) {
				update_option( $dismiss_key, 1 );
			}
		}
		// Render if not dismissed.
		$instructions_hidden = get_option( $dismiss_key );
		if ( ! $instructions_hidden || empty( $instructions_hidden ) || intval( $instructions_hidden ) !== 1 ) {
			$disallowed = array();
			$page       = isset( $_GET['page'] ) ? $_GET['page'] : null;
			if ( ! in_array( $page, $disallowed ) ) {
				include WP_VIMEO_VIDEOS_PATH . '/admin/partials/instructions.php';
			}
		}
	}

	/**
	 * Add a link to the settings page on the plugins.php page.
	 *
	 * @param  array  $links  List of existing plugin action links.
	 *
	 * @return array         List of modified plugin action links.
	 *
	 */
	function plugin_action_links( $links ) {

		$links = array_merge( array(
			'<a href="' . esc_url( admin_url( '/options-general.php?page=dgv-settings' ) ) . '">' . __( 'Settings',
				'wp-vimeo-videos' ) . '</a>'
		), $links );

		return $links;
	}

	/**
	 * Add link to the PRO version on the plugins.php page.
	 *
	 * @param $plugin_meta
	 * @param $plugin_file
	 * @param $plugin_data
	 * @param $status
	 *
	 * @return mixed
	 */
	public function plugin_row_meta( $plugin_meta, $plugin_file, $plugin_data, $status ) {

		if ( WP_VIMEO_VIDEOS_BASENAME === $plugin_file ) {
			$plugin_meta[] = '<a class="wvv-color-green" target="_blank" href="' . esc_url( wvv_get_purchase_url() ) . '"><strong>' . __( 'Buy PRO Version',
					'wp-vimeo-videos' ) . '</strong></a>';
		}

		return $plugin_meta;
	}

	/**
	 * Is any page?
	 * @return bool
	 */
	public function is_any_page() {
		return is_admin() && isset( $_GET['page'] ) && in_array( $_GET['page'], array(
				self::PAGE_VIMEO,
				self::PAGE_SETTINGS
			) );
	}

	/**
	 * Is the list page?
	 * @return bool
	 */
	public function is_list_page() {
		return $this->is_any_page() && ! isset( $_GET['action'] );
	}

	/**
	 * Is the edit page?
	 * @return bool
	 */
	public function is_edit_page() {
		return $this->is_any_page() && isset( $_GET['action'] ) && 'edit' === $_GET['action'];
	}

}
