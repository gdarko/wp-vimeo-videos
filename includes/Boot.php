<?php
/********************************************************************
 * Copyright (C) 2023 Darko Gjorgjijoski (https://ideologix.com)
 *
 * This file is part of "Vimeify - Video Uploads for Vimeo"
 *
 * Vimeify - Video Uploads for Vimeo is free software: you can redistribute it
 * and/or modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation, either version 2 of the License,
 * or (at your option) any later version.
 *
 * Vimeify - Video Uploads for Vimeo is distributed in the hope that
 * it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with "Vimeify - Video Uploads for Vimeo". If not, see <https://www.gnu.org/licenses/>.
 *
 * ---
 *
 * Author Note: This code was written by Darko Gjorgjijoski <dg@darkog.com>
 * If you have any questions find the contact details in the root plugin file.
 *
 **********************************************************************/

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