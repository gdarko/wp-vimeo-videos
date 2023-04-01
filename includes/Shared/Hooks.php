<?php

namespace Vimeify\Core\Shared;

use Vimeify\Core\Abstracts\BaseProvider;
use Vimeify\Core\Utilities\Validators\FileValidator;

class Hooks extends BaseProvider {

	/**
	 * Registers sepcific piece of functionality
	 * @return void
	 */
	public function register() {

		add_filter( 'upload_mimes', [ $this, 'allowed_mime_types' ], 15 );
		add_action( 'wp_vimeo_upload_process_default_time_limit', [ $this, 'upload_process_default_time_limit' ], 10, 1 );
		add_action( 'init', [ $this, 'load_text_domain' ] );

		$this->register_integrations();
	}

	/**
	 * Register integrations
	 * @return void
	 */
	public function register_integrations() {

		$integrations = $this->plugin->get_integrations();

		if ( empty( $integrations ) ) {
			return;
		}

		foreach ( $integrations as $integration ) {
			$integration->register();
		}
	}

	/**
	 * Enable custom extensions support
	 *
	 * @param $mimes
	 *
	 * @return mixed
	 */
	public function allowed_mime_types( $mimes ) {

		$file_validator = new FileValidator();

		foreach ( $file_validator->allowed_mimes() as $key => $mime ) {
			if ( ! isset( $mimes[ $key ] ) ) {
				$mimes[ $key ] = $mime;
			}
		}

		return $mimes;
	}

	/**
	 * Manually determine the default time limit of specific process
	 *
	 * @param $default
	 *
	 * @return int
	 * @since 1.4.0
	 */
	public function upload_process_default_time_limit( $default ) {
		$limit = (int) ini_get( 'max_execution_time' );
		if ( $limit === 0 ) {
			$default = 7200; // 2 hours.
		} elseif ( ( $limit - 10 ) < 0 ) {
			$default = 30;
		} else {
			$default = $limit - 10;
		}

		return $default;
	}

	/**
	 * Load the plugin textdomain
	 * @return void
	 */
	public function load_text_domain() {
		load_plugin_textdomain(
			$this->plugin->slug(),
			false,
			$this->plugin->path() . 'languages' . DIRECTORY_SEPARATOR
		);
	}

	/**
	 * Hook after the attempt was created.
	 *
	 * @param  int  $attempt
	 * @param  string  $uri
	 * @param  array  $args
	 *
	 * @throws VimeoRequestException
	 * @since 1.5.0
	 */
	public function frontend_after_attempt_created( $attempt, $uri, $args ) {

		$context = 'frontend';
		$logtag  = $this->get_logtag( $context );

		if ( isset( $args['process_hooks'] ) && ! $args['process_hooks'] ) {
			$this->plugin->system()->logger()->log( 'No hooks processing required. (frontend)', $logtag );

			return;
		}

		/**
		 * Make sure we are on the right track.
		 */
		if ( ! isset( $args['vimeo_id'] ) ) {
			$this->plugin->system()->logger()->log( 'No vimeo id found. Failed to execute post upload hooks. (frontend)', $logtag );

			return;
		}

		/*
		 * Singal start.
		 */
		$this->plugin->system()->logger()->log( sprintf( 'Processing hooks for %s', $uri ), $logtag );

		/**
		 * Set embed privacy
		 */
		if ( $this->plugin->system()->vimeo()->supports_embed_privacy() ) {
			$this->set_embed_privacy( $uri, $context );
		}

		/**
		 * Set Folder
		 */
		$profile_id = $this->plugin->system()->settings()->get_upload_profile_by_context( $context );
		if ( ! empty( $profile_id ) ) {
			$folder_uri = $this->plugin->system()->database()->get_upload_profile_option( $profile_id, 'folders.folder' );
			$this->set_folder( $uri, $folder_uri, $context );
		}

		/**
		 * Set Presets
		 */
		if ( $this->plugin->system()->vimeo()->supports_embed_presets() ) {
			$preset_uri = $this->plugin->system()->settings()->get( 'embed_presets.preset_frontend', 'default' );
			$this->set_embed_preset( $uri, $preset_uri, $context );
		}

		/**
		 * Set View privacy
		 */
		$view_privacy = isset( $args['overrides']['view_privacy'] ) ? $args['overrides']['view_privacy'] : $this->get_view_privacy( $context );
		if ( $this->plugin->system()->vimeo()->supports_view_privacy_option( $view_privacy ) ) {
			$this->set_view_privacy( $uri, $view_privacy, $context );
		}

		/**
		 * Create local video (Save the video in the database if enabled.)
		 */
		if ( (int) $this->plugin->system()->settings()->get( 'frontend.behavior.store_in_library', 0 ) ) {
			$this->create_local_video( $args, $context );
		}
	}
}