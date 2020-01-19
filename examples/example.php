<?php

/**
 * Whitelist embed domain for specific video
 *
 * @param $response
 * @param null|\Vimeo\Vimeo $api
 */
function _dgv_after_upload_whitelist_domain( $response, $api ) {

	$domains_to_whitelist = array('domain1.com', 'domain2.com');

	if ( ! is_array( $domains_to_whitelist ) || count( $domains_to_whitelist ) === 0 ) {
		return;
	}
	$uri = wvv_response_to_uri( $response );

	if ( ! empty( $uri ) ) {
		try {
			$api_helper = new WP_DGV_Api_Helper();
			$api_helper->set_embed_privacy( $uri, 'whitelist' );
			foreach ( $domains_to_whitelist as $domain ) {
				$api_helper->whitelist_domain_add( $uri, $domain );
			}
		} catch ( \Exception $e ) {
			error_log( 'Vimeo Whitelist: ' . $e->getMessage() );
		}
	} else {
		error_log( 'Vimeo Whitelist: Video not found.' );
	}
}
add_action( 'dgv_after_upload', '_dgv_after_upload_whitelist_domain', 100, 2 );