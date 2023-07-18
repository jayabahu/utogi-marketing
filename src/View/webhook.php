<?php settings_fields('utogi-settings'); ?>
<?php do_settings_sections('utogi-settings'); ?>
<table class="form-table">
    <tr valign="top">
        <th scope="row">
            Webhook:
        </th>
        <td>
            <input type="text" disabled style="width: 400px" name="utogi-marketing-app-key"
                value="<?php echo site_url('wp-json/utogi/v1/sync'); ?>" />
        </td>
    </tr>
</table>