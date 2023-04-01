<?php

namespace Vimeify\Core\Components;

use Vimeify\Core\Abstracts\Interfaces\SettingsInterface;
use Vimeify\Core\Abstracts\Interfaces\SystemComponentInterface;
use Vimeify\Core\Abstracts\Interfaces\SystemInterface;
use Vimeify\Core\Utilities\Arrays\DotNotation;

/********************************************************************
 * Copyright (C) 2023 Darko Gjorgjijoski (https://codeverve.com)
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
 * Class Settings
 *
 * Responsible for read/write plugin settings
 *
 * @note The only difference is the textdomain, otherwise the code is shared in both versions.
 *
 * @license GPLv2
 * @copyright Darko Gjorgjijoski <info@codeverve.com>
 * @package Vimeify\Core
 * @since 1.4.0
 */
class Settings implements SettingsInterface, SystemComponentInterface {


	/**
	 * The dot notation helper
	 * @var DotNotation
	 */
	protected $dot;

	/**
	 * The settings data.
	 * @var array
	 */
	protected $data;

	/**
	 * Updates count
	 * @var
	 */
	protected $updates_count = 0;

	/**
	 * The settings key
	 * @var string
	 */
	protected $settings_key;

	/**
	 * The system instance
	 * @var SystemInterface
	 */
	protected $system;

	/**
	 * Settings constructor.
	 *
	 * @param  SystemInterface  $system
	 * @param  array  $args
	 *
	 * @throws \Exception
	 */
	public function __construct( SystemInterface $system, $args = [] ) {

		$this->system = $system;

		if ( empty( $args ) ) {
			$args = $this->system->config();
		}

		$this->settings_key = isset( $args['settings_key'] ) ? $args['settings_key'] : null;

		if ( is_null( $this->settings_key ) ) {
			throw new \Exception( 'No settings key specified.' );
		}

		$this->data = get_option( $this->settings_key );
		$this->dot  = new DotNotation( $this->data );
	}

	/**
	 * All the settings
	 *
	 * @return array
	 */
	public function all() {
		return $this->data;
	}

	/**
	 * Update setting
	 *
	 * @param $key
	 * @param $value
	 */
	public function set( $key, $value ) {

		$value = $this->prepare_value( $value );

		$this->dot->set( $key, $value );

		$this->updates_count ++;
	}

	/**
	 * Remove setting
	 *
	 * @param $key
	 */
	public function remove( $key ) {
		if ( $this->dot->have( $key ) ) {
			$this->dot->set( $key, null );
			$this->updates_count ++;
		}
	}

	/**
	 * Retrieve single setting.
	 *
	 * @param $key
	 * @param  null  $default
	 *
	 * @return mixed|null
	 */
	public function get( $key, $default = null ) {
		$value = $this->dot->get( $key, $default );

		return apply_filters( 'dgv_settings_get', $value, $key, $default );
	}

	/**
	 * Save settings
	 */
	public function save() {
		update_option( $this->settings_key, $this->data );
		$this->updates_count = 0;
	}

	/**
	 * Prepare Values
	 *
	 * @param $value
	 *
	 * @return array|string
	 */
	public function prepare_value( $value ) {
		if ( ! is_array( $value ) ) {
			$option_value = sanitize_text_field( $value );
		} else {
			$option_value = $value;
		}

		return $option_value;
	}

	/**
	 * Updates count
	 * @return int
	 */
	public function updates_count() {
		return $this->updates_count;
	}

	/**
	 * Returns the default admin embed privacy
	 *
	 * @param $profile  (gutenberg, classic)
	 *
	 * @return  string
	 */
	public function get_default_view_privacy( $profile = 'default' ) {

		$profile_id = $this->system->settings()->get( 'upload_profiles.' . $profile );
		$privacy    = $this->system->database()->get_upload_profile_option( $profile_id, 'view_privacy' );

		if ( ! in_array( $privacy, array( 'anybody', 'contact', 'disable', 'nobody', 'unlisted' ) ) ) {
			$privacy = 'anybody';
		}

		return apply_filters( 'dgv_default_privacy', $privacy, $profile );
	}

