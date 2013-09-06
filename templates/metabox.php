<?php wp_nonce_field( 'toggle_wpautop', 'toggle_wpautop_nonce' ); ?>
<table>
    <tr valign="top">
        <th scope="row">
            <input type="checkbox" name="toggle_wpautop" id="toggle_wpautop" <?php checked(get_post_meta($post->ID,'toggle_wpautop',true)) ?> />
        </th>
        <td>
            <label for="toggle_wpautop"><?php _e('Disable auto-formatting','nouveau') ?></label>
        </td>
    </tr>
</table>