<?php

class DGV_Shortcodes {
	/**
	 * Singleton instance
	 * @var DGV_Shortcodes
	 */
	protected static $instance = null;

	/**
	 * Singleton constructor
	 * @return DGV_Shortcodes
	 */
	public static function instance() {
		if ( ! isset( static::$instance ) ) {
			static::$instance = new self;
		}

		return static::$instance;
	}

	/**
	 * DGV_Backend constructor.
	 */
	protected function __construct() {
		add_shortcode( 'vimeo_video', array( $this, 'video' ) );
	}

	public function video( $atts ) {

		$a = shortcode_atts( array( 'id' => '', ), $atts );
		if ( ! empty( $a['id'] ) ) {

			wp_enqueue_style('dgv-vimeo-public');

			return dgv_get_view( 'video', array(
				'vimeo_id' => $a['id']
			) );

		} else {
			return '';
		}

	}
}

DGV_Shortcodes::instance();