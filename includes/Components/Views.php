<?php

namespace Vimeify\Core\Components;

use Vimeify\Core\Abstracts\Interfaces\SystemComponentInterface;
use Vimeify\Core\Abstracts\Interfaces\SystemInterface;
use Vimeify\Core\Abstracts\Interfaces\ViewsInterface;

class Views implements ViewsInterface, SystemComponentInterface {

	/**
	 * The views path
	 * @var mixed|null
	 */
	protected $path = null;

	/**
	 * The system instance
	 * @var null
	 */
	protected $system = null;

	/**
	 * The constructor
	 *
	 * @param  SystemInterface  $system
	 * @param  array  $args
	 *
	 * @since 2.0.0
	 */
	public function __construct( SystemInterface $system, $args = [] ) {

		$this->system = $system;

		if ( empty( $args ) ) {
			$args = $this->system->config();
		}

		if ( isset( $args['views_path'] ) ) {
			$this->path = trailingslashit( $args['views_path'] );
		}
	}


	/**
	 * Renders view with data
	 *
	 * @param $view
	 * @param  array  $data
	 *
	 * @since    2.0.0
	 *
	 */
	public function render_view( $view, $data = array() ) {

		$path = $this->get_path( $view, false );
		if ( file_exists( $path ) ) {
			if ( ! empty( $data ) ) {
				extract( $data );
			}
			include( $path );
		}
		echo '';
	}

	/**
	 * Renders view with data
	 *
	 * @param $view
	 * @param  array  $data
	 *
	 * @return false|string
	 * @since    2.0.0
	 *
	 */
	public function get_view( $view, $data = array() ) {

		ob_start();
		$this->render_view( $view, $data );

		return ob_get_clean();
	}

	/**
	 * Returns the view path
	 *
	 * @param $view
	 * @param  bool  $dir
	 *
	 * @return string
	 * @since 2.0.0
	 */
	public function get_path( $view, $dir = false ) {
		$view = str_replace( '/', DIRECTORY_SEPARATOR, $view );

		return $dir ? $this->path . $view : $this->path . $view . '.php';
	}


}