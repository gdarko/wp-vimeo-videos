<?php

namespace Vimeify\Core\Frontend;

use Vimeify\Core\Abstracts\BaseProvider;

class Scripts extends BaseProvider {

	/**
	 * Registers sepcific piece of functionality
	 * @return void
	 */
	public function register() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_register_style(
			$this->plugin->slug(),
			$this->plugin->url() . 'assets/frontend/css/main.css',
			array(),
			$this->plugin->plugin_version(),
			'all'
		);
	}


}