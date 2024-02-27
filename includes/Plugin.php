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

namespace Vimeify\Core;

use Vimeify\Core\Abstracts\BaseProvider;
use Vimeify\Core\Abstracts\Interfaces\PluginInterface;
use Vimeify\Core\Abstracts\Interfaces\ProviderInterface;

class Plugin implements PluginInterface {

	/**
	 * The system
	 * @var System
	 */
	protected $system;

	/**
	 * The integrations list
	 * @var BaseProvider[]
	 */
	protected $integrations;

	/**
	 * Constructor.
	 *
	 * @param  System  $system
	 */
	public function __construct( $system ) {
		$this->system = $system;
	}

	/**
	 * The system
	 * @return System
	 */
	public function system() {
		return $this->system;
	}

	/**
	 * Returns the id of the plugin
	 * @return mixed|null
	 */
	public function id() {
		return $this->system->config( 'id' );
	}

	/**
	 * Returns the slug of the plugin
	 * @return mixed|null
	 */
	public function slug() {
		return $this->system->config( 'slug' );
	}

	/**
	 * Returns the name of the plugin
	 * @return mixed|null
	 */
	public function name() {
		return $this->system->config( 'name' );
	}

	/**
	 * Returns the icon of the plugin
	 * @return array|null
	 */
	public function icon() {
		return $this->system->config( 'icon' );
	}

	/**
	 * Returns the main file of the plugin
	 * @return mixed|null
	 */
	public function file() {
		return $this->system->config( 'file' );
	}

	/**
	 * Returns the basename of the plugin
	 * @return mixed|null
	 */
	public function basename() {
		return $this->system->config( 'basename' );
	}

	/**
	 * Returns the path of the plugin
	 * @return mixed|null
	 */
	public function path() {
		return $this->system->config( 'path' );
	}

	/**
	 * Returns the url of the plugin
	 * @return mixed|null
	 */
	public function url() {
		return $this->system->config( 'url' );
	}

	/**
	 * Returns the plugin version
	 * @return mixed|null
	 */
	public function plugin_version() {
		return $this->system->config( 'plugin_version' );
	}

	/**
	 * Returns the database version
	 * @return mixed|null
	 */
	public function database_version() {
		return $this->system->config( 'database_version' );
	}

	/**
	 * Returns the minimum PHP version for this plugin
	 * @return mixed|null
	 */
	public function minimum_php_version() {
		return $this->system->config( 'min_php_version' );
	}

	/**
	 * Returns the minimum WP version for this plugin
	 * @return mixed|null
	 */
	public function minimum_wp_version() {
		return $this->system->config( 'min_wp_version' );
	}

	/**
	 * Returns the settings_key
	 * @return mixed|null
	 */
	public function settings_key() {
		return $this->system->config( 'settings_key' );
	}

	/**
	 * Returns the database version of this plugin
	 * @return mixed|null
	 */
	public function db_version() {
		return $this->system->config( 'db_version' );
	}

	/**
	 * Returns the documentation url
	 * @return mixed
	 */
	public function documentation_url() {
		return $this->system->config( 'documentation_url' );
	}

	/**
	 * Returns the commercial url
	 * @return mixed
	 */
	public function commercial_url() {
		return $this->system->config( 'commercial_url' );
	}

	/**
	 * Returns the settings url
	 * @return mixed
	 */
	public function settings_url() {
		return $this->system->config( 'settings_url' );
	}

	/**
	 * Checks for dependencies
	 * @return void
	 * @throws \Exception
	 */
	public function dependency_check( $deps = [] ) {

		$min_php_ver = $this->minimum_php_version();
		$min_wp_ver  = $this->minimum_wp_version();

		if ( $min_php_ver ) {
			if ( - 1 === version_compare( PHP_VERSION, $min_php_ver ) ) {
				throw new \Exception( sprintf( 'The PHP version %s that you are using does not satisfy the minimum required version %s', '<strong>' . PHP_VERSION . '</strong>', '<strong>' . $min_php_ver . '</strong>' ) );
			}
		}

		global $wp_version;
		if ( $min_wp_ver && $wp_version ) {
			if ( - 1 === version_compare( $wp_version, $min_wp_ver ) ) {
				throw new \Exception( sprintf( 'The WordPress version %s that you are using does not satisfy the minimum required version %s', '<strong>' . $wp_version . '</strong>', '<strong>' . $min_wp_ver . '</strong>' ) );
			}
		}

		if ( in_array( 'curl', $deps ) ) {
			if ( ! function_exists( 'curl_version' ) ) {
				throw new \Exception( 'The cURL PHP extension is required in order to run this plugin. Please contact your webhost to enable the cURL PHP extension.' );
			}
		}

	}

	/**
	 * Add Integration
	 * @return void
	 */
	public function add_integration( ProviderInterface $provider ) {
		$this->integrations[] = $provider;
	}

	/**
	 * THe integrations
	 * @return BaseProvider[]
	 */
	public function get_integrations() {
		return $this->integrations;
	}
}