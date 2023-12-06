<?php

namespace UtogiMarketing\Property;

use WP_Query;
use WP_REST_Request;

class DocumentGroup
{
  public $name;
  public $documents;
}
class Document
{
  public $name;
  public $url;

}

class SyncInitialProperties
{

  public function __invoke()
  {
    $this->initialSyncing();
  }

  private function getPropertyQuery()
  {
    return <<<'JSON'
      {
        marketing {
          campaigns(type: PROPERTY, pagination: { page: 1, perPage: 100 }) {
            data {
              id
              name
              status
              address
              video
              referenceId
              users {
                email
                isActive
              }
              advertising {
                priceDetails {
                  content
                }
                bodies {
                  provider
                  content
                }
                headings {
                  provider
                  content
                }
                webAddress {
                  provider
                  content
                }
                openHomes {
                  content
                }
              }
              property {
                bathroom
                category
                type
                landArea
                floorArea
                bedroom
                livingRoom
                bathroom
                ensuites
                totalBathroom
                dining
                garage
                carport
                offStreetPark
                floor
                titleType
                lot
                titleNumber
                dp
                fullDescription
                localAuthority
                rates
                zoning
                yearBuilt
                coordinates {
                  lat
                  lng
                }
              }
              providerCampaigns(
                pagination: { perPage: 1, page: 1 }
                filters: { provider: PERSONAL_WEBSITE }
              ) {
                data {
                  websiteAd {
                    propertyDetails {
                      streetName
                      streetNumber
                      unit
                      suburb
                      region
                      city
                      country
                    }
                    gallery {
                      images {
                        originalUrl
                      }
                    }
                    featuredImages {
                      originalUrl
                    }
                    feature
                    standard
                    withholdAddress
                  }
                }
              }
              marketingDocuments {
                name
                documents {
                  file
                  availableOnline
                  url
                }
              }
            }
          }
        }
      }
      JSON;
  }

  private function updatePropertyLink($postId, $campaignId)
  {
    $url = get_permalink($postId);

    $mutation = <<<JSON
    mutation {
        marketing {
        syncPropertyUrl(campaignId: "$campaignId", link: "$url")
      }
    }
    JSON;

    mutation($mutation, [], get_option('utogi_marketing-api-key'));
  }

  private function initialSyncing()
  {
    $result = query($this->getPropertyQuery(), [], get_option('utogi_marketing-api-key'));
    $activeCampaigns = $result['data']['marketing']['campaigns']['data'];

    foreach ($activeCampaigns as $activeCampaign) {
      $this->syncProperty($activeCampaign);
    }
  }

  public function updateProperty(WP_REST_Request $data)
  {
    $payload = $data->get_json_params();
    if ($payload['event'] !== 'PROPERTY_CAMPAIGN_HAS_UPDATED') {
      return;
    }

    $this->syncSingleProperty($payload['campaignId']);
  }

  private function syncSingleProperty($id)
  {
    $query = <<<JSON
    {
      marketing {
        campaigns(
          ids: ["$id"]
          type: PROPERTY
          pagination: { page: 1, perPage: 100 }
        ) {
          data {
            id
            name
            status
            address
            video
            referenceId
            users {
              email
              isActive
            }
            advertising {
              priceDetails {
                content
              }
              bodies {
                provider
                content
              }
              headings {
                provider
                content
              }
              openHomes {
                content
              }
              webAddress {
                provider
                content
              }
            }
            property {
              bathroom
              category
              type
              landArea
              floorArea
              bedroom
              livingRoom
              bathroom
              ensuites
              totalBathroom
              dining
              garage
              carport
              offStreetPark
              floor
              titleType
              lot
              titleNumber
              dp
              fullDescription
              localAuthority
              rates
              zoning
              yearBuilt
              coordinates {
                lat
                lng
              }
            }
            providerCampaigns(
              pagination: { perPage: 1, page: 1 }
              filters: { provider: PERSONAL_WEBSITE }
            ) {
              data {
                websiteAd {
                  propertyDetails {
                    streetName
                    streetNumber
                    unit
                    suburb
                    region
                    city
                    country
                  }
                  gallery {
                    images {
                      originalUrl
                    }
                  }
                  featuredImages {
                    originalUrl
                  }
                  feature
                  standard
                  withholdAddress
                }
              }
            }
            marketingDocuments {
              name
              documents {
                file
                availableOnline
                url
              }
            }
          }
        }
      }
    }
    JSON;
    $result = query($query, [], get_option('utogi_marketing-api-key'));
    $activeCampaigns = $result['data']['marketing']['campaigns']['data'];

    if (count($activeCampaigns) === 0) {
      $this->trashPropertyWhenCampaignIsDeleted($id);
    }

    foreach ($activeCampaigns as $activeCampaign) {
      $this->syncProperty($activeCampaign);
    }
  }

