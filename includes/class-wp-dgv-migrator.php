<?php
/********************************************************************
 * Copyright (C) 2020 Darko Gjorgjijoski (https://codeverve.com)
 *
 * This file is part of  Video Uploads for Vimeo
 *
 * Video Uploads for Vimeo is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 *  Video Uploads for Vimeo is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with  Video Uploads for Vimeo. If not, see <https://www.gnu.org/licenses/>.
 **********************************************************************/

/**
 * Class WP_DGV_Migrator
 *
 * Responsible for running database migrations
 *
 * @license GPLv2
 * @copyright Darko Gjorgjijoski <info@codeverve.com>
 * @since 1.4.0
 */
class WP_DGV_Migrator
{
    /**
     * The logger
     * @var WP_DGV_Logger
     */
    private $logger;

    /**
     * WP_DGV_Migrator constructor.
     */
    public function __construct()
    {
        $this->logger = new WP_DGV_Logger();
    }

    /**
     * Initialize plugin
     */
    public function init()
    {
        $this->migrate_settings();
    }

    /**
     * Attempt to migrate from old settings format to new settings format
     *
     * @old <  v1.4.0
     * @new => v1.4.0
     *
     * @since 1.4.0
     */
    public function migrate_settings()
    {
        $settings = new WP_DGV_Settings_Helper();
        if ( ! $settings->requires_migration()) {
            return;
        }

        $settings_data = array();

        // Extract Data
        $pre_v140_setting_keys = array(
            'dgv_client_id',
            'dgv_client_secret',
            'dgv_access_token',
            'dgv_author_uploads_only',
        );
        foreach ($pre_v140_setting_keys as $key) {
            $value = get_option($key);
            if (false !== $value) {
                $settings_data[$key] = $value;
            }
        }

        // If no data found it means this is a new install.
        // Exit in this case.
        if(count($settings_data) === 0) {
            return;
        }

        // Convert data
        foreach ($settings_data as $key => $value) {
            $settings->set($key, $value);
            delete_option($key);
        }

        // Save
        if ($settings->updates_count() > 0) {

            $settings->save();
            // Store backup for just in case.
            set_transient('dgv_settings_backup', $settings_data, DAY_IN_SECONDS  * 100 );
        }

    }

}
