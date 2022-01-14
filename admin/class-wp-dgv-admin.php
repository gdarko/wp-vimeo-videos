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
	 * The vimeo api helper
	 * @var WP_DGV_Api_Helper
	 */
	public $api_helper = null;

	/**
	 * The settings helper
	 * @var WP_DGV_Settings_Helper
	 */
	public $settings_helper = null;

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
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/admin.css', array(), filemtime( plugin_dir_path( __FILE__ ) . 'css/admin.css' ), 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		// Validate the current screen
		$current_screen           = get_current_screen();
		$is_edit_screen           = isset( $_GET['post'] ) && is_numeric( $_GET['post'] );
		$is_create_screen         = $current_screen->action === 'add' && $current_screen->base === 'post';
		$is_gutenberg_active      = wvv_is_gutenberg_active();
		$is_create_or_edit_screen = $is_create_screen || $is_edit_screen;


		// Get all uploads
		$uploads = null;
		if ( $is_create_or_edit_screen ) {
			$current_user_uploads = ! current_user_can( 'administrator' ) && (int) $this->settings_helper->get( 'dgv_local_current_user_only' );
			$uploads              = $this->db_helper->get_uploaded_videos( $current_user_uploads );
		}

		// Select2
		wp_register_script( 'dgv-select2', WP_VIMEO_VIDEOS_URL . 'admin/resources/select2/select2.min.js', null, null, true );
		wp_register_style( 'dgv-select2', WP_VIMEO_VIDEOS_URL . 'admin/resources/select2/select2.min.css', array(), null, 'all' );
		// Sweetalert
		wp_register_script( 'dgv-swal', WP_VIMEO_VIDEOS_URL . 'admin/resources/sweetalert2/sweetalert2.min.js', null, '11.1.4', true );
		// TUS
		wp_register_script( 'dgv-tus', WP_VIMEO_VIDEOS_URL . 'admin/resources/tus-js-client/tus.min.js', null, '1.8.0' );
		// Uploader
		wp_register_script( 'dgv-uploader', WP_VIMEO_VIDEOS_URL . 'admin/js/uploader.js', array( 'dgv-tus' ), filemtime( WP_VIMEO_VIDEOS_PATH . 'admin/js/uploader.js' ) );
		// Upload Modal
		wp_register_script( 'dgv-upload-modal', WP_VIMEO_VIDEOS_URL . 'admin/js/upload-modal.js', array( 'jquery', 'dgv-uploader' ), filemtime( WP_VIMEO_VIDEOS_PATH . 'admin/js/upload-modal.js' ) );
		wp_enqueue_style( 'dgv-upload-modal', WP_VIMEO_VIDEOS_URL . 'admin/css/upload-modal.css', array(), filemtime( WP_VIMEO_VIDEOS_PATH . 'admin/css/upload-modal.css' ) );
		// Admin
		wp_register_script( $this->plugin_name, WP_VIMEO_VIDEOS_URL . 'admin/js/admin.js', array( 'jquery', 'dgv-uploader' ), filemtime( WP_VIMEO_VIDEOS_PATH . 'admin/js/admin.js' ), true );

		// Select2
		if ( ! wp_script_is( 'select2', 'enqueued' ) ) {
			wp_enqueue_script( 'dgv-select2' );
		}
		if ( ! wp_style_is( 'select2', 'enqueued' ) ) {
			wp_enqueue_style( 'dgv-select2' );
		}
		// Sweetalert
		wp_enqueue_script( 'dgv-swal' );
		// TUS
		wp_enqueue_script( 'dgv-tus' );
		// Uploader
		wp_enqueue_script( 'dgv-uploader' );
		// Admin
		wp_enqueue_script( $this->plugin_name );
		wp_localize_script( $this->plugin_name, 'DGV', array(
			'nonce'               => wp_create_nonce( 'dgvsecurity' ),
			'ajax_url'            => admin_url( 'admin-ajax.php' ),
			'access_token'        => $this->settings_helper->get( 'dgv_access_token' ),
			'api_scopes'          => $this->api_helper->scopes,
			'default_privacy'     => apply_filters( 'dgv_default_privacy', 'anybody' ),
			'uploading'           => sprintf( '%s %s', '<img src="' . admin_url( 'images/spinner.gif' ) . '">', __( 'Uploading video. Please wait...', 'wp-vimeo-videos' ) ),
			'sorry'               => __( 'Sorry', 'wp-vimeo-videos' ),
			'upload_invalid_file' => __( 'Please select valid video file.', 'wp-vimeo-videos' ),
			'success'             => __( 'Success', 'wp-vimeo-videos' ),
			'cancel'              => __( 'Cancel', 'wp-vimeo-videos' ),
			'confirm'             => __( 'Confirm', 'wp-vimeo-videos' ),
			'close'               => __( 'Close', 'wp-vimeo-videos' ),
			'correct_errors'      => __( 'Please correct the following errors', 'wp-vimeo-videos' ),
			'problem_solution'    => __( 'Problem solution' ),
			'uploads'             => $uploads,
			'phrases'             => array(
				'select2' => array(
					'errorLoading'    => __( 'The results could not be loaded.', 'wp-vimeo-videos' ),
					'inputTooLong'    => __( 'Please delete {number} character', 'wp-vimeo-videos' ),
					'inputTooShort'   => __( 'Please enter {number} or more characters', 'wp-vimeo-videos' ),
					'loadingMore'     => __( 'Loading more results...', 'wp-vimeo-videos' ),
					'maximumSelected' => __( 'You can only select {number} item', 'wp-vimeo-videos' ),
					'noResults'       => __( 'No results found', 'wp-vimeo-videos' ),
					'searching'       => __( 'Searching...', 'wp-vimeo-videos' ),
					'removeAllItems'  => __( 'Remove all items', 'wp-vimeo-videos' ),
					'removeItem'      => __( 'Remove item', 'wp-vimeo-videos' ),
					'search'          => __( 'Search', 'wp-vimeo-videos' ),
				)
			)
		) );

		if ( $is_gutenberg_active && $is_create_or_edit_screen ) {
			wp_enqueue_script( 'wvv-vimeo-upload-block', WP_VIMEO_VIDEOS_URL . 'admin/blocks/upload/script.js', array(
				'wp-blocks',
				'wp-editor',
				'jquery',
				'dgv-uploader'
			), filemtime( WP_VIMEO_VIDEOS_PATH . 'admin/blocks/upload/script.js' ) );
			wp_enqueue_style( 'wvv-vimeo-upload-block', WP_VIMEO_VIDEOS_URL . 'admin/blocks/upload/style.css', array(), filemtime( WP_VIMEO_VIDEOS_PATH . 'admin/blocks/upload/style.css' ), 'all' );

			wp_localize_script( 'wvv-vimeo-upload-block', 'DGVUB', array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
			) );
		}
	}

	/**
	 * Register the admin menus
	 *
	 * @since 1.0.0
	 */
	public function register_admin_menu() {
		add_media_page(
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
	 * Register Vimeo Button
	 *
	 * @param $buttons
	 *
	 * @return mixed
	 */
	public function tinymce_vimeo_button( $buttons ) {
		$is_gutenberg = is_admin() && wvv_is_gutenberg_active();

		if ( $is_gutenberg ) {
			return $buttons;
		}

		if ( ! apply_filters( 'dgv_enable_tinymce_upload_plugin', true ) ) {
			return $buttons;
		}

		array_push( $buttons, 'dgv_vimeo_button' );

		return $buttons;
	}

	/**
	 * Register Vimeo Plugin
	 *
	 * @param $plugin_array
	 *
	 * @return mixed
	 */
	public function tinymce_vimeo_plugin( $plugin_array ) {

		$is_gutenberg = is_admin() && wvv_is_gutenberg_active();

		if ( $is_gutenberg ) {
			return $plugin_array;
		}

		if ( ! apply_filters( 'dgv_enable_tinymce_upload_plugin', true ) ) {
			return $plugin_array;
		}

		$this->enqueue_tinymce_assets();

		$plugin_array['dgv_vimeo_button'] = WP_VIMEO_VIDEOS_URL . 'admin/js/tinymce-upload.js';

		return $plugin_array;
	}

	/**
	 * Add editor styles
	 */
	public function tinymce_styles() {
		add_editor_style( WP_VIMEO_VIDEOS_URL . 'admin/css/upload-modal.css' );
	}

	/**
	 * Tinymce globals
	 *
	 * @param $settings
	 */
	public function tinymce_globals( $settings ) {

		$is_loaded = false;

		if ( is_array( $settings ) ) {
			foreach ( $settings as $editor_id => $editor ) {
				if ( isset( $editor['external_plugins'] ) ) {
					if ( strpos( $editor['external_plugins'], 'dgv_vimeo_button' ) !== false ) {
						$is_loaded = true;
					}
				}
			}
		}

		if ( ! $is_loaded ) {
			return;
		}

		$this->enqueue_tinymce_assets();

	}

	/**
	 * Enqueues TinyMCE assets
	 */
	public function enqueue_tinymce_assets() {
		foreach ( array( 'dgv-swal', 'dgv-tus', 'dgv-uploader', 'dgv-upload-modal' ) as $script ) {
			wp_enqueue_script( $script );
		}
		foreach ( array( 'dgv-upload-modal' ) as $style ) {
			wp_enqueue_style( $style );
		}
		// Config
		$mce_icon     = apply_filters( 'dgv_mce_toolbar_icon_enable', true );
		$mce_icon_url = $mce_icon ? apply_filters( 'dgv_mce_toolbar_icon_url', esc_url(wvv_get_vimeo_icon_url()) ) : null;
		$mce_text     = apply_filters( 'dgv_mce_toolbar_title', __( 'Vimeo', 'wp-vimeo-videos' ) );
		$mce_text     = $mce_icon && $mce_text ? sprintf( ' %s', $mce_text ) : $mce_text;
		$mce_tooltip  = apply_filters( 'dgv_mce_toolbar_tooltip', __( 'Insert Vimeo Video', 'wp-vimeo-videos' ) );
		wp_localize_script( 'wp-tinymce', 'DGV_MCE_Config', array(
			'phrases'  => array(
				'tmce_title'   => $mce_text,
				'tmce_tooltip' => $mce_tooltip,
			),
			'icon'     => $mce_icon,
			'icon_url' => $mce_icon_url,
		) );
		wp_localize_script( 'dgv-upload-modal', 'DGV_Modal_Config', $this->get_modal_config_params() );
	}


	/**
	 * The modal config params
	 * @return array
	 */
	private function get_modal_config_params() {
		return array(
			'nonce'           => $this->get_nonce(),
			'ajax_url'        => admin_url( 'admin-ajax.php' ),
			'access_token'    => $this->settings_helper->get( 'dgv_access_token' ),
			'default_privacy' => 'anybody',
			'words'           => array(
				'sorry'        => __( 'Sorry', 'wp-vimeo-videos' ),
				'success'      => __( 'Success', 'wp-vimeo-videos' ),
				'title'        => __( 'Title', 'wp-vimeo-videos' ),
				'desc'         => __( 'Description', 'wp-vimeo-videos' ),
				'insert'       => __( 'Insert', 'wp-vimeo-videos' ),
				'search'       => __( 'Search', 'wp-vimeo-videos' ),
				'searching3d'  => __( 'Searching...', 'wp-vimeo-videos' ),
				'upload'       => __( 'Upload', 'wp-vimeo-videos' ),
				'uploading3d'  => __( 'Uploading', 'wp-vimeo-videos' ),
				'file'         => __( 'File', 'wp-vimeo-videos' ),
				'privacy_view' => __( 'Who can view this video?', 'wp-vimeo-videos' ),
			),
			'phrases'         => array(
				'title'                 => apply_filters( 'dgv_upload_modal_title', __( 'Insert Vimeo Video', 'wp-vimeo-videos' ) ),
				'http_error'            => __( 'Sorry there was a HTTP error. Please check the server logs or contact support.', 'wp-vimeo-videos' ),
				'upload_invalid_file'   => __( 'Please select valid video file.', 'wp-vimeo-videos' ),
				'invalid_search_phrase' => __( 'Invalid search phrase. Please enter valid search phrase.', 'wp-vimeo-videos' ),
				'videos_not_found'      => __( 'No uploaded videos found.', 'wp-vimeo-videos' ),
				'search_not_found'      => __( 'No matching videos found for your search', 'wp-vimeo-videos' ),
			),
			'methods'         => wvv_get_editor_insert_methods(),
		);
	}

	/**
	 * Create nonce
	 */
	public function get_nonce() {
		static $dgv_nonce;
		if ( empty( $dgv_nonce ) ) {
			$dgv_nonce = wp_create_nonce( 'dgvsecurity' );
		}

		return $dgv_nonce;
	}

}
