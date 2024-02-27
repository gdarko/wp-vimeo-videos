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

namespace Vimeify\Core\Abstracts;

use Vimeify\Core\Abstracts\Interfaces\PluginInterface;
use Vimeify\Core\Abstracts\Interfaces\ProviderInterface;
use Vimeify\Core\Plugin;

abstract class BaseProvider implements ProviderInterface {

	/**
	 * @var PluginInterface|Plugin
	 */
	protected $plugin;

	/**
	 * The plugin instance
	 *
	 * @param  PluginInterface  $plugin
	 */
	public function __construct( PluginInterface &$plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * Registers sepcific piece of functionality
	 * @return void
	 */
	abstract public function register();

	/**
	 * Boot a plugin
	 *
	 * @param $provider_class
	 *
	 * @return ProviderInterface
	 */
	public function boot( $provider_class ) {
		$instance = new $provider_class( $this->plugin );
		$instance->register();

		return $instance;
	}

	/**
	 * Return the plugin instance
	 * @return PluginInterface|Plugin
	 */
	public function plugin() {
		return $this->plugin;
	}
}