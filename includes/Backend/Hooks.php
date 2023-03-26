<?php

namespace Vimeify\Core\Backend;

use Vimeify\Core\Abstracts\BaseProvider;

class Hooks extends BaseProvider {

	/**
	 * Registers sepcific piece of functionality
	 * @return void
	 */
	public function register() {
		$this->register_activation_hook();
	}

	/**
	 * Register installation / deactivation
	 * @return void
	 */
	public function register_activation_hook() {

		$file = $this->plugin->file();
		if ( empty( $file ) ) {
			return;
		}
		register_activation_hook( $file, function () {
			do_action( 'cv_plugin_activated', $this->plugin->id() );
			do_action( 'vimeify_plugin_activated', $this->plugin );
			$this->plugin->system()->settings()->import_defaults( false );
		} );

	}
}