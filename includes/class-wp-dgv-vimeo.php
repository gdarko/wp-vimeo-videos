<?php

use Vimeo\Vimeo;

/**
 * Class WP_DGV_VImeo
 * Extends the Vimeo/Vimeo library
 */
class WP_DGV_Vimeo extends Vimeo {

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
