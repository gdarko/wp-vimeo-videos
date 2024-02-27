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

namespace Vimeify\Core\Abstracts;

use Vimeify\Core\Abstracts\Interfaces\IntegrationInterface;

abstract class BaseIntegration extends BaseProvider implements IntegrationInterface {

	/**
	 * Is create or edit screen?
	 *
	 * @param $post_type
	 *
	 * @return bool
	 */
	protected function is_editor( $post_type ) {

		$post_type = (array) $post_type;

		if ( ! is_admin() ) {
			return false;
		}

		$is_create = isset( $_GET['post_type'] ) && in_array( $_GET['post_type'], $post_type );
		if ( $is_create ) {
			return true;
		}
		global $post;
		if ( isset( $post->ID ) && in_array( $post->post_type, $post_type ) && isset( $_GET['action'] ) && $_GET['action'] === 'edit' ) {
			return true;
		}

		return false;
	}

	/**
	 * The file path
	 * @param $path
	 *
	 * @return string
	 */
	protected function file( $path = '' ) {
		$fullpath = $this->assemble_path( $this->plugin->path(), $path );
		return str_replace('/', DIRECTORY_SEPARATOR, $fullpath);
	}

	/**
	 * The file url
	 * @param $path
	 *
	 * @return string
	 */
	protected function url( $path = '' ) {
		return $this->assemble_path( $this->plugin->url(), $path );
	}

	/**
	 * Assembles path
	 * @param $root
	 * @param $path
	 *
	 * @return string
	 */
	protected function assemble_path( $root, $path = '' ) {
		$name    = explode( '\\', get_class( $this ) );
		$dirname = $name[ count( $name ) - 1 ];
		return sprintf( '%sincludes/Integrations/%s/%s', $root, $dirname, $path );
	}

    /**
     * Activates the integration
     * @return bool
     */
    public function activate()
    {
        if(!$this->can_activate()) {
            return false;
        }

        $this->register();
        return true;
    }

}