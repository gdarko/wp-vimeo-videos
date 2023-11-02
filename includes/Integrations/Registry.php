<?php

namespace Vimeify\Core\Integrations;

use Vimeify\Core\Abstracts\BaseProvider;
use Vimeify\Core\Integrations\Bricks\Bricks;
use Vimeify\Core\Integrations\Elementor\Elementor;

class Registry extends BaseProvider {

	/**
	 * Registers sepcific piece of functionality
	 * @return void
	 */
	public function register() {
		add_action( 'vimeify_booting', [ $this, 'on_booting' ] );
	}

	/**
	 * Register the integrations
	 *
	 * @param Boot $boot
	 *
	 * @return void
	 */
	public function on_booting( $boot ) {
		$boot->plugin->add_integration( new Bricks( $this->plugin ) );
		$boot->plugin->add_integration( new Elementor( $this->plugin ) );
	}
}