  private function syncProperty($activeCampaign)
  {
    $websiteAd = $activeCampaign['providerCampaigns']['data'][0]['websiteAd'];
    if (!$websiteAd) {
      return;
    }
    if (!in_array($activeCampaign['status'], ['ON_THE_MARKET', 'UNDER_CONTRACT', 'SETTLING', 'SOLD', 'WITHDRAWN', 'WITHDRAWN_FROM_ON_THE_MARKET', 'WITHDRAWN_FROM_SOLD', 'WITHDRAWN_FROM_UNDER_CONTRACT', 'ARCHIVED'])) {
      return;
    }

    $id = $this->createOrUpdateProperty($activeCampaign);

    if (!$id) {
      return;
    }

    $status = 'activated';
    $stage = 'listing';
    $withholdAddress = false;

    if (!$websiteAd['standard']) {
      $status = 'withdrawn';
      $stage = 'withdrawn';
    }
    if($websiteAd['withholdAddress']) {
      $withholdAddress = $websiteAd['withholdAddress'];
    }

    if (in_array($activeCampaign['status'], ['WITHDRAWN_FROM_ON_THE_MARKET', 'WITHDRAWN_FROM_SOLD', 'WITHDRAWN_FROM_UNDER_CONTRACT'])) {
      $status = 'withdrawn';
      $stage = 'withdrawn';
    }

    if (in_array($activeCampaign['status'], ['SETTLING', 'SOLD'])) {
      $status = 'sold';
      $stage = 'sold';
    }

    if ($activeCampaign['status'] === 'ARCHIVED') {
      $status = 'archived';
      $stage = 'archived';
    }

    list($title) = $this->getAdvertisingText($activeCampaign);

    update_post_meta($id, 'utogiId', $activeCampaign['referenceId']);
    update_post_meta($id, 'utogiInternalId', $activeCampaign['id']);
    update_post_meta($id, 'propertyTitle', $title);
    update_post_meta($id, 'withholdAddress', $withholdAddress);

    update_post_meta($id, 'stage', $stage);
    update_post_meta($id, 'status', $status);

    if ($activeCampaign['video']) {
      parse_str(parse_url($activeCampaign['video'], PHP_URL_QUERY), $youtubeId);
      update_post_meta($id, 'youtubeVideoID', $youtubeId['v']);
    }

    $dont_show_statuses = array('archived', 'withdrawn');

    if (in_array($status, $dont_show_statuses)) {
      wp_trash_post($id);
    }

    $previous_status = get_post_meta($id, 'status', true);

    if (isset($activeCampaign['status']) && in_array($activeCampaign['status'], array('Active', 'active')) && in_array($previous_status, $dont_show_statuses)) {
      wp_untrash_post($id);
    }

    if (isset($websiteAd['feature']) && $websiteAd['feature']) {
      wp_set_post_tags($id, 'featured', true);
    }

    if (($activeCampaign['status'] == 'sold') || !$websiteAd['feature']) {
      if (term_exists('featured'))
        wp_remove_object_terms($id, 'featured', 'post_tag');
    }
    $this->syncPriceDetails($id, $activeCampaign);
    $this->syncAddress($id, $activeCampaign, $withholdAddress);
    $this->syncPropertyInformation($id, $activeCampaign['property']);
    $this->syncDocuments($id, $activeCampaign['marketingDocuments'], $title, $activeCampaign['id']);


    $this->syncImages($activeCampaign, $id);
    if ($status === 'activated') {
      $this->updatePropertyLink($id, $activeCampaign['id']);
    }
  }

