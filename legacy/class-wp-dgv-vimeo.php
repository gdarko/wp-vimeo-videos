<?php

use Vimeo\Vimeo;

/**
 * Class WP_DGV_VImeo
 * Extends the Vimeo/Vimeo library
 * @deprecated 2.0.0    Replaced with Vimeify\Core\Wrappers\VimeoAPI
 */
class WP_DGV_Vimeo extends Vimeo {

	public function __construct( $client_id, $client_secret, $access_token = null ) {
		parent::__construct( $client_id, $client_secret, $access_token );
	}

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
