<?php
/********************************************************************
 * Copyright (C) 2020 Darko Gjorgjijoski (https://codeverve.com)
 *
 * This file is part of Video Uploads for Vimeo
 *
 * Video Uploads for Vimeo is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * Video Uploads for Vimeo is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Video Uploads for Vimeo. If not, see <https://www.gnu.org/licenses/>.
 **********************************************************************/

/**
 * Class WP_DGV_Logger
 *
 * Responsible for logging data
 *
 * @license GPLv2
 * @copyright Darko Gjorgjijoski <info@codeverve.com>
 * @since 1.4.0
 */
class WP_DGV_Logger {

	/**
	 * The log dir
	 * @var string|false
	 */
	private $log_dir = null;

	/**
	 * WP_DGV_Logger constructor.
	 */
	public function __construct() {
		$this->setup_log_dir();
		$this->protect_log_dir();
	}

	/**
	 * Wrapper for writing the interactions to /wp-content/uploads/ file
	 *
	 * @param        $message
	 * @param string $tag
	 * @param string $filename
	 *
	 * @return bool
	 */
	public function log( $message, $tag = '', $filename = "debug.log" ) {

		if ( ! file_exists( $this->log_dir ) ) {
			return false;
		}
		$log_file_path = trailingslashit( $this->log_dir ) . $filename;
		if ( file_exists( $log_file_path ) && filesize( $log_file_path ) > 10485760 ) {
			@unlink( $log_file_path );
		}
		$is_object = false;
		if ( ! is_string( $message ) && ! is_numeric( $message ) ) {
			ob_start();
			$this->dump( $message );
			$message   = ob_get_clean();
			$is_object = true;
		}

		if ( ! empty( $tag ) ) {
			if ( $is_object ) {
				$message = $tag . "\n" . $message;
				$message = sprintf( "%s%s%s", $tag, PHP_EOL, $message );
			} else {
				$message = sprintf( '%s: %s', $tag, $message );
			}
		}

		$message = sprintf( '[%s] %s', date( 'Y-m-d H:i:s' ), $message );

		$this->writeln( $log_file_path, $message );

		return true;
	}

	/**
	 * Return the log dir
	 */
	public function get_log_dir() {
		return $this->log_dir;
	}

	/**
	 * Return the log path
	 */
	private function setup_log_dir() {
		$this->log_dir = wvv_get_tmp_dir();
	}

	/**
	 * Return the log dir
	 *
	 * @param bool $noindex
	 */
	private function protect_log_dir( $noindex = true ) {

		$dir = $this->log_dir;

		if ( ! is_dir( $dir ) ) {
			@mkdir( $dir );
		}
		if ( is_dir( $dir ) ) {
			$index_path = $dir . DIRECTORY_SEPARATOR . 'index.html';
			if ( ! file_exists( $index_path ) ) {
				@touch( $index_path );
			}
		}
		if ( $noindex ) {
			$htaccess_path = $dir . DIRECTORY_SEPARATOR . '.htaccess';
			if ( ! file_exists( $htaccess_path ) ) {
				$contents = '# BEGIN WP Vimeo Videos
# The directives (lines) between "BEGIN Vimeo Videos" and "END Vimeo Videos" are
# dynamically generated, and should only be modified via WordPress filters.
# Any changes to the directives between these markers will be overwritten.
# Disable PHP and Python scripts parsing.
<Files *>
  SetHandler none
  SetHandler default-handler
  RemoveHandler .cgi .php .php3 .php4 .php5 .phtml .pl .py .pyc .pyo
  RemoveType .cgi .php .php3 .php4 .php5 .phtml .pl .py .pyc .pyo
</Files>
<IfModule mod_php5.c>
  php_flag engine off
</IfModule>
<IfModule mod_php7.c>
  php_flag engine off
</IfModule>
<IfModule headers_module>
  Header set X-Robots-Tag "noindex"
</IfModule>
# END Vimeo Videos';
				$this->writeln( $htaccess_path, $contents );
			}
		}
	}

	/**
	 * Used to write contents into file provided by parameters
	 *
	 * @param $file string
	 * @param $contents string
	 * @param string $force_flag
	 */
	private function writeln( $file, $contents, $force_flag = '' ) {
		if ( file_exists( $file ) ) {
			$flag = $force_flag !== '' ? $force_flag : 'a';
			$fp   = fopen( $file, $flag );
			fwrite( $fp, $contents . "\n" );
		} else {
			$flag = $force_flag !== '' ? $force_flag : 'w';
			$fp   = fopen( $file, $flag );
			fwrite( $fp, $contents . "\n" );
		}
		fclose( $fp );
	}

	/**
	 * Dump data
	 *
	 * @param $data
	 */
	private function dump( $data ) {
		print_r( $data );
	}

}
