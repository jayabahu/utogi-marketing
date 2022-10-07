<?php

namespace UtogiMarketing;

use UtogiMarketing\Property\SyncInitialProperties;
use UtogiMarketing\View\UIInitializer;

class Initializer
{
    /**
     * @var Loader
     */
    private $loader;

    /**
     * @var UIInitializer
     */
    private $uIInitializer;
    /**
     * @var PropertyType
     */
    private $propertyType;
    /**
     * @var SyncInitialProperties
     */
    private $syncInitialProperties;

    public function __construct()
    {
        $this->loader = new Loader();
        $this->uIInitializer = new UIInitializer();
        $this->propertyType = new PropertyType();
        $this->syncInitialProperties = new SyncInitialProperties();
    }

    public static function onActivation()
    {
        // Action on activation
    }

    public function initWebhook() {
        register_rest_route( 'utogi/v1', '/sync', array(
            'methods' => 'POST',
            'callback' => [$this->syncInitialProperties, 'updateProperty'],
        ) );
    }

    private function registerActions() {
        $this->loader->addAction('admin_init', $this->uIInitializer, "__invoke");
        //$this->loader->addAction('init', $this->propertyType, "initPropertyPostType");
        $this->loader->addAction('add_meta_boxes', $this->propertyType, "initCustomField");

        $this->loader->addAction( 'admin_menu', $this->uIInitializer, "createMenu");
        $this->loader->addAction( 'rest_api_init', $this, "initWebhook");
    }

    private function registerFilters() {
        $this->loader->addFilter( 'sync_initial_properties', $this->syncInitialProperties, "__invoke");
    }

    public function __invoke()
    {
        $this->registerActions();
        $this->registerFilters();
        $this->loader->load();
    }
}