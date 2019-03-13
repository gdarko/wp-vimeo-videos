<?php
/**
 * Plugin Name: DG Vimeo Videos
 * Plugin URI: https://darkog.com/plugins/vimeo-uploads
 * Description: Integrates with WordPress 'Media' screen and allows uploading vimeo videos directly to Vimeo which can be used via shortcode in your website.
 * Version: 1.0.0
 * Author: Darko Gjorgjijoski
 * Author URI: https://darkog.com/
 * Author Email: dg@darkog.com
 * License: GPLv2
 */

if(!defined('ABSPATH')){
	die;
}

define('DGV_PATH', plugin_dir_path(__FILE__));
define('DGV_ASSETS_URL', plugin_dir_url(__FILE__) . 'assets/');
define('DGV_ASSETS_PATH', DGV_PATH . 'assets/');
define('DGV_PT_VU', 'dgv-upload');

require_once 'vendor/autoload.php';
require_once 'includes/helpers.php';
require_once 'includes/classes/DGV_Backend.php';
require_once 'includes/classes/DGV_Videos_Table.php';
require_once 'includes/classes/DGV_Shortcodes.php';
