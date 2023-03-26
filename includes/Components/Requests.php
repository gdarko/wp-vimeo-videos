<?php

namespace Vimeify\Core\Components;

use Vimeify\Core\Abstracts\Interfaces\RequestsInterface;
use Vimeify\Core\Abstracts\Interfaces\SystemComponentInterface;
use Vimeify\Core\Abstracts\Interfaces\SystemInterface;

class Requests implements RequestsInterface, SystemComponentInterface {

	/**
	 * The system instance
	 * @var SystemComponentInterface
	 */
	protected $system;

	/**
	 * Logger constructor.
	 */
	public function __construct( SystemInterface $system, $args = [] ) {
		$this->system = $system;
	}

	/**
	 * Utility function to check if the request is GET
	 * @return bool
	 */
	public function is_http_get() {
		return $_SERVER['REQUEST_METHOD'] === 'GET';
	}

	/**
	 * Utility function to check if the request is POST
	 * @return bool
	 */
	public function is_http_post() {
		return $_SERVER['REQUEST_METHOD'] === 'POST';
	}

	/**
	 * Utility function to chec kif the request is secure
	 *
	 * @param $nonce_name
	 *
	 * @return bool|int
	 */
	public function check_ajax_referer( $nonce_name ) {
		return check_ajax_referer( $nonce_name, '_wpnonce', false );
	}
}