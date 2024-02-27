<?php
/********************************************************************
 * Copyright (C) 2023 Darko Gjorgjijoski (https://darkog.com/)
 * Copyright (C) 2023 IDEOLOGIX MEDIA Dooel (https://ideologix.com/)
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

/* @var \Vimeify\Core\Plugin $plugin */

// Urls
$url_guide    = $plugin->documentation_url();
$url_purchase = $plugin->commercial_url();

// Dismiss Init
$nonce        = wp_create_nonce( 'wvv_instructions_dismiss' );
$actual_link  = ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] === 'on' ? "https" : "http" ) . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$dismiss_link = add_query_arg( 'wvv_nonce', $nonce, $actual_link );
$dismiss_link = add_query_arg( 'wvv_dismiss_instructions', 1, $dismiss_link );
?>
<div class="instructions dgv-instructions notice">
    <div class="dgv-instructions-card dgv-instructions-card-shadow">
        <div class="dgv-instructions-row dgv-instructions-header">
            <div class="dgv-instructions-colf">
                <p class="lead"><?Php _e( 'Thanks for installing', 'vimeify' ); ?> <strong
                            class="green"><?php _e( 'Video Uploads for Vimeo', 'vimeify' ); ?></strong></p>
                <p class="desc"><?php echo sprintf( __( 'This plugin allows you to easily upload and embed Vimeo videos through your WordPress website.', 'vimeify' ) ); ?></p>
                <p class="desc"><?php echo sprintf( __( 'To %s please follow the steps below:', 'vimeify' ), '<strong>' . __( 'get started', 'vimeify' ) . '</strong>' ); ?></p>
            </div>
        </div>
        <div class="dgv-instructions-row">
            <div class="dgv-instructions-col4">
                <div class="dgv-instructions-instruction">
                    <h4 class="navy"><?php _e( '1. Vimeo Developer Portal', 'vimeify' ); ?></h4>
                    <p>
						<?php
						$txt_create_app = '<strong>' . __( 'Create App', 'vimeify' ) . '</strong>';
						$txt_dev_portal = '<a target="_blank" href="https://developer.vimeo.com/">' . __( 'Vimeo Developer Portal', 'vimeify' ) . '</a>';
						echo sprintf( __( 'To get started and successfully connect the plugin to Vimeo you will need to sign up at %s and then %s that will be used by your website.', 'vimeify' ), $txt_dev_portal, $txt_create_app );
						?>
                    </p>
                </div>
            </div>
            <div class="dgv-instructions-col4">
                <div class="dgv-instructions-instruction">
                    <h4 class="navy"><?php _e( '2. Request Upload Access', 'vimeify' ); ?></h4>
                    <p>
						<?php _e( 'In order to be able to upload videos from external software like this plugin you need to request Upload Access from Vimeo and wait for approval.', 'vimeify' ); ?><br/>
                        <strong><?php _e( '(1-5 days required for approval)', 'vimeify' ); ?></strong>
                    </p>
                </div>
            </div>
            <div class="dgv-instructions-col4">
                <div class="dgv-instructions-instruction">
                    <h4 class="navy"><?php _e( '3. Obtain API Credentials', 'vimeify' ); ?></h4>
                    <p>
						<?php
						$txt_c_id  = '<strong>' . __( 'Client ID', 'vimeify' ) . '</strong>';
						$txt_c_sec = '<strong>' . __( 'Client Secret', 'vimeify' ) . '</strong>';
						$txt_a_tok = '<strong>' . __( 'Access Token', 'vimeify' ) . '</strong>';
						echo sprintf( __( 'After your upload access is approved you will need to create access token and also collect the required credentials such as %s, %s, %s.', 'vimeify' ), $txt_c_id, $txt_c_sec, $txt_a_tok );
						?>
                    </p>
                </div>
            </div>
            <div class="dgv-instructions-col4">
                <div class="dgv-instructions-instruction">
                    <h4 class="navy"><?php _e( '4. Setup Credentials', 'vimeify' ); ?></h4>
                    <p>
						<?php
						$txt_settings = '<a href="' . $plugin->settings_url() . '">' . __( 'settings', 'vimeify' ) . '</a>';
						echo sprintf( __( 'Finally, assuming that you have upload access and all the credentials from the step 3, you need to enter those in the plugin %s page', 'vimeify' ), $txt_settings );
						?>
                    </p>
                </div>
            </div>
        </div>
        <div class="dgv-instructions-row">
            <div class="dgv-instructions-colf wvv-pt-0 wvv-pb-0">
               <a class="button-small button-primary" target="_blank" href="<?php echo esc_url($url_guide); ?>"><?php _e( 'Read guide', 'vimeify' ); ?></a>
            </div>
            <div class="dgv-instructions-colf wvv-pb-0">
                <hr/>
            </div>
        </div>
        <div class="dgv-instructions-row dgv-instructions-mb-10">
            <div class="dgv-instructions-colf">
                <div class="dgv-instructions-extra">
                    <h4 class="navy"><?php _e( 'Support', 'vimeify' ); ?></h4>
                    <p>
						<?php _e( 'If you need any help setting up the plugin, feel free to get in touch with us and we can discuss more!', 'vimeify' ); ?>
                    </p>
                </div>
            </div>
        </div>
        <a href="<?php echo esc_url($dismiss_link); ?>" class="notice-dismiss dgv-notice-dismiss"><span class="screen-reader-text"><?php _e( 'Dismiss this notice', 'vimeify' ); ?>.</span></a>
    </div>
</div>
