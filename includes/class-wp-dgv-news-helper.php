<?php

/**
 * The WordPress news helper class
 *
 * @since      1.0.0
 * @package    WP_DGV
 * @subpackage WP_DGV/includes
 * @copyright     Darko Gjorgjijoski <info@codeverve.com>
 * @license    GPLv2
 */
class WP_DGV_Product_News_Helper {

	/**
	 * The plugin handle
	 * @var string
	 */
	protected $handle = 'wp-vimeo-videos';

	/**
	 * Cache time
	 * @var float|int
	 */
	protected $cache_ttl = 30 * 60;

	/**
	 * The news endpoint
	 * @var string
	 */
	protected $endpoint = 'https://codeverve.com/wp-json/dgupdater/v1/plugins/news';

	/**
	 * Return array of news
	 *
	 * @param bool $flush_cache
	 *
	 * @return array|WP_Error
	 */
	public function get( $flush_cache = false ) {

		if ( $flush_cache ) {
			delete_transient( 'dgv_news' );
		}

		$news = get_transient( 'dgv_news' );

		if ( false === $news ) {
			$url      = add_query_arg( 'product', $this->handle, $this->endpoint );
			$response = wp_remote_get( $url );
			$news     = array();
			if ( is_wp_error( $response ) ) {
				error_log( 'DGV News Error: ' . $news->get_error_message() );
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