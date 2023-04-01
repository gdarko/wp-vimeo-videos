<?php
/********************************************************************
 * Copyright (C) 2020 Darko Gjorgjijoski (https://codeverve.com)
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
 * Renders view with data
 *
 * @param $view
 * @param  array  $data
 *
 * @return false|string
 * @since    1.0.0
 *
 */
function wvv_get_view( $view, $data = array() ) {
	$path = WP_VIMEO_VIDEOS_PATH . $view . '.php';
	if ( file_exists( $path ) ) {
		ob_start();
		if ( ! empty( $data ) ) {
			extract( $data );
		}
		include( $path );

		return ob_get_clean();
	}

	return '';
}

/**
 * Is the vimeo minimum php version satisfied?
 * @return bool
 * @since    1.0.0
 */
function wvv_php_version_ok() {
	return version_compare( PHP_VERSION, vimeify()->plugin()->minimum_php_version(), '>=' );
}

/**
 * Is valid domain name?
 *
 * @param $domain_name
 *
 * @return bool
 */
function wvv_is_valid_domain_name( $domain_name ) {
	return ( preg_match( "/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i", $domain_name ) //valid chars check
	         && preg_match( "/^.{1,253}$/", $domain_name ) //overall length check
	         && preg_match( "/^[^\.]{1,63}(\.[^\.]{1,63})*$/", $domain_name ) ); //length of each label
}


/**
 * Check if the block editor is active.
 * @return bool
 */
function wvv_is_gutenberg_active() {
	if ( function_exists( 'is_gutenberg_page' ) &&
	     is_gutenberg_page()
	) {
		// The Gutenberg plugin is on.
		return true;
	}

	require_once( ABSPATH . 'wp-admin/includes/screen.php' );

	$current_screen = get_current_screen();
	if ( ! is_null( $current_screen ) && method_exists( $current_screen, 'is_block_editor' ) && $current_screen->is_block_editor() ) {
		// Gutenberg page on 5+.
		return true;
	}

	return false;
}


/**
 * Return the media library upload buttons
 *
 * @param $id
 *
 * @return string
 */
function wvv_render_media_library_upload_buttons( $id ) {

	$mimetype = get_post_mime_type( $id );
	$is_video = strpos( $mimetype, 'video/' ) !== false;

	$output = '<div class="wvv-button-wrap">';
	if ( ! $is_video ) {
		$output .= "<p>" . __( 'Not a video', 'wp-vimeo-videos-pro' ) . "</p>";
	} else {
		$api  = new WP_DGV_Api_Helper();
		$db   = new WP_DGV_Db_Helper();
		$data = get_post_meta( $id, 'dgv', true );
		if ( ! isset( $data['vimeo_id'] ) ) {
			if ( ! $api->can_upload() ) {
				$output .= '<p>' . __( "Sorry! You are missing the 'upload' scope. Please check your Vimeo account and request 'upload' access to be able to upload videos from your WordPress site.", "wp-vimeo-videos" ) . '</p>';
			} elseif ( ! current_user_can( 'upload_files' ) ) {
				$output .= '<p>' . __( "Sorry! You don't have the required access to upload files.", "wp-vimeo-videos" ) . '</p>';
			} else {
				$output .= '<p><a target="_blank" class="button-primary dgv-upload-attachment" data-id="' . $id . '">' . __( 'Upload to Vimeo', 'wp-vimeo-videos-pro' ) . '</a></p>';
			}
		} else {
			$link   = $db->get_vimeo_link( $data['local_id'] );
			$delete = '';
			if ( current_user_can( 'delete_posts' ) && $api->can_delete() ) {
				$delete = '<a href="#" class="button-primary dgv-delete-attachment" data-id="' . $id . '">' . __( 'Delete from Vimeo', 'wp-vimeo-videos-pro' ) . '</a>';
			}
			$link   = '<a target="_blank" class="button" href="' . $link . '">' . __( 'Vimeo Link', 'wp-vimeo-videos-pro' ) . '</a>';
			$output .= '<p>' . __( 'Video uploaded to Vimeo.', 'wp-vimeo-videos-pro' ) . '</p>';
			$output .= '<p>' . $delete . ' ' . $link . '</p>';
		}
	}
	$output .= '</div>';

	return $output;
}

/**
 * String contains
 *
 * @param $str
 * @param $substr
 *
 * @return bool
 * @since 1.5.0
 *
 */
function wvv_str_contains( $str, $substr ) {
	return strpos( $str, $substr ) !== false;
}

