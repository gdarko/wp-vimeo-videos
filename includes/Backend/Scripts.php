<?php
/********************************************************************
 * Copyright (C) 2023 Darko Gjorgjijoski (https://darkog.com/)
 * Copyright (C) 2023 IDEOLOGIX MEDIA Dooel (https://ideologix.com/)
 *
 * This file is property of IDEOLOGIX MEDIA Dooel (https://ideologix.com)
 * This file is part of Vimeify Plugin - https://wordpress.org/plugins/vimeify/
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

namespace Vimeify\Core\Backend;

use Vimeify\Core\Abstracts\Interfaces\ProviderInterface;
use Vimeify\Core\Plugin;
use Vimeify\Core\Utilities\Validators\WPValidator;

class Scripts implements ProviderInterface {

	/**
	 * The plugin instance
	 * @var Plugin
	 */
	protected $plugin;

	/**
	 * The plugin
	 *
	 * @param Plugin $plugin
	 */
	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * Registers sepcific piece of functionality
	 * @return void
	 */
	public function register() {
		add_action( 'admin_enqueue_scripts', [ $this, 'register_and_enqueue' ], 5 );
	}

	/**
	 * Register and enqueue assets
	 * @return void
	 */
	public function register_and_enqueue() {
		$this->register_assets();
		$this->enqueue_assets();
	}

	/**
	 * Register scripts
	 * @return void
	 */
	public function register_assets() {

		wp_register_script(
			'dgv-admin',
			$this->plugin->url() . 'assets/admin/dist/scripts/main.min.js',
			array(
				'jquery',
				'dgv-uploader',
				'dgv-http',
			),
			filemtime( $this->plugin->path() . 'assets/admin/dist/scripts/main.min.js' ),
			true
		);

		wp_localize_script( 'dgv-admin', 'DGVAdmin', array(
			'phrases' => array(
				'select2' => array(
					'errorLoading'    => __( 'The results could not be loaded.', 'vimeify' ),
					'inputTooLong'    => __( 'Please delete {number} character', 'vimeify' ),
					'inputTooShort'   => __( 'Please enter {number} or more characters', 'vimeify' ),
					'loadingMore'     => __( 'Loading more results...', 'vimeify' ),
					'maximumSelected' => __( 'You can only select {number} item', 'vimeify' ),
					'noResults'       => __( 'No results found', 'vimeify' ),
					'searching'       => __( 'Searching...', 'vimeify' ),
					'removeAllItems'  => __( 'Remove all items', 'vimeify' ),
					'removeItem'      => __( 'Remove item', 'vimeify' ),
					'search'          => __( 'Search', 'vimeify' ),
				)
			)
		) );

		wp_register_script(
			'wvv-vimeo-upload-block',
			$this->plugin->url() . 'assets/blocks-legacy/upload/main.js',
			array(
				'wp-blocks',
				'wp-editor',
				'jquery',
				'dgv-uploader'
			),
			filemtime( $this->plugin->path() . 'assets/blocks-legacy/upload/main.js' )
		);

		wp_register_style(
			'wvv-vimeo-upload-block',
			$this->plugin->url() . 'assets/blocks-legacy/upload/main.css',
			array(),
			filemtime( $this->plugin->path() . 'assets/blocks-legacy/upload/main.css' ),
			'all'
		);

		wp_register_style(
			'dgv-admin',
			$this->plugin->url() . 'assets/admin/dist/styles/main.min.css',
			array(),
			filemtime( $this->plugin->path() . 'assets/admin/dist/styles/main.min.css' ),
			'all'
		);
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_assets() {

		if ( ! function_exists( 'get_current_screen' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/screen.php' );
		}

		// Validate the current screen
		$wp_validator             = new WPValidator();
		$current_screen           = get_current_screen();
		$is_edit_screen           = isset( $_GET['post'] ) && is_numeric( $_GET['post'] );
		$is_create_screen         = $current_screen->action === 'add' && $current_screen->base === 'post';
		$is_gutenberg_active      = $wp_validator->is_gutenberg_active();
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
			wp_enqueue_style( 'dgv-dropzone' );
		}


		// Enqueue admin styles
		wp_enqueue_style( 'dgv-admin' );

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

		$current_user_uploads      = ! current_user_can( 'administrator' ) && (int) $this->plugin->system()->settings()->get( 'admin.gutenberg.show_author_uploads_only', 0 );
		$uploads                   = $this->plugin->system()->database()->get_uploaded_videos( $current_user_uploads );
		$methods                   = array(
			'upload' => __( 'Upload new Vimeo video', 'vimeify' ),
			'local'  => __( 'Insert Vimeo video from local library', 'vimeify' ),
			'search' => __( 'Search your Vimeo account', 'vimeify' ),
		);
		$is_account_search_enabled = $this->plugin->system()->settings()->get( 'admin.gutenberg.enable_account_search', 1 );
		if ( ! $is_account_search_enabled ) {
			unset( $methods['search'] );
		}
		$is_local_search_enabled = $this->plugin->system()->settings()->get( 'admin.gutenberg.enable_local_search', 1 );
		if ( ! $is_local_search_enabled ) {
			unset( $methods['local'] );
		}

		wp_enqueue_script( 'wvv-vimeo-upload-block' );
		wp_localize_script( 'wvv-vimeo-upload-block', 'DGVGTB', array(
			'nonce'               => wp_create_nonce( 'dgvsecurity' ),
			'access_token'        => $this->plugin->system()->settings()->get( 'api_credentials.access_token' ),
			'enable_vimeo_search' => $this->plugin->system()->settings()->get( 'admin.gutenberg.enable_account_search' ),
			'default_privacy'     => $this->plugin->system()->settings()->get_default_view_privacy( 'admin_gutenberg' ),
			'ajax_url'            => admin_url( 'admin-ajax.php' ),
			'uploads'             => $uploads,
			'methods'             => $methods,
			'words'               => array(
				'block_name'   => __( 'Vimeify Upload (Old/Legacy/Deprecated)', 'vimeify' ),
				'title'        => __( 'Title', 'vimeify' ),
				'desc'         => __( 'Description', 'vimeify' ),
				'file'         => __( 'File', 'vimeify' ),
				'uploading3d'  => __( 'Uploading...', 'vimeify' ),
				'upload'       => __( 'Upload', 'vimeify' ),
				'search'       => __( 'Search', 'vimeify' ),
				'sorry'        => __( 'Sorry', 'vimeify' ),
				'privacy_view' => __( 'Who can view this video?', 'vimeify' ),
			),
			'phrases'             => array(
				'upload_invalid_file'               => __( 'Please select valid video file.', 'vimeify' ),
				'invalid_search_phrase'             => __( 'Invalid search phrase. Please enter valid search phrase.', 'vimeify' ),
				'enter_phrase'                      => __( 'Enter phrase', 'vimeify' ),
				'select_video'                      => __( 'Select video', 'vimeify' ),
				'upload_success'                    => __( 'Video uploaded successfully!', 'vimeify' ),
				'block_title'                       => __( 'Insert Vimeo Video', 'vimeify' ),
				'existing_not_visible_current_user' => __( '= Uploaded by someone else, not visible to you =', 'vimeify' ),
				'select_existing_video'             => __( 'Select existing video', 'vimeify' ),
			),
			'upload_form_options' => array(
				'enable_view_privacy' => (int) $this->plugin->system()->settings()->get( 'admin.gutenberg.enable_view_privacy', 0 ),
				'privacy_view'        => $this->plugin->system()->vimeo()->get_view_privacy_options_for_forms( 'admin' ),
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
			'access_token'                  => $this->plugin->system()->settings()->get( 'api_credentials.access_token' ),
			'default_privacy'               => $this->plugin->system()->settings()->get_default_view_privacy( 'admin_classic' ),
			'sorry'                         => __( 'Sorry', 'vimeify' ),
			'upload_invalid_file'           => __( 'Please select valid video file.', 'vimeify' ),
			'delete_not_allowed'            => __( 'Delete is not allowed because your account doesn\'t have the correct delete scope required by Vimeo.' ),
			'delete_confirm_title'          => __( 'Are you sure?', 'vimeify' ),
			'delete_confirm_desc'           => __( 'Are you sure you want to delete this video? This action deletes the video from the Vimeo and can not be reversed.', 'vimeify' ),
			'delete_whitelist_domain_error' => __( 'Sorry, the domain could not be deleted.', 'vimeify' ),
			'http_error'                    => __( 'Sorry there was a HTTP error. Please check the server logs or contact support.', 'vimeify' ),
			'success'                       => __( 'Success', 'vimeify' ),
			'cancel'                        => __( 'Cancel', 'vimeify' ),
			'confirm'                       => __( 'Confirm', 'vimeify' ),
			'close'                         => __( 'Close', 'vimeify' ),
			'remove_lower'                  => __( 'remove', 'vimeify' ),
			'delete_confirmation'           => __( 'Are you sure you want to delete this video?', 'vimeify' ),
			'delete_confirmation_yes'       => __( 'Yes, please', 'vimeify' ),
			'title'                         => __( 'Title', 'vimeify' ),
			'description'                   => __( 'Description', 'vimeify' ),
			'upload'                        => __( 'Upload', 'vimeify' ),
			'upload_to_vimeo'               => __( 'Upload to vimeo', 'vimeify' ),
			'correct_errors'                => __( 'Please correct the following errors', 'vimeify' ),
			'privacy_view'                  => __( 'Who can view this video?', 'vimeify' ),
			'problem_solution'              => __( 'Problem solution', 'vimeify' ),
			'loading'                       => __( 'Loading...', 'vimeify' ),
			'stats'                         => __( 'Statistics', 'vimeify' ),
			'explanation'                   => __( 'Explanation', 'vimeify' ),
			'upload_form_options'           => array(
				'enable_view_privacy' => (int) $this->plugin->system()->settings()->get( 'admin.upload_forms.enable_view_privacy', 0 ),
				'privacy_view'        => $this->plugin->system()->vimeo()->get_view_privacy_options_for_forms( 'admin' ),
			)
		) );
	}
}