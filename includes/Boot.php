<?php
/********************************************************************
 * Copyright (C) 2023 Darko Gjorgjijoski (https://darkog.com/)
 * Copyright (C) 2023 IDEOLOGIX MEDIA Dooel (https://ideologix.com/)
 *
 * This file is property of IDEOLOGIX MEDIA Dooel (https://ideologix.com)
 * This file is part of Vimeify Plugin - https://wordpress.org/plugins/vimeify/
 *
 * Vimeify - Formerly "WP Vimeo Videos" is free software: you can redistribute
 * it and/or modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation, either version 2 of the License,
 * or (at your option) any later version.
 *
 * Vimeify - Formerly "WP Vimeo Videos" is distributed in the hope that it
 * will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this plugin. If not, see <https://www.gnu.org/licenses/>.
 *
 * Code developed by Darko Gjorgjijoski <dg@darkog.com>.
 **********************************************************************/

namespace Vimeify\Core;

use Vimeify\Core\Abstracts\BaseProvider;
use Vimeify\Core\Backend\Backend;
use Vimeify\Core\Frontend\Frontend;
use Vimeify\Core\Integrations\Registry;
use Vimeify\Core\RestAPI\RestAPI;
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
	 * The rest API
	 * @var RestAPI
	 */
	public $restApi;

	/**
	 * The integrations
	 * @var Registry
	 */
	public $integrations;

	/**
	 * Registers sepcific piece of functionality
	 * @return void
	 */
	public function register() {

		$this->integrations = $this->boot( Registry::class );

		$this->init_process_manager();

		do_action( 'vimeify_booting', $this );

		$this->shared   = $this->boot( Shared::class );
		$this->frontend = $this->boot( Frontend::class );
		$this->backend  = $this->boot( Backend::class );
		$this->restApi  = $this->boot( RestAPI::class );

		do_action( 'vimeify_booted', $this );
	}

	/**
	 * Initializes the process manager instance.
	 * @return void
	 */
	public function init_process_manager() {
		ProcessManager::create( $this->plugin() );
		ProcessManager::instance();
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

	public function restApi() {
		return $this->restApi;
	}

}