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

namespace Vimeify\Core\Frontend;

use Vimeify\Core\Abstracts\BaseProvider;

class Scripts extends BaseProvider {

	/**
	 * Registers sepcific piece of functionality
	 * @return void
	 */
	public function register() {
		add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ) );
	}

	/**
	 * Register scripts
	 * @return void
	 */
	public function register_scripts() {
		$this->register_styles();
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	private function register_styles() {

		// Video Element
		wp_enqueue_style(
			'dgv-frontend-video',
			$this->plugin->url() . 'assets/frontend/dist/styles/video.min.css',
			array('dgv-iconfont'),
			$this->plugin->plugin_version(),
			'all'
		);
		wp_register_script(
			'dgv-frontend-video',
			$this->plugin->url() . 'assets/frontend/dist/scripts/video.min.js',
			array(),
			$this->plugin->plugin_version(),
			true
		);

		// Videos Table Element
		wp_register_style(
			'dgv-frontend-videos-table',
			$this->plugin->url() . 'assets/frontend/dist/styles/videos-table.min.css',
			array(),
			$this->plugin->plugin_version(),
			'all'
		);
	}
}