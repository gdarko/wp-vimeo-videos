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

namespace Vimeify\Core\Utilities\Uploads;

use InvalidArgumentException;

class FileChunk {

	/**
	 * Path where the upload chunks and metadata are stored.
	 *
	 * @var string
	 */
	protected $path;

	/**
	 * Metadata about the current upload.
	 *
	 * @var array
	 */
	protected $metadata;

	/**
	 * Chunk offset.
	 *
	 * @var int|null
	 */
	protected $offset;

	/**
	 * Information about each chunk
	 *
	 * @var array
	 */
	protected $chunks = [];

	/**
	 * The input name
	 * @var string
	 */
	protected $input_name;

	/**
	 * Chunk size by default
	 * @var float|int
	 */
	protected $chunk_size = 2 * 1024 * 1024;

	/**
	 * The tmp file dir
	 * @var string|null
	 */
	protected $tmp_dir = null;

	/**
	 * Chunk constructor.
	 *
	 * @param array $metadata Metadata about the chunk.
	 * @param array $params Field.
	 *
	 * @throws InvalidArgumentException Invalid UUID.
	 *
	 */
	public function __construct( array $metadata, $params ) {

		$metadata = array_merge(
			[
				'name'        => '',
				'uuid'        => '',
				'index'       => '',
				'file_size'   => 0,
				'chunk_total' => 0,
				'chunk_size'  => 0,
			],
			$metadata
		);

		if ( ! preg_match( '/^[0-9a-f]{8}\-[0-9a-f]{4}\-[0-9a-f]{4}\-[0-9a-f]{4}\-[0-9a-f]{12}$/i', $metadata['uuid'] ) ) {
			throw new InvalidArgumentException( 'Invalid UUID' );
		}

		if ( isset( $metadata['offset'] ) ) {
			$this->set_offset( $metadata['offset'] );
			unset( $metadata['offset'] );
		}

		if ( isset( $params['tmp_dir'] ) ) {
			$this->tmp_dir = $params['tmp_dir'];
		}

		if ( isset( $params['chunk_size'] ) ) {
			$this->chunk_size = $params['chunk_size'];
		}

		if ( isset( $params['input_name'] ) ) {
			$this->input_name = $params['input_name'];
		}

		$this->path     = trailingslashit( $this->tmp_dir ) . sha1( $metadata['uuid'] ) . '-';
		$this->metadata = $metadata;
	}

	/**
	 * Set the offset of the current block.
	 *
	 * @param int $offset Offset of the current chunk.
	 *
	 * @return FileChunk
	 * @throws InvalidArgumentException Invalid offset.
	 *
	 */
	protected function set_offset( $offset ) {

		if ( ! is_numeric( $offset ) || ! is_int( $offset + 0 ) || $offset < 0 ) {
			throw new InvalidArgumentException( 'Invalid offset' );
		}

		$this->offset = (int) $offset;

		return $this;
	}

	/**
	 * Return the sanitized file name.
	 *
	 * @return string
	 */
	public function get_file_name() {

		return isset( $this->metadata['name'] ) ? $this->metadata['name'] : '';
	}

	/**
	 * Return the original file name.
	 *
	 * @return string
	 */
	public function get_file_user_name() {

		return isset( $this->metadata['file_user_name'] ) ? $this->metadata['file_user_name'] : '';
	}

	/**
	 * Return file_size.
	 *
	 * @return int
	 */
	public function get_file_size() {

		return isset( $this->metadata['file_size'] ) ? (int) $this->metadata['file_size'] : 0;
	}

