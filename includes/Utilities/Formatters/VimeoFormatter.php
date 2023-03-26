<?php

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