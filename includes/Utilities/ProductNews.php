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

namespace Vimeify\Core\Utilities;

class ProductNews {

	/**
	 * The tag
	 * @var string
	 */
	protected $identifier;

	/**
	 * Cache time
	 * @var float|int
	 */
	protected $cache_ttl = 30 * 60;

	/**
	 * WP_CodeVerve_Product_News constructor.
	 *
	 * @param $identifier
	 */
	public function __construct( $identifier ) {
		$this->identifier = $identifier;
	}

	/**
	 * The news endpoint
	 * @var string
	 */
	protected $endpoint = 'https://codeverve.com/wp-json/dgupdater/v1/plugins/news';

	/**
	 * Return array of news
	 *
	 * @param  bool  $flush_cache
	 *
	 * @return array|WP_Error
	 */
	public function get( $flush_cache = false ) {

		if ( $flush_cache ) {
			delete_transient( 'dgv_news' );
		}

		$news = get_transient( 'dgv_news' );

		if ( false === $news ) {
			$url      = add_query_arg( 'product', $this->identifier, $this->endpoint );
			$response = wp_remote_get( $url );
			$news     = array();
			if ( is_wp_error( $response ) ) {
				error_log( 'DGV News Error: ' . $response->get_error_message() );
			} else {
				$data = $response['body'];
				$data = @json_decode( $data, true );
				if ( isset( $data['news'] ) && ! empty( $data['news'] ) ) {
					$news = $data['news'];
					set_transient( 'dgv_news', $news, $this->cache_ttl );
				}
			}
		}

		if ( ! is_array( $news ) ) {
			$news = array();
		}


		return $news;
	}

}