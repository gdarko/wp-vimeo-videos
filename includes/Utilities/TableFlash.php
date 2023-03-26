<?php

namespace Vimeify\Core\Utilities;

/********************************************************************
 * Copyright (C) 2022 Darko Gjorgjijoski (https://codeverve.com)
 *
 * This file is part of Video Uploads for Vimeo PRO
 *
 * Video Uploads for Vimeo PRO is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * Video Uploads for Vimeo PRO is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Video Uploads for Vimeo PRO. If not, see <https://www.gnu.org/licenses/>.
 **********************************************************************/

/**
 * Class TableFlash
 */
class TableFlash {

	/**
	 * Flash message
	 *
	 * @param $message
	 * @param $type
	 *
	 * @return string
	 */
	public function flash_message( $message, $type ) {
		$signature = 'k_' . md5( 'dgv_flash_' . get_current_user_id() );
		set_transient( $signature, array( 'message' => $message, 'type' => $type ), HOUR_IN_SECONDS );

		return $signature;
	}

	/**
	 * Unflash message
	 * @return array
	 */
	public function unflash_message() {
		$signature = $this->get_flash_signature();
		$notice    = get_transient( $signature );
		delete_transient( $signature );

		return $notice;
	}

	/**
	 * Returns the current url
	 *
	 * @param  string  $signature
	 *
	 * @return string
	 */
	public function get_current_url( $signature = '' ) {
		$uri = str_replace( '/wp-admin/', '', $_SERVER['REQUEST_URI'] );
		$url = admin_url( $uri );

		if ( ! empty( $signature ) ) {
			$url = add_query_arg( 'h', $signature, $url );
		}

		return remove_query_arg( array(
			'_wpnonce',
			'_wp_http_referer',
			'action',
			'action2',
			'record_id',
			'filter_action',
		), $url );
	}

	/**
	 * Returns the flash signature
	 * @return string
	 */
	public function get_flash_signature() {
		return 'k_' . md5( 'dgv_flash_' . get_current_user_id() );
	}

}
