<?php
if (isset($_POST['import'])) {
    apply_filters('sync_initial_properties', []);
}
?>
<div class="notice notice-warning notice-alt">
    <p>Warning: Synchronizing will remove modified data of properties.</p>
</div>
<?php
settings_fields('utogi_settings');
do_settings_sections('utogi_settings');
submit_button("Sync Properties", 'primary', 'import');
?>