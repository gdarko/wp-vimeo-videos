<?php
/********************************************************************
 * Copyright (C) 2020 Darko Gjorgjijoski (https://codeverve.com)
 *
 * This file is part of  WP Vimeo Videos PRO
 *
 * WP Vimeo Videos PRO is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 *  WP Vimeo Videos PRO is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with  WP Vimeo Videos PRO. If not, see <https://www.gnu.org/licenses/>.
 **********************************************************************/

/**
 * Class WP_DGV_Post_Types
 *
 * Responsible for registering the post types used by the pluyin.
 *
 * @license GPLv2
 * @copyright Darko Gjorgjijoski <info@codeverve.com>
 * @since 1.0.0
 */
class WP_DGV_Post_Types
{
    /**
     * Register the required post types
     */
    public function init()
    {
        $this->register_vimeo_uplaods();
    }

    /**
     * Register the Vimeo Uploads post type
     */
    public function register_vimeo_uplaods()
    {

    	$settings = new WP_DGV_Settings_Helper();

        $labels = array(
            'name'                  => _x('Vimeo Uploads', 'Post Type General Name', 'wp-vimeo-videos'),
            'singular_name'         => _x('Vimeo Uploads', 'Post Type Singular Name', 'wp-vimeo-videos'),
            'menu_name'             => __('Vimeo Uploads', 'wp-vimeo-videos'),
            'name_admin_bar'        => __('Vimeo Upload', 'wp-vimeo-videos'),
            'archives'              => __('Item Archives', 'wp-vimeo-videos'),
            'attributes'            => __('Item Attributes', 'wp-vimeo-videos'),
            'parent_item_colon'     => __('Parent Item:', 'wp-vimeo-videos'),
            'all_items'             => __('All Items', 'wp-vimeo-videos'),
            'add_new_item'          => __('Add New Item', 'wp-vimeo-videos'),
            'add_new'               => __('Add New', 'wp-vimeo-videos'),
            'new_item'              => __('New Item', 'wp-vimeo-videos'),
            'edit_item'             => __('Edit Item', 'wp-vimeo-videos'),
            'update_item'           => __('Update Item', 'wp-vimeo-videos'),
            'view_item'             => __('View Item', 'wp-vimeo-videos'),
            'view_items'            => __('View Items', 'wp-vimeo-videos'),
            'search_items'          => __('Search Item', 'wp-vimeo-videos'),
            'not_found'             => __('Not found', 'wp-vimeo-videos'),
            'not_found_in_trash'    => __('Not found in Trash', 'wp-vimeo-videos'),
            'featured_image'        => __('Featured Image', 'wp-vimeo-videos'),
            'set_featured_image'    => __('Set featured image', 'wp-vimeo-videos'),
            'remove_featured_image' => __('Remove featured image', 'wp-vimeo-videos'),
            'use_featured_image'    => __('Use as featured image', 'wp-vimeo-videos'),
            'insert_into_item'      => __('Insert into item', 'wp-vimeo-videos'),
            'uploaded_to_this_item' => __('Uploaded to this item', 'wp-vimeo-videos'),
            'items_list'            => __('Items list', 'wp-vimeo-videos'),
            'items_list_navigation' => __('Items list navigation', 'wp-vimeo-videos'),
            'filter_items_list'     => __('Filter items list', 'wp-vimeo-videos'),
        );
        $args   = array(
            'label'               => __('Vimeo Uploads', 'wp-vimeo-videos'),
            'description'         => __('WP Vimeo Uploads', 'wp-vimeo-videos'),
            'labels'              => $labels,
            'supports'            => array('title', 'editor'),
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
            'publicly_queryable'  => ( (bool) $settings->get( 'dgv_enable_single_pages' ) ) ? true : false,
            'rewrite'             => true,
            'capability_type'     => 'page',
        );

        $post_slug = apply_filters('dgv_post_type_slug', 'vimeo-upload');
        if ( ! empty($post_slug) ) {
            $args['rewrite'] = array(
                'slug'       => $post_slug,
                'with_front' => true,
                'pages'      => true,
                'feeds'      => false,
            );
        }

        $args = apply_filters('dgv_post_type_args', $args);

        register_post_type(WP_DGV_Db_Helper::POST_TYPE_UPLOADS, $args);

    }
}