	/**
	 * Return upload profile by context
	 *
	 * @param $context
	 *
	 * @return int|null
	 */
	public function get_upload_profile_by_context( $context ) {

		switch ( $context ) {
			case 'Backend.Editor.Classic':
				$profile = $this->get( 'upload_profiles.admin_tinymce', null );
				break;
			case 'Backend.Editor.Gutenberg':
				$profile = $this->get( 'upload_profiles.admin_gutenberg', null );
				break;
			case 'Backend.Form.Attachment':
			case 'Backend.Form.Upload':
				$profile = $this->get( 'upload_profiles.admin_other', null );
				break;
			default:
				$profile = $this->get( 'upload_profiles.default' );
				break;
		}

		if ( is_numeric( $profile ) ) {
			$profile = (int) $profile;
		}

		return apply_filters( 'dgv_upload_profile_by_context', $profile, $context, $this );

	}

	/**
	 * Set specific file to the temporary files list
	 *
	 * This means that the file is marked to be deleted from file system once the cron is executed.
	 *
	 * @param $path
	 * @param $time
	 *
	 * @return bool
	 */
	public function mark_as_temporary_file( $path, $time = null ) {
		if ( ! file_exists( $path ) ) {
			return false;
		}

		$tmp_files          = $this->get_temporary_files();
		$tmp_files[ $path ] = time();

		$this->set_temporary_files( $tmp_files );

		return true;
	}

	/**
	 * Unset a file from the temporary files list
	 *
	 * @param $path
	 *
	 * @return void
	 */
	public function remove_from_temporary_files( $path ) {
		$tmp_files = $this->get_temporary_files();
		if ( isset( $tmp_files[ $path ] ) ) {
			unset( $tmp_files[ $path ] );
		}
		$this->set_temporary_files( $tmp_files );
	}

	/**
	 * Returns the temporary pull files
	 * @return array
	 */
	public function get_temporary_files() {
		$tmp_files = get_option( 'dgv_tmp_files' );
		if ( ! $tmp_files || ! is_array( $tmp_files ) ) {
			$tmp_files = [];
		}

		return $tmp_files;
	}

	/**
	 * Designate specific list as temporary files
	 *
	 * @param $list
	 *
	 * @return void
	 */
	public function set_temporary_files( $list ) {
		update_option( 'dgv_tmp_files', $list );
	}

	/**
	 * Returns the api credentials
	 *
	 * @param  bool  $force
	 *
	 * @return void
	 */
	public function import_defaults( $force = false ) {

		$defaults = $this->get_defaults();
		$values   = array();

		// Find out if the defaults are not yet initialized
		if ( ! $force ) {
			foreach ( $defaults as $key ) {
				$value = $this->get( $key );
				if ( ! empty( $value ) ) {
					array_push( $values, $value );
				}
			}
		}


		// If the defaults are not yet initialized, import as defaults.
		if ( empty( $values ) || $force ) {
			foreach ( $defaults as $key => $value ) {
				if ( empty( $value ) ) {
					continue;
				}
				$this->set( $key, $value );
			}
		}

		$this->save();
	}

	/**
	 * Return the plugin defaults
	 * @return string[]
	 */
	public function get_defaults() {
		return array(
			'admin.tinymce.enable_account_search'         => '1',
			'admin.tinymce.enable_local_search'           => '1',
			'admin.tinymce.show_author_uploads_only'      => '0',
			'admin.gutenberg.enable_account_search'       => '1',
			'admin.gutenberg.show_author_uploads_only'    => '0',
			'frontend.behavior.enable_single_pages'       => '1',
			'admin.video_management.enable_embed_presets' => '1',
			'admin.video_management.enable_embed_privacy' => '1',
			'admin.video_management.enable_folders'       => '1',
		);
	}

}
