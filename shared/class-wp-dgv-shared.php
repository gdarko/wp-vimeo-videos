<?php
/********************************************************************
 * Copyright (C) 2020 Darko Gjorgjijoski (https://codeverve.com)
 *
 * This file is part of Video Uploads for Vimeo PRO
 *
 * Video Uploads for Vimeo PRO is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * Video Uploads for Vimeo PRO is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Video Uploads for Vimeo PRO. If not, see <https://www.gnu.org/licenses/>.
 **********************************************************************/

/**
 * Class WP_DGV_Shared
 *
 * The shared functionality of the plugin (admin/frontend)
 *
 * @license GPLv2
 * @copyright Darko Gjorgjijoski <info@codeverve.com>
 * @since 1.0.0
 */
class WP_DGV_Shared {

	/**
	 * The settings
	 * @var WP_DGV_Settings_Helper
	 */
	protected $settings_helper;

	/**
	 * The API Helper
	 * @var WP_DGV_Api_Helper
	 */
	protected $api_helper;

	/**
	 * WP_DGV_Shared constructor.
	 */
	public function __construct() {
		$this->settings_helper = new WP_DGV_Settings_Helper();
		$this->api_helper      = new WP_DGV_Api_Helper();
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

	/**
	 * Check if it is possible to enqueue the Vimeo plugin for tinymce.
	 * @return bool
	 */
	public function can_enqueue_vimeo_tinymce() {
		$is_gutenberg = is_admin() && wvv_is_gutenberg_active();

		if ( $is_gutenberg ) {
			return false;
		}

		if ( ! apply_filters( 'dgv_enable_tinymce_upload_plugin', true ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Register Vimeo Button
	 *
	 * @param $buttons
	 *
	 * @return mixed
	 */
	public function tinymce_vimeo_button( $buttons ) {

		if ( $this->can_enqueue_vimeo_tinymce() ) {
			array_push( $buttons, 'dgv_vimeo_button' );
		}

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

		if ( $this->can_enqueue_vimeo_tinymce() ) {
			$plugin_array['dgv_vimeo_button'] = WP_VIMEO_VIDEOS_URL . 'shared/js/tinymce-upload.js';
		}

		return $plugin_array;
	}

	/**
	 * Add editor styles
	 */
	public function tinymce_styles() {
		/*if ( ! wp_script_is( 'dgv-upload-modal', 'enqueued' ) ) {
			add_editor_style( WP_VIMEO_VIDEOS_URL . 'shared/css/upload-modal.css' );
		}*/
		// Disabled, throws wp_script_is() warning.
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
	 * Register all the resources
	 */
	public function register_scripts() {

		wp_register_script(
			'dgv-select2',
			WP_VIMEO_VIDEOS_URL . 'shared/resources/select2/select2.min.js',
			null,
			'4.0.12',
			true
		);

		wp_register_style(
			'dgv-select2',
			WP_VIMEO_VIDEOS_URL . 'shared/resources/select2/select2.min.css',
			array(),
			'4.0.12',
			'all'
		);

		wp_register_script(
			'dgv-swal',
			WP_VIMEO_VIDEOS_URL . 'shared/resources/sweetalert2/sweetalert2.min.js',
			null,
			'11.1.4',
			true
		);

		wp_register_script(
			'dgv-tus',
			WP_VIMEO_VIDEOS_URL . 'shared/resources/tus-js-client/tus.min.js',
			null, '1.8.0'
		);

		wp_register_script(
			'dgv-uploader',
			WP_VIMEO_VIDEOS_URL . 'shared/js/uploader.js',
			array( 'jquery', 'dgv-tus' ),
			filemtime( WP_VIMEO_VIDEOS_PATH . 'shared/js/uploader.js' )
		);

		wp_register_script(
			'dgv-upload-modal',
			WP_VIMEO_VIDEOS_URL . 'shared/js/upload-modal.js',
			array( 'jquery', 'dgv-uploader', 'dgv-swal' ),
			filemtime( WP_VIMEO_VIDEOS_PATH . 'shared/js/upload-modal.js' )
		);

		$modal_config = $this->get_modal_config_params();
		wp_localize_script( 'dgv-upload-modal', 'DGV_Modal_Config', $modal_config );

		wp_register_script(
			'dgv-admin',
			WP_VIMEO_VIDEOS_URL . 'admin/js/admin.js',
			array(
				'jquery',
				'dgv-uploader'
			),
			filemtime( WP_VIMEO_VIDEOS_PATH . 'admin/js/admin.js' ),
			true
		);

		wp_localize_script( 'dgv-admin', 'DGVAdmin', array(
			'phrases' => array(
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

		wp_register_script(
			'wvv-vimeo-upload-block',
			WP_VIMEO_VIDEOS_URL . 'admin/blocks/upload/script.js',
			array(
				'wp-blocks',
				'wp-editor',
				'jquery',
				'dgv-uploader'
			),
			filemtime( WP_VIMEO_VIDEOS_PATH . 'admin/blocks/upload/script.js' )
		);

		wp_register_style(
			'wvv-vimeo-upload-block',
			WP_VIMEO_VIDEOS_URL . 'admin/blocks/upload/style.css',
			array(),
			filemtime( WP_VIMEO_VIDEOS_PATH . 'admin/blocks/upload/style.css' ),
			'all'
		);

		wp_register_style(
			'dgv-admin',
			WP_VIMEO_VIDEOS_URL . 'admin/css/admin.css',
			array(),
			filemtime( WP_VIMEO_VIDEOS_PATH . 'admin/css/admin.css' ),
			'all'
		);

		wp_register_style(
			'dgv-upload-modal',
			WP_VIMEO_VIDEOS_URL . 'shared/css/upload-modal.css',
			array(),
			filemtime( WP_VIMEO_VIDEOS_PATH . 'shared/css/upload-modal.css' ),
			'all'
		);
	}

	/**
	 * Enqueues scripts
	 * @return void
	 */
	public function enqueue_scripts() {

		if ( $this->can_enqueue_vimeo_tinymce() && wp_script_is( 'wp-tinymce', 'enqueued' ) ) {
			$this->enqueue_tinymce_assets();
		}
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
		$mce_icon_url = $mce_icon ? apply_filters( 'dgv_mce_toolbar_icon_url', esc_url( wvv_get_vimeo_icon_url() ) ) : null;
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
			'markup'   => apply_filters( 'dgv_mce_output_markup', '[dgv_vimeo_video id="{id}"]' )
		) );
		wp_localize_script( 'dgv-upload-modal', 'DGV_Modal_Config', $this->get_modal_config_params() );
	}


	/**
	 * The modal config params
	 * @return array
	 */
	private function get_modal_config_params() {
		return array(
			'nonce'               => $this->get_nonce(),
			'ajax_url'            => admin_url( 'admin-ajax.php' ),
			'access_token'        => $this->settings_helper->get( 'dgv_access_token' ),
			'default_privacy'     => $this->settings_helper->get_default_admin_view_privacy(),
			'enable_vimeo_search' => true,
			'enable_local_search' => true,
			'words'               => array(
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
			'phrases'             => array(
				'title'                 => apply_filters( 'dgv_upload_modal_title', __( 'Insert Vimeo Video', 'wp-vimeo-videos' ) ),
				'http_error'            => __( 'Sorry there was a HTTP error. Please check the server logs or contact support.', 'wp-vimeo-videos' ),
				'upload_invalid_file'   => __( 'Please select valid video file.', 'wp-vimeo-videos' ),
				'invalid_search_phrase' => __( 'Invalid search phrase. Please enter valid search phrase.', 'wp-vimeo-videos' ),
				'videos_not_found'      => __( 'No uploaded videos found.', 'wp-vimeo-videos' ),
				'search_not_found'      => __( 'No matching videos found for your search', 'wp-vimeo-videos' ),
				'cancel_upload_confirm' => esc_html__('Are you sure you want to cancel the upload?', 'wp-vimeo-videos')
			),
			'methods'             => wvv_get_editor_insert_methods(),
			'upload_form_options' => array(
				'enable_privacy_option' => false,
				'privacy_view'          => is_admin() ? $this->api_helper->get_view_privacy_options_for_forms( 'admin' ) : null,
			)
		);
	}
}
