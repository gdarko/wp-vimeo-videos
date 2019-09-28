<?php

/**
 * Whitelist embed domain for specific video
 *
 * @param $uri
 * @param null|\Vimeo\Vimeo $api
 */
function _dgv_after_upload_whitelist_domain( $uri, $api ) {

	// If uri is found, continue sending request to whitelist domain.
	if ( ! empty( $uri ) ) {
		try {
			$api->request( $uri . '/privacy/domains/mydomain.mk', [], 'PUT' );
		} catch ( \Exception $e ) {
			error_log( $e->getMessage() );
		}
	}

}
add_action( 'dgv_after_upload', '_dgv_after_upload_whitelist_domain' );
