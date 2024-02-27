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

namespace Vimeify\Core\Shared;

use Vimeify\Core\Abstracts\BaseProvider;
use Vimeify\Core\Traits\AfterUpload;
use Vimeify\Core\Utilities\Formatters\VimeoFormatter;
use Vimeify\Core\Utilities\Validators\FileValidator;
use Vimeo\Exceptions\VimeoRequestException;

class Hooks extends BaseProvider {

	use AfterUpload;

	/**
	 * Registers sepcific piece of functionality
	 * @return void
	 */
	public function register() {

		add_filter( 'upload_mimes', [ $this, 'allowed_mime_types' ], 15 );
		add_action( 'wp_vimeo_upload_process_default_time_limit', [
			$this,
			'upload_process_default_time_limit'
		], 10, 1 );
		add_action( 'init', [ $this, 'load_text_domain' ] );

		add_action( 'dgv_upload_complete', [ $this, 'upload_complete' ], 5 );

		$this->register_integrations();
	}

	/**
	 * Register integrations
	 * @return void
	 */
	public function register_integrations() {

		$integrations = $this->plugin->get_integrations();

		if ( empty( $integrations ) ) {
			return;
		}

		foreach ( $integrations as $integration ) {
			$integration->register();
		}
	}

	/**
	 * Enable custom extensions support
	 *
	 * @param $mimes
	 *
	 * @return mixed
	 */
	public function allowed_mime_types( $mimes ) {

		$file_validator = new FileValidator();

		foreach ( $file_validator->allowed_mimes() as $key => $mime ) {
			if ( ! isset( $mimes[ $key ] ) ) {
				$mimes[ $key ] = $mime;
			}
		}

		return $mimes;
	}

	/**
	 * Manually determine the default time limit of specific process
	 *
	 * @param $default
	 *
	 * @return int
	 * @since 1.4.0
	 */
	public function upload_process_default_time_limit( $default ) {
		$limit = (int) ini_get( 'max_execution_time' );
		if ( $limit === 0 ) {
			$default = 7200; // 2 hours.
		} elseif ( ( $limit - 10 ) < 0 ) {
			$default = 30;
		} else {
			$default = $limit - 10;
		}

		return $default;
	}

	/**
	 * Load the plugin textdomain
	 * @return void
	 */
	public function load_text_domain() {
		load_plugin_textdomain(
			$this->plugin->slug(),
			false,
			$this->plugin->path() . 'languages' . DIRECTORY_SEPARATOR
		);
	}

	/**
	 * Handle after upload hook in Admin area
	 *
	 * @param $args
	 *
	 * @since 1.7.0
	 */
	public function upload_complete( $args ) {

		$logtag = 'DGV-UPLOAD-HOOKS';
		$this->plugin->system()->logger()->log( sprintf( 'Running upload_complete hook. (%s)', wp_json_encode( [ 'args' => $args ] ) ), $logtag );

		/**
		 * Make sure we are on the right track.
		 */
		if ( ! isset( $args['vimeo_id'] ) ) {
			$this->plugin->system()->logger()->log( 'No vimeo id found. Failed to execute post upload hooks. (backend)', $logtag );

			return;
		}

		/**
		 * Obtain some important data.
		 */
		$response        = $args['vimeo_id'];
		$vimeo_formatter = new VimeoFormatter();
		$uri             = $vimeo_formatter->response_to_uri( $response );

		/**
		 * Signal start
		 */
		$this->plugin->system()->logger()->log( sprintf( 'Processing hooks for %s', $uri ), $logtag );

		/**
		 * Retrieve the source
		 */
		$source = isset( $args['source']['software'] ) ? $args['source']['software'] : null;
		if ( empty( $source ) ) {
			$this->plugin->system()->logger()->log( sprintf( '-- Source (%s) not found.', ( $source ? $source : 'NULL' ) ), $logtag );
		} else {
			$this->plugin->system()->logger()->log( sprintf( '-- Source found: %s.', $source ) );
		}

		/**
		 * Retrieve the profile
		 */
		$profile_id = $this->plugin->system()->settings()->get_upload_profile_by_context( $source );
		if ( empty( $profile_id ) ) {
			$this->plugin->system()->logger()->log( '-- No upload profile found. Please go to Vimeify > Upload Profiles and create one, then go to Vimeify Settings > Upload profiles and select the desired ones where you need them.', $logtag );
		} else {
			$this->plugin->system()->logger()->log( '-- Using profile with ID: ' . $profile_id, $logtag );
		}

		/**
		 * Set Folder
		 */
		$folder_uri = isset( $args['overrides']['folder_uri'] ) ? $args['overrides']['folder_uri'] : $this->plugin->system()->settings()->get_upload_profile_option( $profile_id, 'folder', 'default' );
		$this->set_folder( $uri, $folder_uri, $logtag );

		/**
		 * Set Embed privacy
		 */
		if ( $this->plugin->system()->vimeo()->supports_embed_privacy() ) {
			$whitelisted_domains = $this->plugin->system()->settings()->get_upload_profile_whitelisted_domains( $profile_id );
			$this->set_embed_privacy( $uri, $whitelisted_domains, $logtag );
		}

		/**
		 * Set Embed presets
		 */
		if ( $this->plugin->system()->vimeo()->supports_embed_presets() ) {
			$preset_uri = $this->plugin->system()->settings()->get_upload_profile_option( $profile_id, 'embed_preset' );
			$this->set_embed_preset( $uri, $preset_uri, $logtag );
		}

		/**
		 * Set View privacy
		 */
		$view_privacy = isset( $args['overrides']['view_privacy'] ) ? $args['overrides']['view_privacy'] : $this->plugin->system()->settings()->get_upload_profile_option( $profile_id, 'view_privacy' );
		if ( $this->plugin->system()->vimeo()->supports_view_privacy_option( $view_privacy ) ) {
			$this->set_view_privacy( $uri, $view_privacy, $logtag );
		}

		/**
		 * Create local video
		 */
		if ( (int) $this->plugin->system()->settings()->get_upload_profile_option( $profile_id, 'behavior.store_in_library', 1 ) ) {
			$this->create_local_video( $args, $logtag );
		}

		/**
		 * Upload complete hook
		 */
		do_action( 'dgv_upload_complete_hook_finished', $this, $args, $profile_id, $logtag );

		/**
		 * Signal finish
		 */
		$this->plugin->system()->logger()->log( 'Finished upload_complete hook.', $logtag );
	}
}