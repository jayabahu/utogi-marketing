<?php

namespace UtogiMarketing\View;

class UIInitializer
{

    /**
     * @var APIValidator
     */
    private $validateApiToken;

    public function __construct()
    {
        $this->validateApiToken = new APIValidator();
    }

    public function createMenu()
    {

        $page_title = 'Utogi';
        $menu_title = 'Utogi';
        $capability = 'manage_options';
        $menu_slug = 'utogi_settings';
        $function = 'marketing_settings_page';
        $icon_url = UM_PLUGIN_URL . 'src/assets/favicon.ico';
        add_menu_page($page_title, $menu_title, $capability, $menu_slug, $function, $icon_url);
    }

    public function __invoke()
    {
        register_setting('utogi_settings', 'utogi_marketing-api-key', $this->validateApiToken);
        register_setting('utogi_settings', 'utogi_marketing-is-sandbox');
    }

}