	/**
	 * Create a Chunk object from the current request.
	 *
	 * If validation failed FALSE is returned instead.
	 *
	 * @param array $params
	 *
	 * @return bool|FileChunk False or the instance of this class.
	 */
	public static function from_current_request( $params ) {

		$field_name = isset( $params['input_name'] ) ? $params['input_name'] : '';

		if ( empty( $field_name ) ) {
			return false;
		}

		if ( isset( $_FILES[ $field_name ]['name'] ) ) {
			// The current upload has a file attached to it. We should check that DropZone
			// included the following required information about this current upload.
			$required = [
				// This is a UUID generated by the client to identify the current upload.
				'dzuuid'            => 'uuid',
				// The number of the current chunk.
				'dzchunkindex'      => 'index',
				// The size of the current chunk.
				'dzchunksize'       => 'chunk_size',
				// The total number of chunks for this current upload.
				'dztotalchunkcount' => 'chunk_total',
				// The offset in bytes of this current chunk.
				'dzchunkbyteoffset' => 'offset',
			];
			$settings = [
				'name'           => sanitize_file_name( wp_unslash( $_FILES[ $field_name ]['name'] ) ),
				'file_user_name' => sanitize_text_field( wp_unslash( $_FILES[ $field_name ]['name'] ) ),
			];
		} else {
			// No file attached, most likely this is a initialization Ajax call, in that scenario
			// we require fewer fields.
			$required = [
				'dzuuid'          => 'uuid',
				'dztotalfilesize' => 'file_size',
				'name'            => 'file_user_name',
			];
			if ( isset( $_POST['name'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
				$settings = [
					'name' => sanitize_file_name( wp_unslash( $_POST['name'] ) ),
					// phpcs:ignore WordPress.Security.NonceVerification.Missing
				];
			}
		}

		foreach ( $required as $field_name => $alias ) {
			if ( ! array_key_exists( $field_name, $_POST ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				return false;
			}

			$settings[ $alias ] = sanitize_text_field( wp_unslash( $_POST[ $field_name ] ) ); // phpcs:ignore WordPress.Security.NonceVerification
		}

		return new self( $settings, $params );
	}

	/**
	 * Return the path of the metadata of the current upload.
	 *
	 * @return string The path of the metadata file.
	 */
	protected function get_metadata_file_path() {

		return $this->path . 'metadata.json';
	}

	/**
	 * Load the metadata which contains the upload details.
	 *
	 * @return bool Whether the metadata was loaded successfully or not.
	 */
	public function load_metadata() {

		if ( ! is_file( $this->get_metadata_file_path() ) ) {
			return false;
		}

		$this->metadata = array_merge(
			$this->metadata,
			json_decode( file_get_contents( $this->get_metadata_file_path() ), true )
		);

		return true;
	}

	/**
	 * Create the metadata file that will be used in the chunk uploads.
	 *
	 * @return bool
	 */
	public function create_metadata() {

		if ( file_exists( $this->get_metadata_file_path() ) ) {
			return false;
		}
		$tmp                          = $this->path . '-' . uniqid();
		$this->metadata['chunk_size'] = $this->chunk_size;

		file_put_contents( $tmp, wp_json_encode( $this->metadata ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_file_put_contents

		return @rename( $tmp, $this->get_metadata_file_path() ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
	}

	/**
	 * Verify the $_FILE entry is valid before returning it.
	 *
	 * @return bool|array The $_FILE array entry or false otherwise.
	 */
	protected function get_file_upload_array() {

		$field_name = $this->input_name;

		return isset( $_FILES[ $field_name ]['tmp_name'] ) && is_readable( $_FILES[ $field_name ]['tmp_name'] ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			? $_FILES[ $field_name ] // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			: false;
	}

	/**
	 * Verify the chunk size and offset.
	 *
	 * This function is very strict for security. The exact amount of bytes are expected, anything above
	 * or bellow that will be rejected. Only the latest chunk is allowed to maybe be smaller.
	 *
	 * @return bool Whether all chunks have the correct offset and size.*
	 */
	protected function verify_chunk_size_and_offset() {

		$file = $this->get_file_upload_array();

		if ( ! $file ) {
			return false;
		}

		$size     = filesize( $file['tmp_name'] );
		$expected = $this->get_chunk_size();

		// The chunk size must be exactly as expected.
		// The last chunk is the only one allowed to maybe be smaller.
		return $size === $expected || ( $this->is_last_chunk() && $size < $expected );
	}

	/**
	 * Whether the current chunk is the last chunk of the file or not.
	 *
	 * The last chunk by their offset position.
	 *
	 * @return bool
	 *
	 */
	protected function is_last_chunk() {

		$chunk_size = $this->get_chunk_size();
		$offset     = $this->offset + 1;
		$file_size  = $this->metadata['file_size'];

		return ceil( $file_size / $chunk_size ) === ceil( $offset / $chunk_size );
	}

	/**
	 * Return the maximum size for a chunk in file uploads.
	 *
	 * @return int The size of the current chunk.
	 */
	public function get_chunk_size() {

		return $this->metadata['chunk_size'];
	}

	/**
	 * Move the uploaded file to the temporary storage.
	 *
	 * No further check are performed, all the validations are performed
	 * once al the chunks has been uploaded.
	 *
	 * @return bool The status of the write operation.
	 *
	 */
	public function write() {

		$file = $this->get_file_upload_array();
		if ( ! $file || ! $this->verify_chunk_size_and_offset() ) {
			return false;
		}

		$path_to   = $this->path . $this->offset . '.chunk';
		$path_from = $file['tmp_name'];

		return @move_uploaded_file( $path_from, $path_to ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
	}

	/**
	 * Return the chunk offset from a chunk filename.
	 *
	 * @param string $chunk_path Chunk path.
	 *
	 * @return int
	 */
	protected function get_chunk_position_from_file( $chunk_path ) {

		if ( preg_match( '/(\d+).chunk$/', $chunk_path, $match ) ) {
			return (int) $match[1];
		}

		return - 1;
	}

	/**
	 * Check if all the chunks have been uploaded.
	 * This must be TRUE in order to finalize the upload.
	 *
	 * @return bool
	 *
	 */
	protected function validate_chunks() {

		$chunks = $this->get_chunks();

		foreach ( $chunks as $id => $chunk ) {
			if ( ! is_file( $chunk['file'] ) ) {
				return false;
			}

			$next = isset( $chunks[ $id + 1 ] ) ? $chunks[ $id + 1 ] : null;

			if ( $next && $chunk['end'] !== $next['start'] ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Return all the chunks with some useful data: file (path), start, size, end.
	 *
	 * @return array
	 *
	 */
	protected function get_chunks() {

		if ( ! $this->chunks ) {
			$chunks = [];

			foreach ( glob( $this->path . '*.chunk' ) as $file ) {
				$start = $this->get_chunk_position_from_file( $file );
				$size  = filesize( $file );

				$chunks[] = [
					'file'  => $file,
					'start' => $start,
					'size'  => $size,
					'end'   => $start + $size,
				];
			}

			usort(
				$chunks,
				static function ( $chunk1, $chunk2 ) {

					return $chunk1['start'] - $chunk2['start'];
				}
			);

			$this->chunks = $chunks;
		}

		return $this->chunks;
	}

	/**
	 * Delete all chunks and metadata files.
	 * Should be called once the upload has been finalized.
	 *
	 */
	protected function delete_temporary_files() {

		foreach ( $this->get_chunks() as $chunk ) {
			wp_delete_file( $chunk['file'] );
		}

		$this->chunks = [];
		wp_delete_file( $this->get_metadata_file_path() );
	}

	/**
	 * Attempt to finalize the uploading.
	 *
	 * This function should be called at most once. This will verify that all the chunks has been uploaded
	 * successfully and will attempt to merge all those chunks in a single file.
	 *
	 * @param string $path Path where the file will be assembled.
	 *
	 * @return bool
	 *
	 */
	public function finalize( $path ) {

		if ( ! $this->validate_chunks() ) {
			return false;
		}

		$dest = @fopen( $path, 'w+b' ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged,WordPress.WP.AlternativeFunctions.file_system_read_fopen

		foreach ( $this->get_chunks() as $chunk ) {
			$source = @fopen( $chunk['file'], 'rb' ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged,WordPress.WP.AlternativeFunctions.file_system_read_fopen

			$bytes = stream_copy_to_stream( $source, $dest );

			if ( $bytes !== $chunk['size'] ) {
				return false;
			}

			@fclose( $source ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged,WordPress.WP.AlternativeFunctions.file_system_read_fclose
		}

		@fclose( $dest ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged,WordPress.WP.AlternativeFunctions.file_system_read_fclose

		$this->delete_temporary_files();

		return true;
	}

}