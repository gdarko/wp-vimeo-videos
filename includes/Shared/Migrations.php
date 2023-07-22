<?php
/********************************************************************
 * Copyright (C) 2023 Darko Gjorgjijoski (https://darkog.com/)
 * Copyright (C) 2023 IDEOLOGIX MEDIA Dooel (https://ideologix.com/)
 *
 * This file is property of IDEOLOGIX MEDIA Dooel (https://ideologix.com)
 * This file is part of Vimeify Plugin - https://wordpress.org/plugins/wp-vimeo-videos/
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

namespace Vimeify\Core\Shared;

use Vimeify\Core\Abstracts\BaseProvider;

class Migrations extends BaseProvider {

	/**
	 * Registers sepcific piece of functionality
	 * @return void
	 */
	public function register() {
		add_action( 'admin_init', [ $this, 'init' ] );
	}


	/**
	 * Handles the database upgrade
	 * @return void
	 */
	public function init() {
		$latest_version  = $this->plugin->database_version();
		$current_version = get_option( 'dgv_pro_version', 99 );
		if ( $current_version < $latest_version ) {
			for ( $i = $current_version + 1; $i <= $latest_version; $i ++ ) {
				$method = 'upgrade_v' . $i;
				if ( method_exists( $this, $method ) ) {
					if ( @$this->$method( $current_version, $i, $latest_version ) ) {
						update_option( 'dgv_pro_version', $latest_version );
					}
				}
			}
		}
	}

	/**
	 * Upgrade database to version
	 *
	 * @param $old_version
	 * @param $new_version
	 * @param $latest_version
	 *
	 * @return bool
	 */
	private function upgrade_v100( $old_version, $new_version, $latest_version ) {

		$this->plugin->system()->settings()->set( 'dgv_enable_embed_presets_management', 1 );
		$this->plugin->system()->settings()->set( 'dgv_enable_embed_privacy_management', 1 );
		$this->plugin->system()->settings()->set( 'dgv_enable_folders_management', 1 );
		$this->plugin->system()->settings()->save();

		$this->plugin->system()->logger()->log(
			sprintf( 'Database upgraded from version %s to version %s', $old_version, $new_version ),
			'DGV-MIGRATOR'
		);

		return true;
	}
}