<?php
/**
 * Plugin Name:     Utogi Marketing
 * Description:     This plugin will help to integrate with marketing module
 * Text Domain:     utogi-marketing
 * Version:         1.1.4
 *
 * @package         Utogi_Marketing
 */

use UtogiMarketing\Initializer;

if (!defined('WPINC')) {
    die;
}

define('UTOGI_MARKETING_VERSION', '1.1.4');

require plugin_dir_path(__FILE__) . 'vendor/autoload.php';

define('UM_PLUGIN_URL', content_url('/plugins/utogi-marketing/'));
define('API_URL', 'https://api.utogi.com/api/graphql');

define('UTOGI_IMAGE_URL', 'https://image.utogi.com');


function activate_utogi_marketing()
{
    Initializer::onActivation();
}

register_activation_hook(__FILE__, 'activate_utogi_marketing');

$initializer = new Initializer();
$initializer();

if (!class_exists('UtogiMarketingUpdater')) {
    include_once(plugin_dir_path(__FILE__) . 'updater.php');
}

$updater = new UtogiMarketingUpdater(__FILE__);
$updater->set_username('jayabahu');
$updater->set_repository('utogi-marketing');

$updater->initialize();