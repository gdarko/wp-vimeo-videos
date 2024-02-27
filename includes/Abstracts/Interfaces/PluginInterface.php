<?php
/********************************************************************
 * Copyright (C) 2024 Darko Gjorgjijoski (https://darkog.com/)
 * Copyright (C) 2024 IDEOLOGIX MEDIA Dooel (https://ideologix.com/)
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

namespace Vimeify\Core\Abstracts\Interfaces;

use Vimeify\Core\Abstracts\BaseProvider;

interface PluginInterface {

	/**
	 * The plugin interface constructor
	 *
	 * @param  SystemInterface  $system
	 */
	public function __construct( SystemInterface $system );

	/**
	 * The system
	 * @return SystemInterface
	 */
	public function system();

	/**
	 * Returns the id of the plugin
	 * @return mixed|null
	 */
	public function id();

	/**
	 * Returns the name of the plugin
	 * @return mixed|null
	 */
	public function name();

	/**
	 * Returns the slug of the plugin
	 * @return mixed|null
	 */
	public function slug();

	/**
	 * Returns the icon of the plugin
	 * @return array|null
	 */
	public function icon();

	/**
	 * Returns the main file of the plugin
	 * @return mixed|null
	 */
	public function file();

	/**
	 * Returns the basename of the plugin
	 * @return mixed|null
	 */
	public function basename();

	/**
	 * Returns the path of the plugin
	 * @return mixed|null
	 */
	public function path();

	/**
	 * Returns the url of the plugin
	 * @return mixed
	 */
	public function url();

	/**
	 * Returns the plugin version
	 * @return mixed|null
	 */
	public function plugin_version();

	/**
	 * Returns the database version
	 * @return mixed|null
	 */
	public function database_version();

	/**
	 * Returns the minimum PHP version for this plugin
	 * @return mixed|null
	 */
	public function minimum_php_version();

	/**
	 * Returns the minimum WP version for this plugin
	 * @return mixed|null
	 */
	public function minimum_wp_version();

	/**
	 * Returns the settings key
	 * @return mixed
	 */
	public function settings_key();

	/**
	 * Returns the documentation url
	 * @return mixed
	 */
	public function documentation_url();

	/**
	 * Returns the commercial url
	 * @return mixed
	 */
	public function commercial_url();

	/**
	 * Returns the settings url
	 * @return mixed
	 */
	public function settings_url();

	/**
	 * Add Integration
	 *
	 * @param  ProviderInterface  $provider
	 *
	 * @return void
	 */
	public function add_integration(ProviderInterface $provider);

	/**
	 * List of integrations
	 * @return BaseProvider[]
	 */
	public function get_integrations();
}