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

namespace Vimeify\Core\Backend;

use Vimeify\Core\Abstracts\BaseProvider;
use Vimeify\Core\Traits\AfterUpload;
use Vimeify\Core\Utilities\Formatters\VimeoFormatter;
use Vimeo\Exceptions\VimeoRequestException;

class Hooks extends BaseProvider {

	/**
	 * Registers sepcific piece of functionality
	 * @return void
	 */
	public function register() {
		$this->register_activation_hook();
	}

	/**
	 * Register installation / deactivation
	 * @return void
	 */
	public function register_activation_hook() {

		$file = $this->plugin->file();
		if ( empty( $file ) ) {
			return;
		}
		register_activation_hook( $file, function () {
			do_action( 'cv_plugin_activated', $this->plugin->id() );
			do_action( 'vimeify_plugin_activated', $this->plugin );
			$this->plugin->system()->settings()->import_defaults( false );
		} );

	}
}