/**
 * Convert the embed preset uri to ID.
 *
 * @param $uri
 *
 * @return mixed
 * @since 1.5.0
 *
 */
function wvv_embed_preset_uri_to_id( $uri ) {
	return wvv_uri_to_id( $uri );
}

/**
 * Convert Response to URI
 *  -- Support for pull method which returns array structure ['body']['uri']
 *  -- Support for upload stream method which returns the uri directly.
 *
 * @param $response
 *
 * @return string
 */
function wvv_response_to_uri( $response ) {

	$uri = '';
	if ( isset( $response['body']['uri'] ) ) { // Support for pull method
		$uri = $response['body']['uri'];
	} else {
		if ( is_numeric( $response ) ) {
			$uri = sprintf( '/videos/%s', $response );
		} elseif ( is_string( $response ) ) { // Support for upload method.
			$id = wvv_uri_to_id( $response );
			if ( is_numeric( $id ) ) {
				$uri = $response;
			}
		}
	}

	return $uri;
}

/**
 * Convert Vimeo URI to ID
 *
 * @param $uri
 *
 * @return mixed
 * @since 1.0.0
 *
 */
function wvv_uri_to_id( $uri ) {

	if ( is_array( $uri ) ) {
		if ( isset( $uri['body']['uri'] ) ) {
			$uri = $uri['body']['uri'];
		} elseif ( isset( $uri['response']['body']['uri'] ) ) {
			$uri = $uri['response']['body']['uri'];
		}
	}

	if ( ! is_string( $uri ) ) {
		return $uri;
	}

	$parts = explode( '/', $uri );

	return end( $parts );
}

/**
 * Ensure that uri is always uri.
 *
 * @param $id
 *
 * @return string
 */
function wvv_id_to_uri( $id ) {
	if ( is_numeric( $id ) || ! wvv_str_contains( $id, '/' ) ) {
		return '/videos/' . $id;
	} else {
		return $id;
	}
}

/**
 * Format bytes
 *
 * @param $bytes
 * @param  int  $precision
 *
 * @return string
 */
function wvv_format_bytes( $bytes, $precision = 2 ) {

	$units = array( 'B', 'KB', 'MB', 'GB', 'TB' );

	$bytes = max( $bytes, 0 );
	$pow   = floor( ( $bytes ? log( $bytes ) : 0 ) / log( 1024 ) );
	$pow   = min( $pow, count( $units ) - 1 );

	// Uncomment one of the following alternatives
	//$bytes /= pow( 1024, $pow );

	$bytes /= ( 1 << ( 10 * $pow ) );

	return round( $bytes, $precision ) . ' ' . $units[ $pow ];
}

/**
 * Format timezone
 *
 * @param $datetimeTz
 *
 * @return string
 * @since 1.5.2
 */
