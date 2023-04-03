<?php

class VimeifyLoadNonComposerDependencies {

	/**
	 * The path
	 * @var string
	 */
	private $dir;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->dir = trailingslashit(dirname( __FILE__ ));

		$this->load_vimeo_php();
		$this->load_background_processing();

	}

	/**
	 * Loads the Vimeo PHP conditionally based on the environment.
	 * @return void
	 */
	public function load_vimeo_php() {

		$lib_path = null;

		/**
		 * If latest verison is allowed, attempt to determine it.
		 */
		if ( version_compare( PHP_VERSION, '7.2.5' ) >= 0 ) {
			if ( ! class_exists( "\GuzzleHttp\Client" ) ) {
				$lib_path = 'vimeo-php-guzzle7/vendor/autoload.php';
			} else {
				if ( defined( '\GuzzleHttp\Client::MAJOR_VERSION' ) ) {
					$lib_path = 'vimeo-php-guzzle7/vendor/autoload.php';
				} else {
					$lib_path = 'vimeo-php-guzzle6/vendor/autoload.php';
				}
			}
		}

		/**
		 * Fallback to the legacy version.
		 */
		if ( is_null( $lib_path ) ) {
			$lib_path = 'vimeo-php-legacy/autoload.php';
		}

		require_once $this->dir . $lib_path;
	}

	/**
	 * Load the background processing libraries
	 * @return void
	 */
	public function load_background_processing() {
		require_once $this->dir . 'wp-background-processing/class-wp-async-request.php';
		require_once $this->dir . 'wp-background-processing/class-wp-background-process.php';
	}
}

new VimeifyLoadNonComposerDependencies;