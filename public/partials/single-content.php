<?php
// This is single vimeo view for specific vimeo video

/* @var WP_Post $post */
/* @var $content */

$vimeo_id = $this->db_helper->get_vimeo_id($post->ID);
if(!empty($vimeo_id)) {
    $before = '<div class="dgv-vimeo-preview">[dgv_vimeo_video id="' . $vimeo_id . '"]</div>';
    $content = $before . $content;
    echo $content;
}