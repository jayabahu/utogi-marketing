<?php

namespace UtogiMarketing;

use UtogiMarketing\Property\SyncInitialProperties;
use UtogiMarketing\Campaign\CampaignDocuments;
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
      /**
     * @var CampaignDocuments
     */
    private $campaignDocuments;

    public function __construct()
    {
        $this->loader = new Loader();
        $this->uIInitializer = new UIInitializer();
        $this->propertyType = new PropertyType();
        $this->syncInitialProperties = new SyncInitialProperties();
        $this->campaignDocuments = new CampaignDocuments();
    }

    public static function onActivation()
    {
        // Action on activation
    }

    public function initWebhook()
    {
        register_rest_route('utogi/v1', '/sync', array(
            'methods' => 'POST',
            'callback' => [$this->syncInitialProperties, 'updateProperty'],
            'permission_callback' => '__return_true'
        )
        );
    }

    private function registerActions()
    {
        $this->loader->addAction('admin_init', $this->uIInitializer, "__invoke");
        //$this->loader->addAction('init', $this->propertyType, "initPropertyPostType");
        $this->loader->addAction('add_meta_boxes', $this->propertyType, "initCustomField");

        $this->loader->addAction('admin_menu', $this->uIInitializer, "createMenu");
        $this->loader->addAction('rest_api_init', $this, "initWebhook");
        $this->loader->addAction('wp_ajax_call_add_download_contact_handler', $this->campaignDocuments, "addDownloadedContact");
        $this->loader->addAction('wp_ajax_nopriv_call_add_download_contact_handler', $this->campaignDocuments, "addDownloadedContact");
    }

    private function registerFilters()
    {
        $this->loader->addFilter('sync_initial_properties', $this->syncInitialProperties, "__invoke");
    }

    public function __invoke()
    {
        $this->registerActions();
        $this->registerFilters();
        $this->loader->load();
    }
}