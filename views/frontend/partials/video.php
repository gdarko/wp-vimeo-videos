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

/* @var string $vimeo_id */
/* @var string $embed_url */
/* @var string $vimeo_uri */

if ( empty( $embed_url ) ) {
	if ( isset( $vimeo_id ) ) {
		$embed_url = sprintf( 'https://player.vimeo.com/video/%s', $vimeo_id );
	} else if ( isset( $vimeo_uri ) ) {
		$embed_url = sprintf( 'https://player.vimeo.com/%s', str_replace( '/videos/', 'video/', $vimeo_uri ) );
	} else {
		$embed_url = '';
	}
}

?>
<div class='dgv-embed-container'>
	<?php echo sprintf( "<iframe src='%s' frameborder='0' webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>", esc_url( $embed_url ) ); ?>
</div>
