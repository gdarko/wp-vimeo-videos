<?php

namespace Vimeify\Core\Integrations\Bricks;

use Vimeify\Core\Abstracts\BaseProvider;
use Vimeify\Core\Abstracts\Interfaces\IntegrationInterface;

class Bricks extends BaseProvider implements IntegrationInterface {

	/**
	 * Registers sepcific piece of functionality
	 * @return void
	 */
	public function register() {
		add_action( 'init', [ $this, 'activate' ], 150 );
	}

	/**
	 * Check if the integration can be activated.
	 * @return bool
	 */
	public function can_activate() {
		return defined( 'BRICKS_VERSION' );
	}

	/**
	 * Activates the integration
	 * @return bool
	 */
	public function activate() {

		if ( ! $this->can_activate() ) {
			return false;
		}

		$path = __DIR__ . DIRECTORY_SEPARATOR . 'Elements' . DIRECTORY_SEPARATOR;

		$files = [
			$path . 'VideosTable.php',
		];

		foreach ( $files as $file ) {
			\Bricks\Elements::register_element( $file );
		}

		return true;
	}
}