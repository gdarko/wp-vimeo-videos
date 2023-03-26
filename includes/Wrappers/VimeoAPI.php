<?php

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