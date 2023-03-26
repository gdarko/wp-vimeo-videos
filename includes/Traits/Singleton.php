<?php

namespace Vimeify\Core\Traits;

trait Singleton {

	/**
	 * The singleton instance
	 * @var static
	 */
	private static $instance = null;

	protected function __construct() {
	}

	private function __clone() {
	}

	/**
	 * The singleton instance
	 * @return null|static
	 */
	public static function instance() {
		if ( static::$instance === null ) {
			static::$instance = new static();
		}

		return static::$instance;
	}

}