<?php
/********************************************************************
 * Copyright (C) 2023 Darko Gjorgjijoski (https://ideologix.com)
 *
 * This file is part of "Vimeify - Video Uploads for Vimeo"
 *
 * Vimeify - Video Uploads for Vimeo is free software: you can redistribute it
 * and/or modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation, either version 2 of the License,
 * or (at your option) any later version.
 *
 * Vimeify - Video Uploads for Vimeo is distributed in the hope that
 * it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with "Vimeify - Video Uploads for Vimeo". If not, see <https://www.gnu.org/licenses/>.
 *
 * ---
 *
 * Author Note: This code was written by Darko Gjorgjijoski <dg@darkog.com>
 * If you have any questions find the contact details in the root plugin file.
 *
 **********************************************************************/

namespace Vimeify\Core\Backend;

use Vimeify\Core\Abstracts\BaseProvider;
use Vimeify\Core\Traits\AfterUpload;
use Vimeify\Core\Utilities\Formatters\VimeoFormatter;
use Vimeo\Exceptions\VimeoRequestException;

class Hooks extends BaseProvider {

	use AfterUpload;

	/**
	 * Registers sepcific piece of functionality
	 * @return void
	 */
	public function register() {
		$this->register_activation_hook();

		add_action( 'dgv_backend_after_upload', [ $this, 'backend_after_upload' ], 5 );
	}

	/**
	 * Register installation / deactivation
	 * @return void
	 */
	public function register_activation_hook() {

		$file = $this->plugin->file();
		if ( empty( $file ) ) {
			return;
		}
		register_activation_hook( $file, function () {
			do_action( 'cv_plugin_activated', $this->plugin->id() );
			do_action( 'vimeify_plugin_activated', $this->plugin );
			$this->plugin->system()->settings()->import_defaults( false );
		} );

	}

	/**
	 * Handle after upload hook in Admin area
	 *
	 * @param $args
	 *
	 * @throws VimeoRequestException
	 * @since 1.7.0
	 */
	public function backend_after_upload( $args ) {

		$logtag  = 'DGV-BACKEND-HOOKS';

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
			$this->plugin->system()->logger()->log( sprintf('-- Source (%s) not found.', ($source ? $source : 'NULL')), $logtag );
		}

		/**
		 * Retrieve the profile
		 */
		$profile_id = $this->plugin->system()->settings()->get_upload_profile_by_context( $source );
		if ( empty( $profile_id ) ) {
			$this->plugin->system()->logger()->log( '-- No upload profile found.', $logtag );
		} else {
			$this->plugin->system()->logger()->log( '-- Using profile with ID: ' . $profile_id, $logtag );
		}


		/**
		 * Set embed privacy
		 */
		if ( $this->plugin->system()->vimeo()->supports_embed_privacy() ) {
			$whitelisted_domains = $this->plugin->system()->settings()->get_upload_profile_whitelisted_domains( $profile_id );
			$this->set_embed_privacy( $uri, $whitelisted_domains, $logtag );
		}

		/**
		 * Set Folder
		 */
		$folder_uri = $this->plugin->system()->settings()->get_upload_profile_option( $profile_id, 'folder', 'default' );
		$this->set_folder( $uri, $folder_uri, $logtag );

		/**
		 * Set Presets
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
		$this->create_local_video( $args, $logtag );

		/**
		 * Old deprecated hook
		 */
		do_action( 'dgv_after_upload', $uri, $this->plugin->system()->vimeo(), $profile_id );
	}
}