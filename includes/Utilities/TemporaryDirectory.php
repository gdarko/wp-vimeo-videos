<?php

namespace Vimeify\Core\Utilities;

class TemporaryDirectory {

	/**
	 * @var string
	 */
	public $path;

	/**
	 * @var string
	 */
	public $url;

	/**
	 * Constructor
	 * @param array $args
	 *
	 * @return void
	 */
	public function __construct( $args ) {
		$this->path = isset( $args['path'] ) ? $args['path'] : null;
		$this->url  = isset( $args['url'] ) ? $args['url'] : null;
	}
}