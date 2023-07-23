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

namespace Vimeify\Core\RestAPI;

use Vimeify\Core\Abstracts\BaseProvider;
use WP_REST_Request;

class RestAPI extends BaseProvider {

	public function register() {
		add_action( 'rest_api_init', function () {
			register_rest_route( 'vimeify/v1', '/videos', array(
				'methods'             => 'GET',
				'callback'            => [ $this, 'videos_index' ],
				'permission_callback' => '__return_true',
			) );
		} );

		add_action( 'rest_api_init', function () {
			register_rest_route( 'vimeify/v1', '/folders', array(
				'methods'             => 'GET',
				'callback'            => [ $this, 'folders_index' ],
				'permission_callback' => '__return_true',
			) );
		} );
	}

	/**
	 * The video index endpoint callback
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return mixed
	 */
	public function videos_index( $request ) {

		if ( ! $this->plugin->system()->requests()->check_ajax_referer( 'wp_rest' ) ) {
			http_response_code( 403 );
			exit;
		}

		$params    = wp_parse_args( $request->get_query_params(), [ 'number' => 30 ] );
		$records   = $this->plugin->system()->database()->get_videos( $params );
		$formatted = [];
		foreach ( $records as $record ) {
			$formatted[] = array(
				'name' => $record->post_title,
				'uri'  => $this->plugin->system()->database()->get_vimeo_uri( $record->ID )
			);
		}

		return rest_ensure_response( [ 'data' => $formatted ] );
	}

	/**
	 * The folder index endpoint
	 * @param $request
	 *
	 * @return void|\WP_Error|\WP_HTTP_Response|\WP_REST_Response
	 */
	public function folders_index( $request ) {

		if ( ! $this->plugin->system()->requests()->check_ajax_referer( 'wp_rest' ) ) {
			http_response_code( 403 );
			exit;
		}

		$params = wp_parse_args( $request->get_query_params(), [ 'per_page' => 30, 'page' => 1, 'query' => ''] );
		try {
			$folders_response = $this->plugin->system()->vimeo()->get_folders_query( $params );
		} catch ( \Exception $e ) {
		}

		$records   = ! empty( $folders_response['results'] ) ? $folders_response['results'] : [];
		$formatted = [];
		foreach ( $records as $record ) {
			$formatted[] = [
				'name' => $record['name'],
				'uri'  => $record['uri'],
			];
		}

		return rest_ensure_response( [ 'data' => $formatted ] );
	}
}