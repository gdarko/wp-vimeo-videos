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

namespace Vimeify\Core\Shared;

use Vimeify\Core\Abstracts\BaseProvider;
use Vimeify\Core\Utilities\Validators\RequestValidator;
use Vimeify\Core\Utilities\Validators\WPValidator;

class Scripts extends BaseProvider {

	/**
	 * Registers sepcific piece of functionality
	 * @return void
	 */
	public function register() {
		add_action( 'wp_enqueue_scripts', [ $this, 'register_scripts' ], 0 );
		add_action( 'admin_enqueue_scripts', [ $this, 'register_scripts' ], 0 );

		add_action( 'wp_enqueue_editor', [ $this, 'enqueue_scripts_tinymce' ], 1000 );

		add_action( 'before_wp_tiny_mce', [ $this, 'tinymce_globals' ] );
		add_action( 'after_setup_theme', [ $this, 'tinymce_styles' ] );
		add_filter( 'mce_buttons', [ $this, 'tinymce_vimeo_button' ] );
		add_filter( 'mce_external_plugins', [ $this, 'tinymce_vimeo_plugin' ] );
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

		$wp_validator = new WPValidator();

		$is_gutenberg = is_admin() && $wp_validator->is_gutenberg_active();

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
			$plugin_array['dgv_vimeo_button'] = $this->plugin->url() . 'assets/shared/js/tinymce-upload.js';
		}

