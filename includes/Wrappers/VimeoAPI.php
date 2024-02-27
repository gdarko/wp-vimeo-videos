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

namespace Vimeify\Core\Wrappers;

class VimeoAPI extends \Vimeo\Vimeo {

	/**
	 * Take an upload attempt and perform the actual upload via tus.
	 *
	 * @param $file_path
	 * @param $file_size
	 * @param $attempt
	 *
	 * @return mixed|string
	 * @throws \Vimeo\Exceptions\VimeoRequestException
	 * @throws \Vimeo\Exceptions\VimeoUploadException
	 */
	public function do_upload_tus( $file_path, $file_size, $attempt ) {
		return $this->perform_upload_tus( $file_path, $file_size, $attempt );
	}

}