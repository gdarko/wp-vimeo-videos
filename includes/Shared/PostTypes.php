<?php

namespace Vimeify\Core\Shared;

use Vimeify\Core\Abstracts\BaseProvider;
use Vimeify\Core\Components\Database;

class PostTypes extends BaseProvider {

	/**
	 * Registers sepcific piece of functionality
	 * @return void
	 */
	public function register() {
		add_action( 'init', [ $this, 'register_vimeo_uplaods' ] );
		add_action( 'init', [ $this, 'register_upload_profiles' ] );
	}

	/**
	 * Register the Vimeo Uploads post type
	 */
	public function register_vimeo_uplaods() {

		$labels = array(
			'name'                  => _x( 'Vimeo Uploads', 'Post Type General Name', 'wp-vimeo-videos-pro' ),
			'singular_name'         => _x( 'Vimeo Uploads', 'Post Type Singular Name', 'wp-vimeo-videos-pro' ),
			'menu_name'             => __( 'Vimeo Uploads', 'wp-vimeo-videos-pro' ),
			'name_admin_bar'        => __( 'Vimeo Upload', 'wp-vimeo-videos-pro' ),
			'archives'              => __( 'Item Archives', 'wp-vimeo-videos-pro' ),
			'attributes'            => __( 'Item Attributes', 'wp-vimeo-videos-pro' ),
			'parent_item_colon'     => __( 'Parent Item:', 'wp-vimeo-videos-pro' ),
			'all_items'             => __( 'All Items', 'wp-vimeo-videos-pro' ),
			'add_new_item'          => __( 'Add New Item', 'wp-vimeo-videos-pro' ),
			'add_new'               => __( 'Add New', 'wp-vimeo-videos-pro' ),
			'new_item'              => __( 'New Item', 'wp-vimeo-videos-pro' ),
			'edit_item'             => __( 'Edit Item', 'wp-vimeo-videos-pro' ),
			'update_item'           => __( 'Update Item', 'wp-vimeo-videos-pro' ),
			'view_item'             => __( 'View Item', 'wp-vimeo-videos-pro' ),
			'view_items'            => __( 'View Items', 'wp-vimeo-videos-pro' ),
			'search_items'          => __( 'Search Item', 'wp-vimeo-videos-pro' ),
			'not_found'             => __( 'Not found', 'wp-vimeo-videos-pro' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'wp-vimeo-videos-pro' ),
			'featured_image'        => __( 'Featured Image', 'wp-vimeo-videos-pro' ),
			'set_featured_image'    => __( 'Set featured image', 'wp-vimeo-videos-pro' ),
			'remove_featured_image' => __( 'Remove featured image', 'wp-vimeo-videos-pro' ),
			'use_featured_image'    => __( 'Use as featured image', 'wp-vimeo-videos-pro' ),
			'insert_into_item'      => __( 'Insert into item', 'wp-vimeo-videos-pro' ),
			'uploaded_to_this_item' => __( 'Uploaded to this item', 'wp-vimeo-videos-pro' ),
			'items_list'            => __( 'Items list', 'wp-vimeo-videos-pro' ),
			'items_list_navigation' => __( 'Items list navigation', 'wp-vimeo-videos-pro' ),
			'filter_items_list'     => __( 'Filter items list', 'wp-vimeo-videos-pro' ),
		);
		$args   = array(
			'label'               => __( 'Vimeo Uploads', 'wp-vimeo-videos-pro' ),
			'description'         => __( 'Local Vimeo Library', 'wp-vimeo-videos-pro' ),
			'labels'              => $labels,
			'supports'            => array( 'title', 'editor' ),
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => false,
			'show_in_menu'        => false,
			'menu_position'       => 5,
			'show_in_admin_bar'   => false,
			'show_in_nav_menus'   => true,
			'can_export'          => true,
			'has_archive'         => false,
			'exclude_from_search' => false,
			'publicly_queryable'  => ( (bool) $this->plugin->system()->settings()->get( 'frontend.behavior.enable_single_pages' ) ) ? true : false,
			'rewrite'             => true,
			'capability_type'     => 'page',
		);

		$post_slug = apply_filters( 'dgv_post_type_slug', 'vimeo-upload' );
		if ( ! empty( $post_slug ) ) {
			$args['rewrite'] = array(
				'slug'       => $post_slug,
				'with_front' => true,
				'pages'      => true,
				'feeds'      => false,
			);
		}

		$args = apply_filters( 'dgv_post_type_args', $args );

		register_post_type( Database::POST_TYPE_UPLOADS, $args );

	}

