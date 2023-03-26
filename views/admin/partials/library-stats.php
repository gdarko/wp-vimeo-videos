<?php
/* @var \Vimeify\Core\Plugin $plugin */
/* @var float $total_uploaded */

?>
<div class="dgv-stats-wrap">
    <h4><?php _e('Statistics', 'wp-vimeo-videos-pro' ); ?></h4>
    <table class="dgv-stats-table">
        <tbody>
        <tr>
            <th><?php _e('Total Uploaded', 'wp-vimeo-videos-pro'); ?></th>
            <td><?php echo esc_html($total_uploaded); ?></td>
        </tr>
        </tbody>
    </table>
</div>
