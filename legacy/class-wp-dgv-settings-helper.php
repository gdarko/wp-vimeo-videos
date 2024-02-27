<?php
/********************************************************************
 * Copyright (C) 2024 Darko Gjorgjijoski (https://codeverve.com)
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
 * Class WP_DGV_Settings_Helper
 *
 * Responsible for read/write plugin settings
 *
 * @note The only difference is the textdomain, otherwise the code is shared in both versions.
 *
 * @license GPLv2
 * @copyright Darko Gjorgjijoski <info@codeverve.com>
 * @since 1.4.0
 * @deprecated 2.0.0    Replaced with Vimeify\Core\Plugin (e.g, vimeify()->system()->settings() )
 */
class WP_DGV_Settings_Helper {

	/**
	 * The settings data.
	 * @var array
	 */
	private $data;

	/**
	 * If data was found. This may tell us if this is a first time install.
	 * @var bool
	 */
	private $found;

	/**
	 * Updates count
	 * @var
	 */
	private $updates_count = 0;

	/**
	 * WP_DGV_Settings constructor.
	 */
	public function __construct() {

		$this->data = get_option( 'dgv_settings' );

		if ( ! is_array( $this->data ) ) {
			$this->data  = array();
			$this->found = false;
		} else {
			$this->found = true;
		}

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
		$key   = $this->prepare_key( $key );
		$value = $this->prepare_value( $value );

		$this->data[ $key ] = $value;
		$this->updates_count ++;
	}

	/**
	 * Remove setting
	 *
	 * @param $key
	 */
	public function remove( $key ) {
		$key = $this->prepare_key( $key );
		if ( isset( $this->data[ $key ] ) ) {
			unset( $this->data[ $key ] );
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
		$key = $this->prepare_key( $key );

		$value = isset( $this->data[ $key ] ) ? $this->data[ $key ] : $default;

		return apply_filters( 'dgv_settings_get', $value, $key, $default );
	}

	/**
	 * Save settings
	 */
	public function save() {
		update_option( 'dgv_settings', $this->data );
		$this->updates_count = 0;
	}

	/**
	 * Sanitize key
	 *
	 * @param $key
	 *
	 * @return string|string[]
	 */
	private function prepare_key( $key ) {
		$key = str_replace( 'dgv_', '', $key );

		return $key;
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
	 * Check if the settings require migration
	 */
	public function requires_migration() {
		return ! $this->found;
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
	 * @return  string
	 */
	public function get_default_admin_view_privacy() {
		$privacy = $this->get( 'dgv_view_privacy' );
		if ( ! in_array( $privacy, array( 'anybody', 'contact', 'disable', 'nobody', 'unlisted' ) ) ) {
			$privacy = 'anybody';
		}

		return apply_filters( 'dgv_default_privacy', $privacy );
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
	 * Returns the default front-end view privacy
	 * @return string
	 */
	public function get_default_frontend_view_privacy() {
		$privacy = $this->get( 'dgv_view_privacy_fe' );
		if ( ! in_array( $privacy, array( 'anybody', 'contact', 'disable', 'nobody', 'unlisted' ) ) ) {
			$privacy = 'anybody';
		}
		if ( $privacy === 'unlisted' ) { // Only available in paid versions.
			$api = new WP_DGV_Api_Helper();
			if ( $api->is_free() ) {
				$privacy = 'anybody';
				$this->set( 'dgv_view_privacy_fe', $privacy );
			}
		}

		return apply_filters( 'dgv_default_privacy_frontend', $privacy );
	}

	/**
	 * Returns list of whitelisted domains
	 * @return array
	 */
	public function get_whitelisted_domains() {
		$domains   = array();
		$whitelist = $this->get( 'dgv_embed_domains' );
		if ( ! empty( $whitelist ) ) {
			$parts = explode( ',', $whitelist );
			foreach ( $parts as $domain ) {
				if ( empty( $domain ) || false === filter_var( $domain, FILTER_VALIDATE_DOMAIN ) ) {
					continue;
				}
				array_push( $domains, $domain );
			}
		}

		return $domains;
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
			'dgv_view_privacy'                    => 'anybody',
			'dgv_view_privacy_fe'                 => 'anybody',
			'dgv_upload_folder'                   => 'default',
			'dgv_upload_folder_fe'                => 'default',
			'dgv_mce_enable_account_search'       => '1',
			'dgv_mce_enable_local_search'         => '1',
			'dgv_gtb_enable_account_search'       => '1',
			'dgv_save_frontend_uploads'           => '1',
			'dgv_enable_single_pages'             => '1',
			'dgv_local_current_user_only'         => '1',
			'dgv_enable_embed_presets_management' => '1',
			'dgv_enable_embed_privacy_management' => '1',
			'dgv_enable_folders_management'       => '1',

		);
	}

}
