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

use Vimeify\Core\Abstracts\Interfaces\RequestsInterface;
use Vimeify\Core\Abstracts\Interfaces\SystemComponentInterface;
use Vimeify\Core\Abstracts\Interfaces\SystemInterface;

class Requests implements RequestsInterface, SystemComponentInterface {

	/**
	 * The system instance
	 * @var SystemComponentInterface
	 */
	protected $system;

	/**
	 * Logger constructor.
	 */
	public function __construct( SystemInterface $system, $args = [] ) {
		$this->system = $system;
	}

	/**
	 * Utility function to check if the request is GET
	 * @return bool
	 */
	public function is_http_get() {
		return $_SERVER['REQUEST_METHOD'] === 'GET';
	}

	/**
	 * Utility function to check if the request is POST
	 * @return bool
	 */
	public function is_http_post() {
		return $_SERVER['REQUEST_METHOD'] === 'POST';
	}

	/**
	 * Utility function to chec kif the request is secure
	 *
	 * @param $nonce_name
	 *
	 * @return bool|int
	 */
	public function check_ajax_referer( $nonce_name ) {
		return check_ajax_referer( $nonce_name, '_wpnonce', false );
	}
}