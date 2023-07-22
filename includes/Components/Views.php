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

namespace Vimeify\Core\Components;

use Vimeify\Core\Abstracts\Interfaces\SystemComponentInterface;
use Vimeify\Core\Abstracts\Interfaces\SystemInterface;
use Vimeify\Core\Abstracts\Interfaces\ViewsInterface;

class Views implements ViewsInterface, SystemComponentInterface {

	/**
	 * The views path
	 * @var mixed|null
	 */
	protected $path = null;

	/**
	 * The system instance
	 * @var null
	 */
	protected $system = null;

	/**
	 * The constructor
	 *
	 * @param  SystemInterface  $system
	 * @param  array  $args
	 *
	 * @since 2.0.0
	 */
	public function __construct( SystemInterface $system, $args = [] ) {

		$this->system = $system;

		if ( empty( $args ) ) {
			$args = $this->system->config();
		}

		if ( isset( $args['views_path'] ) ) {
			$this->path = trailingslashit( $args['views_path'] );
		}
	}


	/**
	 * Renders view with data
	 *
	 * @param $view
	 * @param  array  $data
	 *
	 * @since    2.0.0
	 *
	 */
	public function render_view( $view, $data = array() ) {

		$path = $this->get_path( $view, false );
		if ( file_exists( $path ) ) {
			if ( ! empty( $data ) ) {
				extract( $data );
			}
			include( $path );
		}
		echo '';
	}

	/**
	 * Renders view with data
	 *
	 * @param $view
	 * @param  array  $data
	 *
	 * @return false|string
	 * @since    2.0.0
	 *
	 */
	public function get_view( $view, $data = array() ) {

		ob_start();
		$this->render_view( $view, $data );

		return ob_get_clean();
	}

	/**
	 * Returns the view path
	 *
	 * @param $view
	 * @param  bool  $dir
	 *
	 * @return string
	 * @since 2.0.0
	 */
	public function get_path( $view, $dir = false ) {
		$view = str_replace( '/', DIRECTORY_SEPARATOR, $view );

		return $dir ? $this->path . $view : $this->path . $view . '.php';
	}


}