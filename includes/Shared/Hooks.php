<?php
/********************************************************************
 * Copyright (C) 2023 Darko Gjorgjijoski (https://ideologix.com)
 *
 * This file is part of "Vimeify - Video Uploads for Vimeo"
 *
 * Vimeify - Video Uploads for Vimeo is free software: you can redistribute it
 * and/or modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation, either version 2 of the License,
 * or (at your option) any later version.
 *
 * Vimeify - Video Uploads for Vimeo is distributed in the hope that
 * it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with "Vimeify - Video Uploads for Vimeo". If not, see <https://www.gnu.org/licenses/>.
 *
 * ---
 *
 * Author Note: This code was written by Darko Gjorgjijoski <dg@darkog.com>
 * If you have any questions find the contact details in the root plugin file.
 *
 **********************************************************************/

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