  private function syncImages($activeCampaign, $id)
  {
    $websiteAd = $activeCampaign['providerCampaigns']['data'][0]['websiteAd'];
    $imageHost = getImageUrl();

    $featureImages = [];
    $gallery = [];

    foreach ($websiteAd['gallery']['images'] as $image) {
      $gallery[] = $image['originalUrl'];
    }

    if ($websiteAd['featuredImages']) {
      foreach ($websiteAd['featuredImages'] as $featuredImage) {
        $featureImages[] = $imageHost . $featuredImage['originalUrl'];
      }
    }

    if (!empty($gallery)) {
      $images = [];

      foreach ($gallery as $image) {
        $images[] = $imageHost . $image;
      }
      update_post_meta($id, 'galleryImages', implode(',', $images));
    }

    if (!empty($featureImages)) {
      $featureImage = $featureImages[0];
    } else if (!empty($gallery)) {
      $featureImage = $gallery[0];
    } else {
      $featureImage = '';
    }

    update_post_meta($id, 'featuredImages', implode(',', $featureImages));
    update_post_meta($id, 'featuredLarge', $featureImage);
    update_post_meta($id, 'featuredMedium', $featureImage);
    update_post_meta($id, 'featuredSmall', $featureImage);
  }

  private function getAdvertisingText($activeCampaign)
  {
    $text = $activeCampaign['advertising'];
    $defaultTitle = null;
    $title = null;

    $defaultWebAddress = null;
    $webAddress = null;

    $defaultDescription = null;
    $description = null;

    foreach ($text['headings'] as $heading) {
      if (!$heading['provider']) {
        $defaultTitle = $heading['content'];
      }
      if ($heading['provider'] === 'PERSONAL_WEBSITE') {
        $title = $heading['content'];
      }
    }

    foreach ($text['webAddress'] as $address) {
      if (!$address['provider']) {
        $defaultWebAddress = $address['content'];
      }
      if ($address['provider'] === 'PERSONAL_WEBSITE') {
        $webAddress = $address['content'];
      }
    }

    foreach ($text['bodies'] as $body) {
      if (!$body['provider']) {
        $defaultDescription = nl2br($body['content']);
      }
      if ($body['provider'] === 'PERSONAL_WEBSITE') {
        $defaultDescription = $body['content'];
      }
    }

    $title = $title ?? $defaultTitle;
    $description = $description ?? $defaultDescription;
    $webAddress = $webAddress ?? $defaultWebAddress;

    return array($title, $description, $webAddress);
  }

  private function syncPropertyInformation($id, $property)
  {
    // Property Information
    update_post_meta($id, 'bedrooms', $property['bedroom']);
    update_post_meta($id, 'bathrooms', $property['totalBathroom']);
    update_post_meta($id, 'landArea', $property['landArea']);
    update_post_meta($id, 'floorArea', $property['floorArea']);
    update_post_meta($id, 'livingrooms', $property['livingRoom']);
    update_post_meta($id, 'ensuites', $property['ensuites']);
    update_post_meta($id, 'diningrooms', $property['dining']);
    update_post_meta($id, 'garages', $property['garage']);
    update_post_meta($id, 'carports', $property['carport']);
    update_post_meta($id, 'offStreetPark', $property['offStreetPark']);

    if ($property['coordinates']) {
      update_post_meta($id, 'latitude', $property['coordinates']['lat']);
      update_post_meta($id, 'longitude', $property['coordinates']['lng']);
    }
  }

  private function syncDocuments($id, $documents, $title, $campaign)
  {
    if (count($documents) === 0) {
      return;
    }
    $available = array();
    foreach ($documents as $document) {
      $list = array();
      foreach ($document['documents'] as $file) {
        if ($file['availableOnline']) {
          $current = new Document();
          $current->name = $file['file'];
          $current->url = str_replace('api/graphql', '', getUtogiAPIURL()) . 'marketing/campaigns/document/download?file=' . urlencode($file['url']) . '&name=' . urlencode($file['file']) . '&campaign=' . $campaign . '&reference=';
          $list[] = $current;
        }
      }
      if (!empty($list)) {
        $group = new DocumentGroup();
        $group->name = $document['name'] ?? 'Other';
        $group->documents = $list;
        $available[] = $group;
      }
    }
    update_post_meta($id, 'documents', $available);
    update_post_meta($id, 'allDocuments', str_replace('api/graphql', '', getUtogiAPIURL()) . 'marketing/campaigns/documents/download?name=' . urlencode($title) . '&campaign=' . $campaign . '&reference=');
  }

