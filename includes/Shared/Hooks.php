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
}