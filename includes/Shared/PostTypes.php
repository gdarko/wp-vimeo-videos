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


		// Register post type

		$args   = array(
			'label'               => __( 'Vimeo Uploads', 'wp-vimeo-videos' ),
			'description'         => __( 'Local Vimeo Library', 'wp-vimeo-videos' ),
			'labels'              => array(
				'name'                  => _x( 'Vimeo Uploads', 'Post Type General Name', 'wp-vimeo-videos' ),
				'singular_name'         => _x( 'Vimeo Uploads', 'Post Type Singular Name', 'wp-vimeo-videos' ),
				'menu_name'             => __( 'Vimeo Uploads', 'wp-vimeo-videos' ),
				'name_admin_bar'        => __( 'Vimeo Upload', 'wp-vimeo-videos' ),
				'archives'              => __( 'Item Archives', 'wp-vimeo-videos' ),
				'attributes'            => __( 'Item Attributes', 'wp-vimeo-videos' ),
				'parent_item_colon'     => __( 'Parent Item:', 'wp-vimeo-videos' ),
				'all_items'             => __( 'All Items', 'wp-vimeo-videos' ),
				'add_new_item'          => __( 'Add New Item', 'wp-vimeo-videos' ),
				'add_new'               => __( 'Add New', 'wp-vimeo-videos' ),
				'new_item'              => __( 'New Item', 'wp-vimeo-videos' ),
				'edit_item'             => __( 'Edit Item', 'wp-vimeo-videos' ),
				'update_item'           => __( 'Update Item', 'wp-vimeo-videos' ),
				'view_item'             => __( 'View Item', 'wp-vimeo-videos' ),
				'view_items'            => __( 'View Items', 'wp-vimeo-videos' ),
				'search_items'          => __( 'Search Item', 'wp-vimeo-videos' ),
				'not_found'             => __( 'Not found', 'wp-vimeo-videos' ),
				'not_found_in_trash'    => __( 'Not found in Trash', 'wp-vimeo-videos' ),
				'featured_image'        => __( 'Featured Image', 'wp-vimeo-videos' ),
				'set_featured_image'    => __( 'Set featured image', 'wp-vimeo-videos' ),
				'remove_featured_image' => __( 'Remove featured image', 'wp-vimeo-videos' ),
				'use_featured_image'    => __( 'Use as featured image', 'wp-vimeo-videos' ),
				'insert_into_item'      => __( 'Insert into item', 'wp-vimeo-videos' ),
				'uploaded_to_this_item' => __( 'Uploaded to this item', 'wp-vimeo-videos' ),
				'items_list'            => __( 'Items list', 'wp-vimeo-videos' ),
				'items_list_navigation' => __( 'Items list navigation', 'wp-vimeo-videos' ),
				'filter_items_list'     => __( 'Filter items list', 'wp-vimeo-videos' ),
			),
			'supports'            => array( 'title', 'author' ),
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => true,
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

		register_post_type( Database::POST_TYPE_UPLOADS, apply_filters( 'dgv_post_type_args', $args ) );

		// Register taxonomy
		$args = array(
			'labels'                     => array(
				'name'                       => _x( 'Categories', 'Taxonomy General Name', 'wp-vimeo-videos' ),
				'singular_name'              => _x( 'Category', 'Taxonomy Singular Name', 'wp-vimeo-videos' ),
				'menu_name'                  => __( 'Category', 'wp-vimeo-videos' ),
				'all_items'                  => __( 'All Items', 'wp-vimeo-videos' ),
				'parent_item'                => __( 'Parent Item', 'wp-vimeo-videos' ),
				'parent_item_colon'          => __( 'Parent Item:', 'wp-vimeo-videos' ),
				'new_item_name'              => __( 'New Item Name', 'wp-vimeo-videos' ),
				'add_new_item'               => __( 'Add New Item', 'wp-vimeo-videos' ),
				'edit_item'                  => __( 'Edit Item', 'wp-vimeo-videos' ),
				'update_item'                => __( 'Update Item', 'wp-vimeo-videos' ),
				'view_item'                  => __( 'View Item', 'wp-vimeo-videos' ),
				'separate_items_with_commas' => __( 'Separate items with commas', 'wp-vimeo-videos' ),
				'add_or_remove_items'        => __( 'Add or remove items', 'wp-vimeo-videos' ),
				'choose_from_most_used'      => __( 'Choose from the most used', 'wp-vimeo-videos' ),
				'popular_items'              => __( 'Popular Items', 'wp-vimeo-videos' ),
				'search_items'               => __( 'Search Items', 'wp-vimeo-videos' ),
				'not_found'                  => __( 'Not Found', 'wp-vimeo-videos' ),
				'no_terms'                   => __( 'No items', 'wp-vimeo-videos' ),
				'items_list'                 => __( 'Items list', 'wp-vimeo-videos' ),
				'items_list_navigation'      => __( 'Items list navigation', 'wp-vimeo-videos' ),
			),
			'hierarchical'               => true,
			'public'                     => true,
			'show_ui'                    => true,
			'show_admin_column'          => true,
			'show_in_nav_menus'          => true,
			'show_tagcloud'              => true,
			'show_in_rest'               => true,
			'rewrite'                    => false,
		);
		register_taxonomy( Database::TAX_CATEGORY, array( 'dgv-upload' ), $args );
	}

	/**
	 * Register the upload profiles
	 * @return void
	 */
	public function register_upload_profiles() {

		$labels = array(
			'name'                  => _x( 'Upload Profiles', 'Post Type General Name', 'wp-vimeo-videos' ),
			'singular_name'         => _x( 'Upload Profile', 'Post Type Singular Name', 'wp-vimeo-videos' ),
			'menu_name'             => __( 'Upload Profiles', 'wp-vimeo-videos' ),
			'name_admin_bar'        => __( 'Upload Profile', 'wp-vimeo-videos' ),
			'archives'              => __( 'Item Archives', 'wp-vimeo-videos' ),
			'attributes'            => __( 'Item Attributes', 'wp-vimeo-videos' ),
			'parent_item_colon'     => __( 'Parent Profile:', 'wp-vimeo-videos' ),
			'all_items'             => __( 'All Profiles', 'wp-vimeo-videos' ),
			'add_new_item'          => __( 'Add New Profile', 'wp-vimeo-videos' ),
			'add_new'               => __( 'Add New', 'wp-vimeo-videos' ),
			'new_item'              => __( 'New Profile', 'wp-vimeo-videos' ),
			'edit_item'             => __( 'Edit Profile', 'wp-vimeo-videos' ),
			'update_item'           => __( 'Update Item', 'wp-vimeo-videos' ),
			'view_item'             => __( 'View Profil', 'wp-vimeo-videos' ),
			'view_items'            => __( 'View Profiles', 'wp-vimeo-videos' ),
			'search_items'          => __( 'Search Profile', 'wp-vimeo-videos' ),
			'not_found'             => __( 'Not found', 'wp-vimeo-videos' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'wp-vimeo-videos' ),
			'featured_image'        => __( 'Featured Image', 'wp-vimeo-videos' ),
			'set_featured_image'    => __( 'Set featured image', 'wp-vimeo-videos' ),
			'remove_featured_image' => __( 'Remove featured image', 'wp-vimeo-videos' ),
			'use_featured_image'    => __( 'Use as featured image', 'wp-vimeo-videos' ),
			'insert_into_item'      => __( 'Insert into item', 'wp-vimeo-videos' ),
			'uploaded_to_this_item' => __( 'Uploaded to this item', 'wp-vimeo-videos' ),
			'items_list'            => __( 'Profiles list', 'wp-vimeo-videos' ),
			'items_list_navigation' => __( 'Profiles list navigation', 'wp-vimeo-videos' ),
			'filter_items_list'     => __( 'Filter profiles list', 'wp-vimeo-videos' ),
		);
		$args   = array(
			'label'               => __( 'Upload Profiles', 'wp-vimeo-videos' ),
			'description'         => __( 'Upload Profiles', 'wp-vimeo-videos' ),
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