		return $plugin_array;
	}

	/**
	 * Add editor styles
	 */
	public function tinymce_styles() {
		/*if ( ! wp_script_is( 'dgv-upload-modal', 'enqueued' ) ) {
			add_editor_style( $this->plugin->url() . 'shared/css/upload-modal.css' );
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

		$request_validator = new RequestValidator();

		wp_register_script(
			'dgv-http',
			$this->plugin->url() . 'assets/shared/js/http.js',
			null,
			null,
			true
		);

		wp_register_script(
			'dgv-dropzone',
			$this->plugin->url() . 'assets/resources/dropzone/dropzone.min.js',
			null,
			null,
			true
		);

		wp_register_style(
			'dgv-dropzone',
			$this->plugin->url() . 'assets/resources/dropzone/dropzone.min.css',
			null,
			null,
			'all'
		);

		wp_register_script(
			'dgv-chunked-upload',
			$this->plugin->url() . 'assets/shared/js/chunked-upload.js',
			array( 'wp-util', 'dgv-dropzone', 'jquery' ),
			filemtime( $this->plugin->path() . 'assets/shared/js/chunked-upload.js' ),
			true
		);

		wp_localize_script( 'dgv-chunked-upload', 'DGV_CHUNKED_UPLOAD', array(
			'url'             => admin_url( 'admin-ajax.php' ),
			'errors'          => array(
				'file_not_uploaded' => esc_html__( 'This file was not uploaded.', 'wp-vimeo-videos' ),
				'file_limit'        => esc_html__( 'File limit has been reached ({fileLimit}).', 'wp-vimeo-videos' ),
				'file_extension'    => esc_html__( 'File type is not allowed.', 'wp-vimeo-videos' ),
				'file_size'         => esc_html__( 'File exceeds the max size allowed.', 'wp-vimeo-videos' ),
				'post_max_size'     => sprintf( /* translators: %s - max allowed file size by a server. */
					esc_html__( 'File exceeds the upload limit allowed (%s).', 'wp-vimeo-videos' ),
					$request_validator->max_upload_size()
				),
			),
			'loading_message' => esc_html__( 'File upload is in progress. Please submit the form once uploading is completed.', 'wp-vimeo-videos' ),
		) );

		wp_register_script(
			'dgv-select2',
			$this->plugin->url() . 'assets/resources/select2/select2.min.js',
			null,
			'4.0.12',
			true
		);

		wp_register_style(
			'dgv-select2',
			$this->plugin->url() . 'assets/resources/select2/select2.min.css',
			array(),
			'4.0.12',
			'all'
		);

		wp_register_script(
			'dgv-swal',
			$this->plugin->url() . 'assets/resources/sweetalert2/sweetalert2.min.js',
			null,
			'11.1.4',
			true
		);

		wp_register_script(
			'dgv-tus',
			$this->plugin->url() . 'assets/resources/tus-js-client/tus.min.js',
			null, '1.8.0'
		);

		wp_register_script(
			'dgv-uploader',
			$this->plugin->url() . 'assets/shared/js/uploader.js',
			array( 'jquery', 'dgv-tus' ),
			filemtime( $this->plugin->path() . 'assets/shared/js/uploader.js' )
		);

		wp_register_script(
			'dgv-upload-modal',
			$this->plugin->url() . 'assets/shared/js/upload-modal.js',
			array( 'jquery', 'dgv-uploader', 'dgv-swal' ),
			filemtime( $this->plugin->path() . 'assets/shared/js/upload-modal.js' )
		);

		$modal_config = $this->get_modal_config_params();
		wp_localize_script( 'dgv-upload-modal', 'DGV_Modal_Config', $modal_config );

		wp_register_style(
			'dgv-upload-modal',
			$this->plugin->url() . 'assets/shared/css/upload-modal.css',
			array(),
			filemtime( $this->plugin->path() . 'assets/shared/css/upload-modal.css' ),
			'all'
		);
	}

	/**
	 * Enqueues scripts
	 * @return void
	 */
	public function enqueue_scripts_tinymce( $config ) {
		if ( isset( $config['tinymce'] ) && $config['tinymce'] && $this->can_enqueue_vimeo_tinymce() ) {
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
		$mce_icon_url = $mce_icon ? apply_filters( 'dgv_mce_toolbar_icon_url', $this->plugin->icon() ) : null;
		$mce_text     = apply_filters( 'dgv_mce_toolbar_title', __( 'Vimeo', 'wp-vimeo-videos' ) );
		$mce_text     = $mce_icon && $mce_text ? sprintf( ' %s', $mce_text ) : $mce_text;
		$mce_tooltip  = apply_filters( 'dgv_mce_toolbar_tooltip', __( 'Insert Vimeo Video', 'wp-vimeo-videos' ) );
		wp_localize_script( 'wp-tinymce', 'DGV_MCE_Config', array(
			'phrases'  => array(
				'tmce_title'            => $mce_text,
				'tmce_tooltip'          => $mce_tooltip,
				'cancel_upload_confirm' => esc_html__( 'Are you sure you want to cancel the upload?', 'wp-vimeo-videos' ),
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
			'access_token'        => $this->plugin->system()->settings()->get( 'api_credentials.access_token' ),
			'default_privacy'     => $this->plugin->system()->settings()->get_default_view_privacy('admin_classic'),
			'enable_vimeo_search' => $this->plugin->system()->settings()->get( 'admin.tinymce.enable_account_search' ),
			'enable_local_search' => $this->plugin->system()->settings()->get( 'admin.tinymce.enable_local_search' ),
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
				'cancel_upload_confirm' => esc_html__( 'Are you sure you want to cancel the upload?', 'wp-vimeo-videos' )
			),
			'methods'             => array(
				'upload' => __( 'Upload new Vimeo video', 'wp-vimeo-videos' ),
				'local'  => __( 'Insert Vimeo video from local library', 'wp-vimeo-videos' ),
				'search' => __( 'Search your Vimeo account', 'wp-vimeo-videos' ),
			),
			'upload_form_options' => array(
				'enable_view_privacy' => (int) $this->plugin->system()->settings()->get( 'admin.tinymce.enable_view_privacy', 0 ),
				'privacy_view'          => is_admin() ? $this->plugin->system()->vimeo()->get_view_privacy_options_for_forms( 'admin' ) : null,
			)
		);
	}
}