  private function syncAddress($id, $activeCampaign, $withholdAddress): void
  {
    $address = $activeCampaign['providerCampaigns']['data'][0]['websiteAd']['propertyDetails'];

    // General
    update_post_meta($id, 'streetNumber', $withholdAddress ? null : $address['streetNumber']);
    update_post_meta($id, 'unit', $withholdAddress ? null : $address['unit']);
    update_post_meta($id, 'streetName', $address['streetName']);
    update_post_meta($id, 'suburb', $address['suburb']);
    update_post_meta($id, 'region', $address['region']);
    update_post_meta($id, 'city', $address['city']);
    update_post_meta($id, 'country', $address['country']);
    update_post_meta($id, 'category', $activeCampaign['property']['category']);
    update_post_meta($id, 'propertyType', $activeCampaign['property']['type']);

    $fullAddressNumber = $address['unit'] ? $address['unit'] . '/' . $address['streetNumber'] : $address['streetNumber'];
    if($withholdAddress) {
      $fullAddress = $address['streetName'] . ', ' . $address['suburb'] . ', ' . $address['city'];
    }else {
      $fullAddress = $fullAddressNumber . ' ' . $address['streetName'] . ', ' . $address['suburb'] . ', ' . $address['city'];
    }

    update_post_meta($id, 'address', $fullAddress);
  }

  private function syncPriceDetails($id, $activeCampaign)
  {
    $price = $activeCampaign['advertising']['priceDetails'][0];
    $openHomes = $activeCampaign['advertising']['openHomes'];

    if (!$price) {
      return;
    }

    $data = json_decode($price['content']);

    $saleType = $data->saleMethod === "Price By Negotiation" ? "By Negotiation" : $data->saleMethod;

    update_post_meta($id, 'saleType', $saleType);

    if (isset($data->date)) {
      update_post_meta($id, 'priceDate', $data->date);
    } else {
      update_post_meta($id, 'priceDate', null);
    }

    if (isset($data->price)) {
      update_post_meta($id, 'price', $data->price);
      update_post_meta($id, 'priceValue', $data->price);
    } else {
      update_post_meta($id, 'price', null);
      update_post_meta($id, 'priceValue', null);
    }

    if (!empty($openHomes)) {
      $formattedOpenHomes = [];
      $decodedOpenHomes = json_decode($openHomes[0]['content']);

      foreach ($decodedOpenHomes as $openHome) {
        $decodedOpenHome = json_decode($openHome);
        $formattedOpenHomes[] = $decodedOpenHome->from . '#' . $decodedOpenHome->to;
      }

      update_post_meta($id, 'openhomes', implode(',', $formattedOpenHomes));
    } else {
      update_post_meta($id, 'openhomes', '');
    }
  }

  private function trashPropertyWhenCampaignIsDeleted($campaign) {
    $id = null;
    $args = [
      'posts_per_page' => 1,
      'post_type' => 'property',
      'meta_key' => 'utogiInternalId',
      'meta_value' => $campaign,
      'post_status' => ['publish'],
    ];
    // Run the query
    $existingProperty = new WP_Query($args);
    if ($existingProperty->have_posts()) {
      while ($existingProperty->have_posts()) {
        $existingProperty->the_post();
        wp_trash_post($existingProperty->post->ID);
      }
    }
  }

  private function createOrUpdateProperty($activeCampaign)
  {
    $id = null;
    list($title, $description, $webAddress) = $this->getAdvertisingText($activeCampaign);

    $status = in_array($activeCampaign['status'], ['ON_THE_MARKET', 'UNDER_CONTRACT']) ? 'publish' : 'trash';

    $args = [
      'posts_per_page' => 1,
      'post_type' => 'property',
      'meta_key' => 'utogiInternalId',
      'meta_value' => $activeCampaign['id'],
      'post_status' => ['publish'],
    ];

    // Run the query
    $existingProperty = new WP_Query($args);
    if ($existingProperty->have_posts()) {
      while ($existingProperty->have_posts()) {
        $existingProperty->the_post();
        $id = $existingProperty->post->ID;
        $existingProperty->post->post_title = $title;
        $existingProperty->post->post_content = $description;
        $existingProperty->post->post_name = $webAddress;
        wp_update_post($existingProperty->post);
      }
      return $id;
    }

    return wp_insert_post(
      array(
        'post_title' => $title ?? $activeCampaign['address'],
        'post_type' => 'property',
        'post_content' => $description ?? $activeCampaign['address'],
        'post_status' => $status,
        "post_name" => $webAddress,
      )
    );
  }
}