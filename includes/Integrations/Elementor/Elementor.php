<?php

namespace Vimeify\Core\Integrations\Elementor;

use Vimeify\Core\Abstracts\BaseProvider;
use Vimeify\Core\Abstracts\Interfaces\IntegrationInterface;
use Vimeify\Core\Integrations\Elementor\Elements\VideosTable;

class Elementor extends BaseProvider implements IntegrationInterface {

	/**
	 * Registers sepcific piece of functionality
	 * @return void
	 */
	public function register() {
		$this->activate();
	}

	/**
	 * Check if the integration can be activated.
	 * @return bool
	 */
	public function can_activate() {
		return defined( 'ELEMENTOR_VERSION' );
	}

	/**
	 * Activates the integration
	 * @return bool
	 */
	public function activate() {

		if ( ! $this->can_activate() ) {
			return false;
		}

		/**
		 * Register oEmbed Widget.
		 *
		 * Include widget file and register widget class.
		 *
		 * @param \Elementor\Widgets_Manager $widgets_manager Elementor widgets manager.
		 *
		 * @return void
		 * @since 1.0.0
		 */
		add_action( 'elementor/widgets/register', function ( $widgets_manager ) {

			/* @var  \Elementor\Widgets_Manager $widgets_manager */

			$widgets_manager->register( new VideosTable );

		} );

		return true;
	}
}