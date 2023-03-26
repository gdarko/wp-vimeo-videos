<?php

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