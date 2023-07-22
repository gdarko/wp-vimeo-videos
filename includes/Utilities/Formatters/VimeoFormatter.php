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

namespace Vimeify\Core\Utilities\Formatters;

class VimeoFormatter {

	/**
	 * Convert the embed preset uri to ID.
	 *
	 * @param $uri
	 *
	 * @return mixed
	 * @since 1.5.0
	 *
	 */
	public function embed_preset_uri_to_id( $uri ) {
		return $this->uri_to_id( $uri );
	}

	/**
	 * Convert Response to URI
	 *  -- Support for pull method which returns array structure ['body']['uri']
	 *  -- Support for upload stream method which returns the uri directly.
	 *
	 * @param $response
	 *
	 * @return string
	 */
	public function response_to_uri( $response ) {

		$uri = '';
		if ( isset( $response['body']['uri'] ) ) { // Support for pull method
			$uri = $response['body']['uri'];
		} else {
			if ( is_numeric( $response ) ) {
				$uri = sprintf( '/videos/%s', $response );
			} elseif ( is_string( $response ) ) { // Support for upload method.
				$id = $this->uri_to_id( $response );
				if ( is_numeric( $id ) ) {
					$uri = $response;
				}
			}
		}

		return $uri;
	}

	/**
	 * Convert Vimeo URI to ID
	 *
	 * @param $uri
	 *
	 * @return mixed
	 * @since 1.0.0
	 *
	 */
	public function uri_to_id( $uri ) {

		if ( is_array( $uri ) ) {
			if ( isset( $uri['body']['uri'] ) ) {
				$uri = $uri['body']['uri'];
			} elseif ( isset( $uri['response']['body']['uri'] ) ) {
				$uri = $uri['response']['body']['uri'];
			}
		}

		if ( ! is_string( $uri ) ) {
			return $uri;
		}

		$parts = explode( '/', $uri );

		return end( $parts );
	}

	/**
	 * Ensure that uri is always uri.
	 *
	 * @param $id
	 *
	 * @return string
	 */
	public function id_to_uri( $id ) {
		if ( is_numeric( $id ) || false === strpos( $id, '/' ) ) {
			return '/videos/' . $id;
		} else {
			return $id;
		}
	}

}