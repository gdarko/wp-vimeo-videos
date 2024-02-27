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

/* @var string $vimeo_id */
/* @var string $embed_url */
/* @var string $vimeo_uri */
/* @var string $thumbnail */

if ( empty( $embed_url ) ) {
	if ( isset( $vimeo_id ) ) {
		$embed_url = sprintf( 'https://player.vimeo.com/video/%s', $vimeo_id );
	} else if ( isset( $vimeo_uri ) ) {
		$embed_url = sprintf( 'https://player.vimeo.com/%s', str_replace( '/videos/', 'video/', $vimeo_uri ) );
	} else {
		$embed_url = '';
	}
}

$player_params = apply_filters( 'dgv_embed_player_args', [
	'loop'        => false,
	'byline'      => false,
	'portrait'    => false,
	'title'       => false,
	'speed'       => true,
	'transparent' => 0,
	'gesture'     => 'media',
] );

if ( ! empty( $player_params ) ) {
	$embed_url = add_query_arg( $player_params, $embed_url );
}
?>

<div class="dgv-embed-modern">
    <div allowfullscreen="" allow="autoplay" data-iframe-src="<?php echo esc_url( $embed_url ); ?>" class="dgv-embed-modern-video-preview-image" style="background-image: url(<?php echo esc_url( $thumbnail ); ?>);"></div>
    <div class="dgv-embed-modern-video-overlay"></div>
    <span class="dgv-embed-modern-video-overlay-icon vimeify-play"></span>
</div>


<style>
.dgv-embed-modern {
    align-items: center;
    background-color: var(--bricks-bg-light);
    display: flex;
    justify-content: center;
    overflow: hidden;
    padding-top: 56.25%;
    position: relative;
    width: 100%;
}
.dgv-embed-modern-video-overlay, .dgv-embed-modern-video-preview-image {
    background-size: cover;
    bottom: 0;
    cursor: pointer;
    left: 0;
    position: absolute;
    right: 0;
    top: 0;
}

.dgv-embed-modern-video-preview-image {
    background-position: 50%;
    background-repeat: no-repeat;
}

.dgv-embed-modern-video-overlay {
    background-color: rgba(20, 20, 20, 0.47);
}

.dgv-embed-modern-video-overlay-icon {
    color: #fff;
    cursor: pointer;
    font-size: 60px;
    left: 50%;
    position: absolute;
    top: 50%;
    transform: translate(-50%,-50%);
    z-index: 2;
}

.dgv-embed-modern iframe {
    border: none;
    height: 100%;
    position: absolute;
    top: 0;
    width: 100%;
}

</style>