	/**
	 * Register the upload profiles
	 * @return void
	 */
	public function register_upload_profiles() {

		$labels = array(
			'name'                  => _x( 'Upload Profiles', 'Post Type General Name', 'wp-vimeo-videos-pro' ),
			'singular_name'         => _x( 'Upload Profile', 'Post Type Singular Name', 'wp-vimeo-videos-pro' ),
			'menu_name'             => __( 'Upload Profiles', 'wp-vimeo-videos-pro' ),
			'name_admin_bar'        => __( 'Upload Profile', 'wp-vimeo-videos-pro' ),
			'archives'              => __( 'Item Archives', 'wp-vimeo-videos-pro' ),
			'attributes'            => __( 'Item Attributes', 'wp-vimeo-videos-pro' ),
			'parent_item_colon'     => __( 'Parent Profile:', 'wp-vimeo-videos-pro' ),
			'all_items'             => __( 'All Profiles', 'wp-vimeo-videos-pro' ),
			'add_new_item'          => __( 'Add New Profile', 'wp-vimeo-videos-pro' ),
			'add_new'               => __( 'Add New', 'wp-vimeo-videos-pro' ),
			'new_item'              => __( 'New Profile', 'wp-vimeo-videos-pro' ),
			'edit_item'             => __( 'Edit Profile', 'wp-vimeo-videos-pro' ),
			'update_item'           => __( 'Update Item', 'wp-vimeo-videos-pro' ),
			'view_item'             => __( 'View Profil', 'wp-vimeo-videos-pro' ),
			'view_items'            => __( 'View Profiles', 'wp-vimeo-videos-pro' ),
			'search_items'          => __( 'Search Profile', 'wp-vimeo-videos-pro' ),
			'not_found'             => __( 'Not found', 'wp-vimeo-videos-pro' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'wp-vimeo-videos-pro' ),
			'featured_image'        => __( 'Featured Image', 'wp-vimeo-videos-pro' ),
			'set_featured_image'    => __( 'Set featured image', 'wp-vimeo-videos-pro' ),
			'remove_featured_image' => __( 'Remove featured image', 'wp-vimeo-videos-pro' ),
			'use_featured_image'    => __( 'Use as featured image', 'wp-vimeo-videos-pro' ),
			'insert_into_item'      => __( 'Insert into item', 'wp-vimeo-videos-pro' ),
			'uploaded_to_this_item' => __( 'Uploaded to this item', 'wp-vimeo-videos-pro' ),
			'items_list'            => __( 'Profiles list', 'wp-vimeo-videos-pro' ),
			'items_list_navigation' => __( 'Profiles list navigation', 'wp-vimeo-videos-pro' ),
			'filter_items_list'     => __( 'Filter profiles list', 'wp-vimeo-videos-pro' ),
		);
		$args   = array(
			'label'               => __( 'Upload Profiles', 'wp-vimeo-videos-pro' ),
			'description'         => __( 'Upload Profiles', 'wp-vimeo-videos-pro' ),
			'labels'              => $labels,
			'supports'            => array( 'title' ),
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => false,
			'menu_position'       => 5,
			'show_in_admin_bar'   => false,
			'show_in_nav_menus'   => false,
			'can_export'          => true,
			'has_archive'         => false,
			'exclude_from_search' => false,
			'publicly_queryable'  => false,
			'rewrite'             => true,
			'capability_type'     => 'page',
		);

		register_post_type( Database::POST_TYPE_UPLOAD_PROFILES, $args );
	}
}