<?php

namespace Vimeify\Core\Abstracts\Interfaces;

interface ViewsInterface {

	/**
	 * Renders view with data
	 *
	 * @param $view
	 * @param  array  $data
	 *
	 * @return false|string
	 * @since 2.0.0
	 *
	 */
	public function get_view( $view, $data = array() );

	/**
	 * Returns the view path
	 *
	 * @param $view
	 * @param  bool  $dir
	 *
	 * @return string
	 * @since 2.0.0
	 *
	 */
	public function get_path( $view, $dir = false );

}