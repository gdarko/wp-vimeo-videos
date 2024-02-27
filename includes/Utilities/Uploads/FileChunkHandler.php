<?php
/********************************************************************
 * Copyright (C) 2023 Darko Gjorgjijoski (https://darkog.com/)
 * Copyright (C) 2023 IDEOLOGIX MEDIA Dooel (https://ideologix.com/)
 *
 * This file is property of IDEOLOGIX MEDIA Dooel (https://ideologix.com)
 * This file is part of Vimeify Plugin - https://wordpress.org/plugins/wp-vimeo-videos/
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

namespace Vimeify\Core\Utilities\Uploads;

use Vimeify\Core\Utilities\Validators\FileValidator;
use Vimeify\Core\Utilities\Validators\RequestValidator;

abstract class FileChunkHandler {

	/**
	 * Unique string for this specific handler.
	 * @var string
	 */
	protected $prefix;

	/**
	 * The tmp dir that the tmp files are uploaded.
	 * @var string
	 */
	protected $tmp_dir;

	/**
	 * The $_FILES array input name of the files.
	 * @var mixed
	 */
	protected $input_name;

	/**
	 * Max file size allowed per upload.
	 * @var string
	 */
	protected $max_file_size;

	/**
	 * Possible file upload errors
	 * @var array
	 */
	protected $file_upload_errors;

	/**
	 * The plugin version
	 * @var mixed|string
	 */
	protected $version;

	/**
	 * FileChunkHandler constructor.
	 *
	 * @param $prefix
	 * @param  array  $params
	 */
	public function __construct( $prefix, $params = array() ) {

		$this->prefix = $prefix;

		$this->version = isset($params['version']) ? $params['version'] : '1.0.0';

		$this->file_upload_errors = array(
			false,
			esc_html__( 'The uploaded file exceeds the upload_max_filesize directive in php.ini.', 'vimeify' ),
			esc_html__( 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.', 'vimeify' ),
			esc_html__( 'The uploaded file was only partially uploaded.', 'vimeify' ),
			esc_html__( 'No file was uploaded.', 'vimeify' ),
			'',
			esc_html__( 'Missing a temporary folder.', 'vimeify' ),
			esc_html__( 'Failed to write file to disk.', 'vimeify' ),
			esc_html__( 'File upload stopped by extension.', 'vimeify' ),
		);

		// Set tmp dir.
		if ( isset( $params['tmp_dir'] ) ) {
			$this->tmp_dir = $params['tmp_dir'];
			if ( ! file_exists( $this->tmp_dir ) ) {
				_doing_it_wrong( __METHOD__, 'Tmp dir path does not exist.', $this->version );
			}
		} else {
			_doing_it_wrong( __METHOD__, 'No tmp dir path specified.', $this->version );
		}

		// Set input name
		if ( isset( $params['input_name'] ) ) {
			$this->input_name = $params['input_name'];
		} else {
			_doing_it_wrong( __METHOD__, 'No input name specified.', $this->version );
		}

		// Max filesize
		if ( isset( $params['max_file_size'] ) ) {
			$this->max_file_size = $params['max_file_size'];
		} else {
			_doing_it_wrong( __METHOD__, 'No max file size specified.', $this->version );
		}
	}

	/**
	 * Returns the handler prefix.
	 *
	 * @return mixed
	 */
	public function get_prefix() {
		return $this->prefix;
	}

	/**
	 * Register the chunk upload endpoints
	 */
	public function register() {

		add_action( 'wp_ajax_' . $this->prefix . '_file_upload_speed_test', 'wp_send_json_success' );
		add_action( 'wp_ajax_nopriv_' . $this->prefix . '_file_upload_speed_test', 'wp_send_json_success' );

		add_action( 'wp_ajax_' . $this->prefix . '_upload_chunk_init', array( $this, 'ajax_upload_init' ) );
		add_action( 'wp_ajax_nopriv_' . $this->prefix . '_upload_chunk_init', array(
			$this,
			'ajax_upload_init'
		) );
		add_action( 'wp_ajax_' . $this->prefix . '_upload_chunk', array( $this, 'ajax_upload_chunk' ) );
		add_action( 'wp_ajax_nopriv_' . $this->prefix . '_upload_chunk', array( $this, 'ajax_upload_chunk' ) );
		add_action( 'wp_ajax_' . $this->prefix . '_file_chunks_uploaded', array( $this, 'ajax_upload_finalize' ) );
		add_action( 'wp_ajax_nopriv_' . $this->prefix . '_file_chunks_uploaded', array(
			$this,
			'ajax_upload_finalize'
		) );

		add_action( 'wp_ajax_' . $this->prefix . '_remove_file', array( $this, 'ajax_remove_file' ) );
		add_action( 'wp_ajax_nopriv_' . $this->prefix . '_remove_file', array( $this, 'ajax_remove_file' ) );


		// phpcs:ignore WordPress.Security.NonceVerification
		if ( ! empty( $_POST[ $this->prefix . '_slow' ] ) && 'true' === $_POST[ $this->prefix . '_slow' ] && ! empty( $this->validate() ) ) {
			add_filter( $this->prefix . '_file_upload_chunk_parallel', '__return_false' );
			add_filter( $this->prefix . '_file_upload_chunk_size', array( $this, 'get_slow_connection_chunk_size' ) );
		}
	}

	/**
	 * Clean up the tmp folder - remove all old files every day (filterable interval).
	 */
	protected function clean_tmp_files() {

		$files = glob( trailingslashit( $this->get_tmp_dir() ) . '*' );

		if ( ! is_array( $files ) || empty( $files ) ) {
			return;
		}

		$lifespan = (int) apply_filters( $this->prefix . '_field_clean_tmp_files_lifespan', DAY_IN_SECONDS );

		foreach ( $files as $file ) {
			if ( 'index.html' === $file || ! is_file( $file ) ) {
				continue;
			}

			// In some cases filemtime() can return false, in that case - pretend this is a new file and do nothing.
			$modified = (int) filemtime( $file );
			if ( empty( $modified ) ) {
				$modified = time();
			}

			if ( ( time() - $modified ) >= $lifespan ) {
				@unlink( $file ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
			}
		}
	}

	/**
	 * Remove the file from the temporary directory.
	 */
	public function ajax_remove_file() {

		$default_error = esc_html__( 'Something went wrong while removing the file.', 'vimeify' );

		$validated_form_field = $this->validate();
		if ( empty( $validated_form_field ) ) {
			wp_send_json_error( $default_error, 400 );
		}

		if ( empty( $_POST['file'] ) ) {
			wp_send_json_error( $default_error, 403 );
		}

		$file     = sanitize_file_name( wp_unslash( $_POST['file'] ) );
		$tmp_path = wp_normalize_path( $this->get_tmp_dir() . '/' . $file );

		// Requested file does not exist, which is good.
		if ( ! is_file( $tmp_path ) ) {
			wp_send_json_success( $file );
		}

		if ( @unlink( $tmp_path ) ) {
			wp_send_json_success( $file );
		}

		wp_send_json_error( $default_error, 400 );
	}

	/**
	 * Initializes the chunk upload process.
	 *
	 * No data is being send by the client, they expecting an authorization from this method
	 * before sending any chunk.
	 *
	 * The server may return different configs to the uploader client (smaller chunks, disable
	 * parallel uploads etc).
	 *
	 * This method would validate the file extension, maximum size and other things.
	 */
	public function ajax_upload_init() {

		$default_error = esc_html__( 'Something went wrong, please try again.', 'vimeify' );

		$validated_form_field = $this->validate();
		if ( empty( $validated_form_field ) ) {
			wp_send_json_error( $default_error );
		}

		$handler = FileChunk::from_current_request( array(
			'input_name' => $this->input_name,
			'tmp_dir'    => $this->tmp_dir,
			'chunk_size' => $this->get_chunk_size(),
		) );

		if ( ! $handler || ! $handler->create_metadata() ) {
			wp_send_json_error( $default_error, 403 );
		}

		$error     = 0;
		$name      = sanitize_file_name( wp_unslash( $handler->get_file_name() ) );
		$extension = strtolower( pathinfo( $name, PATHINFO_EXTENSION ) );

		$request_validator = new RequestValidator();
		$file_validator = new FileValidator();

		$errors = array();
		$errors = array_merge( $errors, (array) $this->validate_file_upload_error( $error ) );
		$errors = array_merge( $errors, (array) $request_validator->validate_upload_max_filesize( $this->get_max_file_size(), [ $handler->get_file_size() ] ) );
		$errors = array_merge( $errors, (array) $file_validator->validate_extension( $extension ) );
		$errors = array_filter( $errors );
		$errors = array_unique( $errors );
		$errors = array_values( $errors );

		if ( count( $errors ) > 0 ) {
			wp_send_json_error( implode( ',', $errors ) );
		}

		wp_send_json(
			[
				'success' => true,
				'data'    => [
					'dzchunksize'          => $handler->get_chunk_size(),
					'parallelChunkUploads' => apply_filters( $this->prefix . '_file_upload_chunk_parallel', false ),
				],
			]
		);
	}

	/**
	 * Upload the files using chunks.
	 */
	public function ajax_upload_chunk() {

		$default_error = esc_html__( 'Something went wrong, please try again.', 'vimeify' );

		$validated_form_field = $this->validate();
		if ( empty( $validated_form_field ) ) {
			wp_send_json_error( $default_error );
		}

		$handler = FileChunk::from_current_request( array(
			'input_name' => $this->input_name,
			'tmp_dir'    => $this->tmp_dir,
			'chunk_size' => $this->get_chunk_size(),
		) );

		if ( ! $handler || ! $handler->load_metadata() ) {
			wp_send_json_error( $default_error, 403 );
		}

		if ( ! $handler->write() ) {
			wp_send_json_error( $default_error, 403 );
		}

		wp_send_json( [ 'success' => true ] );
	}

	/**
	 * Ajax handler for finalizing a chunked upload.
	 */
	public function ajax_upload_finalize() {
		$default_error = esc_html__( 'Something went wrong, please try again.', 'vimeify' );

		$handler = FileChunk::from_current_request( array(
			'input_name' => $this->input_name,
			'tmp_dir'    => $this->tmp_dir,
			'chunk_size' => $this->get_chunk_size(),
		) );
		if ( ! $handler || ! $handler->load_metadata() ) {
			wp_send_json_error( $default_error, 403 );
		}

		$file_name      = $handler->get_file_name();
		$file_user_name = $handler->get_file_user_name();
		$extension      = strtolower( pathinfo( $file_name, PATHINFO_EXTENSION ) );
		$tmp_dir        = $this->get_tmp_dir();
		$tmp_name       = $this->get_tmp_file_name( $extension );
		$tmp_path       = wp_normalize_path( $tmp_dir . '/' . $tmp_name );

		if ( ! $handler->finalize( $tmp_path ) ) {
			wp_send_json_error( $default_error, 403 );
		}

		$file_validator = new FileValidator();
		$is_valid_type = $file_validator->validate_wp_filetype_and_ext( $tmp_path, $file_name );
		if ( false !== $is_valid_type ) {
			wp_send_json_error( $is_valid_type, 403 );
		}

		$this->clean_tmp_files();

		wp_send_json_success(
			array(
				'name'           => $file_name,
				'file'           => pathinfo( $tmp_path, PATHINFO_FILENAME ) . '.' . pathinfo( $tmp_path, PATHINFO_EXTENSION ),
				'file_user_name' => $file_user_name,
				'size'           => filesize( $tmp_path ),
			)
		);
	}

	/**
	 * Returns the tmp dir
	 * @return string
	 */
	protected function get_tmp_dir() {
		return $this->tmp_dir;
	}

	/**
	 * Basic file upload validation.
	 *
	 * @param  int  $error  Error ID provided by PHP.
	 *
	 * @return false|string False if no errors found, error text otherwise.
	 *
	 * @since 1.6.0
	 *
	 */
	protected function validate_file_upload_error( $error ) {

		if ( 0 === $error || 4 === $error ) {
			return false;
		}

		if ( array_key_exists( $error, $this->file_upload_errors ) ) {
			return sprintf( esc_html__( 'File upload error. %s', 'vimeify' ), $this->file_upload_errors[ $error ] );
		}

		return false;
	}

	/**
	 * Maximum chunk for slow connections.
	 *
	 * @return int
	 */
	public function get_slow_connection_chunk_size() {

		$max_allowed_size = $this->get_max_allowed_chunk_size( true );
		$max_file_size    = $this->get_max_file_size();

		if ( is_numeric( $max_file_size ) && $max_file_size > 0 ) {
			$value = min( $max_allowed_size, $max_file_size );
		} else {
			$value = $max_allowed_size;
		}

		return $value;
	}

	/**
	 * Maximum size for a chunk in file uploads.
	 *
	 * @return int
	 */
	public function get_chunk_size() {

		$max_allowed_size = $this->get_max_allowed_chunk_size();
		$max_file_size    = $this->get_max_file_size();

		if ( is_numeric( $max_file_size ) && $max_file_size > 0 ) {
			$value = min( $max_allowed_size, $max_file_size );
		} else {
			$value = $max_allowed_size;
		}

		return $value;
	}

	/**
	 * Maximum allowed file size by server constraints
	 *
	 * @param  bool  $slow
	 *
	 * @return mixed
	 */
	public function get_max_allowed_chunk_size( $slow = false ) {

		if ( ! $slow ) {
			$default_size = apply_filters( $this->prefix . '_file_upload_chunk_size', 2 * 1024 * 1024 );
		} else {
			$default_size = 512 * 1024;
		}
		$max_allowed_size = wp_max_upload_size();

		return min( $default_size, $max_allowed_size );
	}

	/**
	 * Returns the max file size
	 */
	public function get_max_file_size() {
		return $this->max_file_size;
	}

	/**
	 * Validate field meta and check if it is valid.
	 *
	 * @return bool
	 */
	abstract protected function validate();

	/**
	 * Returns the tmp filename
	 *
	 * @param $extension
	 *
	 * @return string
	 */
	abstract protected function get_tmp_file_name( $extension );

}