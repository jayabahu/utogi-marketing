<?php settings_fields( 'utogi_settings' ); ?>
<?php do_settings_sections( 'utogi_settings' ); ?>
<table class="form-table">
   <!-- <tr valign="top">
        <th scope="row">
            Use sandbox :
        </th>
        <td>

            <input
                    type="checkbox"
                    name="utogi_marketing-is-sandbox"
                    value="1"
                    checked
            />
        </td>
    </tr>-->
    <tr valign="top">
        <th scope="row">
            App Key:
        </th>
        <td>
            <input type="text" style="width: 400px" name="utogi_marketing-api-key" value="<?php echo get_option( 'utogi_marketing-api-key' ); ?>"/>
        </td>
    </tr>
</table>
<?php submit_button(); ?>