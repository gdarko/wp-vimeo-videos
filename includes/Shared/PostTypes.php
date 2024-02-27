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
			'label'               => __( 'Vimeo Uploads', 'vimeify' ),
			'description'         => __( 'Local Vimeo Library', 'vimeify' ),
			'labels'              => array(
				'name'                  => _x( 'Vimeo Uploads', 'Post Type General Name', 'vimeify' ),
				'singular_name'         => _x( 'Vimeo Uploads', 'Post Type Singular Name', 'vimeify' ),
				'menu_name'             => __( 'Vimeo Uploads', 'vimeify' ),
				'name_admin_bar'        => __( 'Vimeo Upload', 'vimeify' ),
				'archives'              => __( 'Item Archives', 'vimeify' ),
				'attributes'            => __( 'Item Attributes', 'vimeify' ),
				'parent_item_colon'     => __( 'Parent Item:', 'vimeify' ),
				'all_items'             => __( 'All Items', 'vimeify' ),
				'add_new_item'          => __( 'Add New Item', 'vimeify' ),
				'add_new'               => __( 'Add New', 'vimeify' ),
				'new_item'              => __( 'New Item', 'vimeify' ),
				'edit_item'             => __( 'Edit Item', 'vimeify' ),
				'update_item'           => __( 'Update Item', 'vimeify' ),
				'view_item'             => __( 'View Item', 'vimeify' ),
				'view_items'            => __( 'View Items', 'vimeify' ),
				'search_items'          => __( 'Search Item', 'vimeify' ),
				'not_found'             => __( 'Not found', 'vimeify' ),
				'not_found_in_trash'    => __( 'Not found in Trash', 'vimeify' ),
				'featured_image'        => __( 'Featured Image', 'vimeify' ),
				'set_featured_image'    => __( 'Set featured image', 'vimeify' ),
				'remove_featured_image' => __( 'Remove featured image', 'vimeify' ),
				'use_featured_image'    => __( 'Use as featured image', 'vimeify' ),
				'insert_into_item'      => __( 'Insert into item', 'vimeify' ),
				'uploaded_to_this_item' => __( 'Uploaded to this item', 'vimeify' ),
				'items_list'            => __( 'Items list', 'vimeify' ),
				'items_list_navigation' => __( 'Items list navigation', 'vimeify' ),
				'filter_items_list'     => __( 'Filter items list', 'vimeify' ),
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
				'name'                       => _x( 'Categories', 'Taxonomy General Name', 'vimeify' ),
				'singular_name'              => _x( 'Category', 'Taxonomy Singular Name', 'vimeify' ),
				'menu_name'                  => __( 'Category', 'vimeify' ),
				'all_items'                  => __( 'All Items', 'vimeify' ),
				'parent_item'                => __( 'Parent Item', 'vimeify' ),
				'parent_item_colon'          => __( 'Parent Item:', 'vimeify' ),
				'new_item_name'              => __( 'New Item Name', 'vimeify' ),
				'add_new_item'               => __( 'Add New Item', 'vimeify' ),
				'edit_item'                  => __( 'Edit Item', 'vimeify' ),
				'update_item'                => __( 'Update Item', 'vimeify' ),
				'view_item'                  => __( 'View Item', 'vimeify' ),
				'separate_items_with_commas' => __( 'Separate items with commas', 'vimeify' ),
				'add_or_remove_items'        => __( 'Add or remove items', 'vimeify' ),
				'choose_from_most_used'      => __( 'Choose from the most used', 'vimeify' ),
				'popular_items'              => __( 'Popular Items', 'vimeify' ),
				'search_items'               => __( 'Search Items', 'vimeify' ),
				'not_found'                  => __( 'Not Found', 'vimeify' ),
				'no_terms'                   => __( 'No items', 'vimeify' ),
				'items_list'                 => __( 'Items list', 'vimeify' ),
				'items_list_navigation'      => __( 'Items list navigation', 'vimeify' ),
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
			'name'                  => _x( 'Upload Profiles', 'Post Type General Name', 'vimeify' ),
			'singular_name'         => _x( 'Upload Profile', 'Post Type Singular Name', 'vimeify' ),
			'menu_name'             => __( 'Upload Profiles', 'vimeify' ),
			'name_admin_bar'        => __( 'Upload Profile', 'vimeify' ),
			'archives'              => __( 'Item Archives', 'vimeify' ),
			'attributes'            => __( 'Item Attributes', 'vimeify' ),
			'parent_item_colon'     => __( 'Parent Profile:', 'vimeify' ),
			'all_items'             => __( 'All Profiles', 'vimeify' ),
			'add_new_item'          => __( 'Add New Profile', 'vimeify' ),
			'add_new'               => __( 'Add New', 'vimeify' ),
			'new_item'              => __( 'New Profile', 'vimeify' ),
			'edit_item'             => __( 'Edit Profile', 'vimeify' ),
			'update_item'           => __( 'Update Item', 'vimeify' ),
			'view_item'             => __( 'View Profil', 'vimeify' ),
			'view_items'            => __( 'View Profiles', 'vimeify' ),
			'search_items'          => __( 'Search Profile', 'vimeify' ),
			'not_found'             => __( 'Not found', 'vimeify' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'vimeify' ),
			'featured_image'        => __( 'Featured Image', 'vimeify' ),
			'set_featured_image'    => __( 'Set featured image', 'vimeify' ),
			'remove_featured_image' => __( 'Remove featured image', 'vimeify' ),
			'use_featured_image'    => __( 'Use as featured image', 'vimeify' ),
			'insert_into_item'      => __( 'Insert into item', 'vimeify' ),
			'uploaded_to_this_item' => __( 'Uploaded to this item', 'vimeify' ),
			'items_list'            => __( 'Profiles list', 'vimeify' ),
			'items_list_navigation' => __( 'Profiles list navigation', 'vimeify' ),
			'filter_items_list'     => __( 'Filter profiles list', 'vimeify' ),
		);
		$args   = array(
			'label'               => __( 'Upload Profiles', 'vimeify' ),
			'description'         => __( 'Upload Profiles', 'vimeify' ),
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