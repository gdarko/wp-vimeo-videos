<?php

namespace Vimeify\Core;

use Vimeify\Core\Abstracts\BaseProvider;
use Vimeify\Core\Backend\Backend;
use Vimeify\Core\Frontend\Frontend;
use Vimeify\Core\Shared\Shared;
use Vimeify\Core\Utilities\ProcessManager;

class Boot extends BaseProvider {

	/**
	 * The frontend hooks
	 * @var Frontend
	 */
	public $frontend;

	/**
	 * The backend hooks
	 * @var Backend
	 */
	public $backend;

	/**
	 * The shared hooks
	 * @var Shared
	 */
	public $shared;

	/**
	 * Registers sepcific piece of functionality
	 * @return void
	 */
	public function register() {

		ProcessManager::create( $this->plugin() );

		do_action( 'vimeify_booting', $this );

		$this->shared   = $this->boot( Shared::class );
		$this->frontend = $this->boot( Frontend::class );
		$this->backend  = $this->boot( Backend::class );

		do_action( 'vimeify_booted', $this );
	}


	/**
	 * The plugin
	 * @return Abstracts\Interfaces\PluginInterface
	 */
	public function plugin() {
		return $this->plugin;
	}

	/**
	 * The backend
	 * @return Backend
	 */
	public function backend() {
		return $this->backend;
	}

	/**
	 * The frontend
	 * @return Frontend
	 */
	public function frontend() {
		return $this->frontend;
	}

	/**
	 * The shared
	 * @return Shared
	 */
	public function shared() {
		return $this->shared;
	}

}