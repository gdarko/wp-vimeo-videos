<?php
/**
 * Plugin Name:       Vimeify - Video Uploads for Vimeo
 * Plugin URI:        https://vimeify.com
 * Description:       Embed and upload videos to Vimeo directly from WordPress
 * Version:           2.0.0
 * Author:            Darko Gjorgjijoski
 * Author URI:        https://vimeify.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       vimeify
 * Domain Path:       /languages
 *
 ********************************************************************
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
 **********************************************************************/


// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Load the composer dependencies, bail if not set up.
if ( ! file_exists( plugin_dir_path( __FILE__ ) . 'vendor/autoload.php' ) ) {
	wp_die( 'You are using a Development version of Vimeify plugin, please run composer install.' );
}
require_once 'vendor/autoload.php';

if ( ! function_exists( 'vimeify' ) ) {

	/**
	 * The Vimeify plugin wrapper
	 * @return \Vimeify\Core\Boot|null
	 * @throws Exception
	 */
	function vimeify() {

		static $boot = null;

		if ( is_null( $boot ) ) {

			$plugin_url   = plugin_dir_url( __FILE__ );
			$plugin_path  = plugin_dir_path( __FILE__ );
			$plugin_bname = plugin_basename( __FILE__ );

			$system = new \Vimeify\Core\System( [
				'id'                => 561,
				'name'              => 'Vimeify',
				'slug'              => 'vimeify',
				'file'              => __FILE__,
				'path'              => $plugin_path,
				'url'               => $plugin_url,
				'basename'          => $plugin_bname,
				'icon'              => $plugin_url . '/assets/shared/img/icon-64.png',
				'tmp_dir_name'      => 'vimeify',
				'min_php_version'   => '7.2.5',
				'min_wp_version'    => '4.7',
				'plugin_version'    => '2.0.0',
				'database_version'  => '100',
				'settings_key'      => 'dgv_settings_v2',
				'views_path'        => $plugin_path . 'views',
				'commercial_url'    => 'https://vimeify.com/',
				'documentation_url' => 'https://vimeify.com/documentation',
				'settings_url'      => admin_url( 'admin.php?page=dgv-settings' ),
				'components'        => [
					'database' => \Vimeify\Core\Components\Database::class,
					'settings' => \Vimeify\Core\Components\Settings::class,
					'requests' => \Vimeify\Core\Components\Requests::class,
					'logger'   => \Vimeify\Core\Components\Logger::class,
					'vimeo'    => \Vimeify\Core\Components\Vimeo::class,
					'views'    => \Vimeify\Core\Components\Views::class
				]
			] );

			$plugin = new \Vimeify\Core\Plugin( $system );
			$plugin->dependency_check( [ 'curl' ] );

			$boot = new \Vimeify\Core\Boot( $plugin );
			$boot->register();
		}

		return $boot;
	}
}

try {

	vimeify();

} catch ( \Exception $e ) {

	add_action( 'admin_notices', function () use ( $e ) {
		$class   = 'notice notice-error is-dismissible';
		$plugin  = 'Vimeify';
		$message = $e->getMessage();
		printf( '<div class="%1$s"><p><strong>%2$s</strong>: %3$s</p></div>', esc_attr( $class ), esc_html( $plugin ), esc_html( $message ) );
	} );

}
