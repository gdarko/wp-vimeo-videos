<?php

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    WP_DGV
 * @subpackage WP_DGV/public
 * @copyright     Darko Gjorgjijoski <info@codeverve.com>
 * @license GPLv2
 */
class WP_DGV_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of the plugin.
	 * @param string $version The version of this plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_register_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wp-vimeo-videos-public.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_register_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wp-vimeo-videos-public.js', array( 'jquery' ), $this->version, false );
	}

	/**
	 * The video shortcode
	 *
	 * @param $atts
	 *
	 * @return false|string
	 */
	public function shortcode_video( $atts ) {
		$a = shortcode_atts( array( 'id' => '', ), $atts );
		$content  = '';
		$video_id = isset( $a['id'] ) ? $a['id'] : null;
		if ( ! empty( $video_id ) ) {
			wp_enqueue_style( $this->plugin_name );
			$content = wvv_get_view( 'public/partials/video', array(
				'vimeo_id' => $video_id
			) );
		}
		return apply_filters('dgv_shortcode_output', $content, $video_id);
	}

}
