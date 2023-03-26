<?php

namespace Vimeify\Core\Utilities\Formatters;

class DateFormatter {

	/**
	 * Format timezone
	 *
	 * @param $datetimeTz
	 *
	 * @return string
	 * @since 1.5.2
	 */
	public function format_tz( $datetimeTz ) {
		if ( empty( $datetimeTz ) ) {
			return $datetimeTz;
		}
		try {
			$dateTime = new \DateTime( $datetimeTz );

			return $dateTime->format( 'Y-m-d H:i:s' );
		} catch ( \Exception $e ) {
		}

		return $datetimeTz;
	}

}