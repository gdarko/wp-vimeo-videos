<?php

namespace Vimeify\Core\Utilities\Formatters;

class ByteFormatter {

	public function format( $bytes, $precision = 2 ) {

		$units = array( 'B', 'KB', 'MB', 'GB', 'TB' );

		$bytes = max( $bytes, 0 );
		$pow   = floor( ( $bytes ? log( $bytes ) : 0 ) / log( 1024 ) );
		$pow   = min( $pow, count( $units ) - 1 );

		// Uncomment one of the following alternatives
		//$bytes /= pow( 1024, $pow );

		$bytes /= ( 1 << ( 10 * $pow ) );

		return round( $bytes, $precision ) . ' ' . $units[ $pow ];
	}

	public function format_max_upload_size() {
		return size_format( wp_max_upload_size() );
	}

}