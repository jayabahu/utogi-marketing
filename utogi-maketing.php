<?php

/**
 * Plugin Name:     Utogi Marketing
 * Description:     This plugin will help to integrate with marketing module
 * Text Domain:     utogi-marketing
 * Version:         1.1.0
 *
 * @package         Utogi_Marketing
 */

use UtogiMarketing\Initializer;

if ( ! defined( 'WPINC' ) ) {
    die;
}

define( 'UTOGI_MARKETING_VERSION', '1.0.0' );

require plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

define('UM_PLUGIN_URL', content_url('/plugins/utogi-marketing/'));
define('API_URL_LOCAL', 'http://api.utogi.local/api/graphql');
define('API_URL_SANDBOX', 'https://api.utogi.net/api/graphql');
define('API_URL_LIVE', 'https://api.utogi.com/api/graphql');

define('UTOGI_IMAGE_URL_LOCAL', 'http://images.utogi.local');
define('UTOGI_IMAGE_URL_SANDBOX', 'https://images.utogi.net');
define('UTOGI_IMAGE_URL_LIVE', 'https://image.utogi.com');


function activate_utogi_marketing() {
    Initializer::onActivation();
}


register_activation_hook( __FILE__, 'activate_utogi_marketing' );

$initializer = new Initializer();
$initializer();
