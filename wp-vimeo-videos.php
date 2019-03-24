<?php
/**
 * Plugin Name: WP Vimeo Videos
 * Plugin URI: https://darkog.com/plugins/wp-vimeo-videos
 * Description: Easily upload vimeo videos and embed them on your site from WordPress Dashboard.
 * Version: 1.0.3
 * Author: Darko Gjorgjijoski
 * Author URI: https://darkog.com/
 */
if(!defined('ABSPATH')){
	exit;
}
define('DGV_PATH', plugin_dir_path(__FILE__));
define('DGV_ASSETS_URL', plugin_dir_url(__FILE__) . 'assets/');
define('DGV_ASSETS_PATH', DGV_PATH . 'assets/');
define('DGV_PT_VU', 'dgv-upload');
define('DGV_MIN_PHP_VER', '5.5.0');
require_once 'includes/classes/DGV_Plugin.php';