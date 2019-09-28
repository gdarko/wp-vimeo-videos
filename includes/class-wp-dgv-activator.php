<?php

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    WP_DGV
 * @subpackage WP_DGV/includes
 * @copyright     Darko Gjorgjijoski <info@codeverve.com>
 * @license    GPLv2
 */
class WP_DGV_Activator {

	public static function activate() {
		if ( ! class_exists( 'WP_DGV_Db_Helper' ) ) {
			require_once WP_VIMEO_VIDEOS_PATH . 'includes/class-wp-vimeo-videos-db-helper.php';
		}
		$db_helper = new WP_DGV_Db_Helper();
		$db_helper->set_defaults();
	}
}
