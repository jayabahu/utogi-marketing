<?php




if (isset($_POST['import'])) {
    apply_filters('sync_initial_properties', []);
}



?>

<div class="notice notice-warning notice-alt">
    <p>Warning: Synchronizing will remove modified data of properties.</p>
</div>

<?php settings_fields( 'utogi_settings' ); ?>
<?php do_settings_sections( 'utogi_settings' ); ?>
<!--<table class="form-table">
    <tr valign="top">
        <th scope="row">
            Skip Sold?:
        </th>
        <td>
            <input type="checkbox" name="utogi-marketing-app-key" value="<?php /*echo get_option( 'algolia-app-key' ); */?>"/>
        </td>
    </tr>
</table>-->
<?php submit_button("Sync Properties",'primary', 'import'); ?>