function wvv_format_datetime_tz( $datetimeTz ) {
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

/**
 * Return the guide url
 * @return string
 */
function wvv_get_guide_url() {
	return 'https://docs.codeverve.com/video-uploads-for-vimeo/';
}

/**
 * Return the purchase url
 * @return string
 */
function wvv_get_purchase_url() {
	return 'https://codeverve.com/video-uploads-for-vimeo';
}

/**
 * Return the settings url
 * @return string|void
 */
function wvv_get_settings_url() {
	return admin_url( 'admin.php?page=' . \Vimeify\Core\Backend\Ui::PAGE_SETTINGS );
}

/**
 * The vimeo insert methods in the Gutenberg and TinyMCE editors that are supported.
 * @return array
 */
function wvv_get_editor_insert_methods() {
	return array(
		'upload' => __( 'Upload new Vimeo video', 'wp-vimeo-videos-pro' ),
		'local'  => __( 'Insert Vimeo video from local library', 'wp-vimeo-videos-pro' ),
		'search' => __( 'Search your Vimeo account', 'wp-vimeo-videos-pro' ),
	);
}


/**
 * Returns the tmp dir
 * @return string
 */
function wvv_get_tmp_dir() {
	$uploads = wp_upload_dir();
	$base    = trailingslashit( $uploads['basedir'] . DIRECTORY_SEPARATOR );
	$dir     = "{$base}wp-vimeo-videos";
	if ( is_writable( $base ) ) {
		// Note: If dir already exists it will return TRUE
		if ( wp_mkdir_p( $dir ) ) {
			return $dir;
		} else {
			error_log( 'DGV: Failed to create tmp dir: ' . $dir );
		}
	} else {
		error_log( 'DGV: Base ' . $base . ' not writable.' );
	}

	return false;
}

/**
 * Return the tmp dir url
 * @return string
 */
function wvv_get_tmp_dir_url() {
	$uploads = wp_upload_dir();

	return $uploads['baseurl'] . '/wp-vimeo-videos';
}

/**
 * Return the allowed mimetypes
 */
function wvv_get_allowed_mimes() {
	return array(
		'mp4|m4v'  => 'video/mp4',
		'mov|qt'   => 'video/quicktime',
		'wmv'      => 'video/x-ms-wmv',
		'avi'      => 'video/avi',
		'flv'      => 'video/x-flv',
		'mts|m2ts' => 'video/MP2T',
	);
}

/**
 * Return the allowed extensions
 * @return array
 */
function wvv_get_allowed_extensions() {
	$allowed_extensions = array();
	foreach ( wvv_get_allowed_mimes() as $ext => $mime ) {
		$exts               = explode( '|', $ext );
		$allowed_extensions = array_merge( $allowed_extensions, $exts );
	}

	return apply_filters( 'dgv_allowed_extensions', $allowed_extensions );
}

/**
 * Returns the thumbnail url
 *
 * @param $post_id
 *
 * @return string
 */
function wvv_get_thumbnail( $post_id ) {

	$dbHelper = new WP_DGV_Db_Helper();
	$vimeo_id = $dbHelper->get_vimeo_id( $post_id );

	// Retrieve the thumbs path
	$tmp_path    = wvv_get_tmp_dir();
	$thumbs_path = trailingslashit( $tmp_path ) . 'thumbs';

	// Check if there is existing thumb
	$thumb_file = null;
	foreach ( array( 'jpg', 'png' ) as $ext ) {
		$_thumb_file = $thumbs_path . DIRECTORY_SEPARATOR . $vimeo_id . '.' . $ext;
		if ( file_exists( $_thumb_file ) ) {
			$thumb_file = $_thumb_file;
			break;
		}
	}

	$url = null;

	// Video image exists locally? Yupii
	if ( ! is_null( $thumb_file ) ) {

		$_base_url = wvv_get_tmp_dir_url();
		$url       = "{$_base_url}/thumbs/" . basename( $thumb_file );

	} else {

		// Check If the video is deleted from vimeo.com
		$is_404 = get_post_meta( $post_id, 'dgv_vimeo_404', true );
		if ( $is_404 ) {
			return null;
		}

		// Check cache
		$key  = 'wvv_' . $vimeo_id . '_thumb';
		$_url = get_transient( $key );

		// Obtain url of the video image
		if ( false === $_url ) {
			$result = wp_remote_get( "http://vimeo.com/api/v2/video/{$vimeo_id}.json" );
			if ( ! is_wp_error( $result ) ) {
				if ( isset( $result['response']['code'] ) && $result['response']['code'] === 200 ) {
					$data = json_decode( $result['body'] );
					if ( isset( $data[0]->thumbnail_medium ) ) {
						$url = $data[0]->thumbnail_medium;
					}
				}
			}
			if ( is_null( $url ) ) {
				try {
					$api      = new WP_DGV_Api_Helper();
					$pictures = $api->get( "/videos/{$vimeo_id}?sizes=250x150" );
					// If video doesn't exists when calling the api, mark it as 404.
					// This happens if the user deletes the videos from Vimeo.com site but they still remain locally.
					if ( isset( $pictures['status'] ) && $pictures['status'] === 404 ) {
						update_post_meta( $post_id, 'dgv_vimeo_404', 1 );
					}
					if ( isset( $pictures['body']['pictures']['sizes'] ) ) {
						$sizes = $pictures['body']['pictures']['sizes'];
						if ( is_array( $sizes ) && count( $sizes ) >= 1 ) {
							$url = $sizes[0]['link'];
						}
					}
				} catch ( \Exception $e ) {
				};

			}
			if ( ! is_null( $url ) ) {
				set_transient( $key, $url, HOUR_IN_SECONDS * 10 );
			}
		} else {
			$url = $_url;
		}

		// Store the video image locally
		if ( ! is_null( $url ) ) {
			$file_ext = pathinfo( $url, PATHINFO_EXTENSION );
			$contents = wp_remote_get( $url );

			$contents_body = null;
			if ( ! is_wp_error( $contents ) ) {
				$contents_body = $contents['body'];
			}

			if ( ! is_null( $contents_body ) && false !== $tmp_path ) {
				// Note: wp_mkdir_p returns TRUE if dir is already created. Does not throw errors.
				$_file_ext = explode( '?', $file_ext );
				$file_ext  = $_file_ext[0];
				if ( wp_mkdir_p( $thumbs_path ) ) {
					$thumb_path = $thumbs_path . DIRECTORY_SEPARATOR . $vimeo_id . '.' . $file_ext;
					file_put_contents( $thumb_path, $contents_body );
				}
			}
		}
	}

	return $url;
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
function wvv_validate_file_upload_error( $error ) {

	if ( 0 === $error || 4 === $error ) {
		return false;
	}

	$errors = array(
		false,
		esc_html__( 'The uploaded file exceeds the upload_max_filesize directive in php.ini.', 'wp-vimeo-videos-pro' ),
		esc_html__( 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.', 'wp-vimeo-videos-pro' ),
		esc_html__( 'The uploaded file was only partially uploaded.', 'wp-vimeo-videos-pro' ),
		esc_html__( 'No file was uploaded.', 'wp-vimeo-videos-pro' ),
		'',
		esc_html__( 'Missing a temporary folder.', 'wp-vimeo-videos-pro' ),
		esc_html__( 'Failed to write file to disk.', 'wp-vimeo-videos-pro' ),
		esc_html__( 'File upload stopped by extension.', 'wp-vimeo-videos-pro' ),
	);

	if ( array_key_exists( $error, $errors ) ) {
		return sprintf( esc_html__( 'File upload error. %s', 'wp-vimeo-videos-pro' ), $errors[ $error ] );
	}

	return false;
}

/**
 * Validate file against what WordPress is set to allow.
 *
 * @param  string  $path  Path to a newly uploaded file.
 * @param  string  $name  Name of a newly uploaded file.
 *
 * @return false|string False if no errors found, error text otherwise.
 *
 * @since 1.6.0
 *
 */
function wvv_validate_wp_filetype_and_ext( $path, $name ) {

	$wp_filetype = wp_check_filetype_and_ext( $path, $name );

	$ext             = empty( $wp_filetype['ext'] ) ? '' : $wp_filetype['ext'];
	$type            = empty( $wp_filetype['type'] ) ? '' : $wp_filetype['type'];
	$proper_filename = empty( $wp_filetype['proper_filename'] ) ? '' : $wp_filetype['proper_filename'];

	if ( $proper_filename || ! $ext || ! $type ) {
		return esc_html__( 'File type is not allowed.', 'wp-vimeo-videos-pro' );
	}

	return false;
}

/***
 * Validates request max size.
 *
 * @param $max_size
 * @param  null  $sizes
 *
 * @return false|string
 *
 * @since 1.6.0
 *
 */
function wvv_validate_request_upload_max_filesize( $max_size, $sizes = null ) {

	if ( null === $sizes && ! empty( $_FILES ) ) {
		$sizes = [];
		foreach ( $_FILES as $file ) {
			$sizes[] = $file['size'];
		}
	}

	if ( ! is_array( $sizes ) ) {
		return false;
	}

	$max_size = min( wp_max_upload_size(), $max_size );

	foreach ( $sizes as $size ) {
		if ( $size > $max_size ) {
			return sprintf( /* translators: $s - allowed file size in Mb. */
				esc_html__( 'File exceeds max size allowed (%s).', 'wp-vimeo-videos-pro' ),
				size_format( $max_size )
			);
		}
	}

	return false;
}

/**
 * Validate file extension
 *
 * @param $ext
 *
 * @return false|string
 *
 * @since 1.6.0
 */
function wvv_validate_file_extension( $ext ) {

	// Make sure file has an extension first.
	if ( empty( $ext ) ) {
		return esc_html__( 'File must have an extension.', 'wp-vimeo-videos-pro' );
	}

	// Validate extension against all allowed values.
	if ( ! in_array( $ext, wvv_get_allowed_extensions(), true ) ) {
		return esc_html__( 'File type is not allowed.', 'wp-vimeo-videos-pro' );
	}

	return false;
}


/**
 * Convert a file size provided, such as "2M", to bytes.
 *
 * @link http://stackoverflow.com/a/22500394
 *
 * @since 1.6.0
 *
 * @param  bool  $bytes
 *
 * @return mixed
 */
function wvv_max_upload_size( $bytes = false ) {

	$max = wp_max_upload_size();
	if ( $bytes ) {
		return $max;
	}

	return size_format( $max );
}

/**
 * Set up the error messages
 *
 * @param $key
 *
 * @return mixed|string|void
 *
 * @since 1.6.0
 */
function wvv_get_error_message( $key ) {
	$messages = array(
		'invalid_vimeo_video' => __( 'Invalid video vimeo provided', 'wp-vimeo-videos-pro' ),
		'invalid_file'        => __( 'Video file is required. Please pick a valid video file.', 'wp-vimeo-videos-pro' ),
		'invalid_title'       => __( 'Title is required. Please specify valid video title.', 'wp-vimeo-videos-pro' ),
		'not_connected'       => __( 'Unable to connect to Vimeo for the file upload.', 'wp-vimeo-videos-pro' ),
		'not_authenticated'   => __( 'Connection to Vimeo is successful. However we detected that the connection is made with unauthenticated access token. To connect to Vimeo successfully "Authenticated" Access Token is required with the proper Scopes selected.', 'wp-vimeo-videos-pro' ),
		'cant_upload'         => __( 'Connection to Vimeo is successful. However we detected that the current Access Token is missing the Upload scope. To be able to upload Videos successfully "Authenticated" Access Token is required with all the Scopes selected.', 'wp-vimeo-videos-pro' ),
		'quota_limit'         => __( 'Sorry, the current remaining quota in the Vimeo account is %s and this file is %s. Therefore the video can not be uploaded because the Vimeo account doesn\'t have enough free space.', 'wp-vimeo-videos-pro' ),
	);
	if ( ! isset( $messages[ $key ] ) ) {
		return __( 'Something went wrong. Please try again later.', 'wp-vimeo-videos-pro' );
	} else {
		return $messages[ $key ];
	}
}

/**
 *  Set correct file permissions for specific file.
 *
 * @param $path
 *
 * @since 1.6.0
 */
function wvv_set_fs_permissions( $path ) {
	$stat = stat( dirname( $path ) );
	@chmod( $path, $stat['mode'] & 0000666 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
}

/**
 * Check if function is available
 *
 * @param $name
 *
 * @return bool
 *
 * @since 1.6.0
 */
function wvv_is_function_available( $name ) {
	static $available;
	if ( ! isset( $available ) ) {
		if ( ! function_exists( $name ) ) {
			$available = false;
		} else {
			$available = true;
			$d         = ini_get( 'disable_functions' );
			$s         = ini_get( 'suhosin.executor.func.blacklist' );
			if ( "$d$s" ) {
				$array = preg_split( '/,\s*/', "$d,$s" );
				if ( in_array( $name, $array ) ) {
					$available = false;
				}
			}
		}
	}

	return $available;
}

/**
 * Return the vimeo icon url
 *
 * @param  null  $size
 *
 * @return string
 *
 * @since 1.7.0
 */
function wvv_get_vimeo_icon_url( $size = null ) {
	return sprintf( '%s/%s', WP_VIMEO_VIDEOS_URL, 'shared/img/icon-64.png' );
}

/**
 * Returns the user edit url
 *
 * @param  int  $id
 *
 * @return string
 *
 * @since 1.7.3
 */
function wvv_get_user_edit_url( $id ) {
	if ( is_object( $id ) && isset( $id->ID ) ) {
		$id = $id->ID;
	} elseif ( is_array( $id ) && isset( $id['ID'] ) ) {
		$id = $id['ID'];
	}

	return admin_url( sprintf( 'user-edit.php?user_id=%s', $id ) );
}

/**
 * Returns link to the user profile
 *
 * @param $id
 *
 * @return string|void
 *
 * @since 1.7.3
 */
function wvv_get_user_edit_link( $id ) {

	$user = wp_cache_get( 'user_' . $id, 'dgv' );
	if ( false === $user ) {
		$user = get_user_by( 'id', $id );
		wp_cache_set( 'user_' . $id, $user, 'dgv' );
	}

	$name = '';
	$link = '';
	if ( is_a( $user, '\WP_User' ) ) {
		$link = wvv_get_user_edit_url( $user->ID );
		if ( ! empty( $user->display_name ) ) {
			$name = $user->display_name;
		} elseif ( ! empty( $user->user_nicename ) ) {
			$name = $user->user_nicename;
		} elseif ( ! empty( $user->user_login ) ) {
			$name = $user->user_login;
		}
	}

	return $name ? sprintf( '<a href="%s">%s</a>', $link, $name ) : __( 'Unknown' );
}
