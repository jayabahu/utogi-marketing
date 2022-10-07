<?php

namespace UtogiMarketing\Property;

use WP_Query;
use WP_REST_Request;

class SyncInitialProperties
{

    public function __invoke()
    {
       $this->initialSyncing();
    }

    private function getPropertyQuery() {
        return <<<'JSON'
        {
       marketing {
        campaigns(type: PROPERTY, pagination: {page: 1, perPage: 100}) {
            data {
                id
                name
                status
                address
                video
                referenceId
                users{ 
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
                providerCampaigns(pagination: { perPage: 1, page: 1 }, filters: { provider: WEBSITE }) {
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
                    }
                  }
                }
            }
        }
      }
    }
JSON;
    }

    private function updatePropertyLink($postId, $campaignId) {

        $url = get_permalink($postId);

        $mutation = <<<JSON
        mutation {
            marketing {
            syncPropertyUrl(campaignId: "$campaignId", link: "$url")
          }
        }
JSON;

         mutation($mutation, [], get_option( 'utogi_marketing-api-key' ) );
    }


    private function initialSyncing() {

        $result  = query($this->getPropertyQuery(), [], get_option( 'utogi_marketing-api-key' ) );
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
        campaigns(ids: ["$id"],type: PROPERTY, pagination: {page: 1, perPage: 100}) {
            data {
                id
                name
                status
                address
                video
                referenceId
                users{ 
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
                  webAddress{
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
                providerCampaigns(pagination: { perPage: 1, page: 1 }, filters: { provider: WEBSITE }) {
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
                    }
                  }
                }
            }
        }
      }
    }
JSON;
        $result  = query($query, [], get_option( 'utogi_marketing-api-key' ) );
        $activeCampaigns = $result['data']['marketing']['campaigns']['data'];

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
          if (!in_array($activeCampaign['status'], ['ACTIVATED', 'SOLD', 'WITHDRAWN', 'ARCHIVED']) ) {
              return;
          }

        $id = $this->createOrUpdateProperty($activeCampaign);

        if (!$id) {
            return;
        }

        $status = 'activated';
        $stage = 'listing';

        if (!$websiteAd['standard']) {
            $status = 'withdrawn';
            $stage = 'withdrawn';
        }

        if ($activeCampaign['status'] === 'WITHDRAWN') {
            $status = 'withdrawn';
            $stage = 'withdrawn';
        }

        if ($activeCampaign['status'] === 'SOLD') {
            $status = 'sold';
            $stage = 'sold';
        }

        if ($activeCampaign['status'] === 'ARCHIVED') {
            $status = 'archived';
            $stage = 'archived';
        }


        update_post_meta($id, 'utogiId', $activeCampaign['referenceId']);
        update_post_meta($id, 'utogiInternalId', $activeCampaign['id']);


        update_post_meta($id, 'stage', $stage);
        update_post_meta($id, 'status', $status);

        if ($activeCampaign['video']) {
            parse_str( parse_url( $activeCampaign['video'], PHP_URL_QUERY ), $youtubeId );
            update_post_meta($id, 'youtubeVideoID', $youtubeId['v']);
        }

        $dont_show_statuses = array('archived', 'withdrawn');

        if(in_array($status, $dont_show_statuses)) {
            wp_trash_post($id);
        }

        $previous_status = get_post_meta( $id, 'status', true);

        if(isset($activeCampaign['status']) && in_array($activeCampaign['status'], array('Active', 'active')) && in_array($previous_status, $dont_show_statuses)) {
            wp_untrash_post( $id  );
        }

        if(isset($websiteAd['feature']) && $websiteAd['feature']) {
            wp_set_post_tags( $id, 'featured', true );
        }

        if(($activeCampaign['status'] == 'sold') || !$websiteAd['feature']) {
            wp_remove_object_terms( $id, 'featured', 'post_tag' );
        }


        $this->syncPriceDetails($id, $activeCampaign);
        $this->syncAddress($id, $activeCampaign);
        $this->syncPropertyInformation($id, $activeCampaign['property']);

        $this->syncAgents($activeCampaign, $id);
        $this->syncImages($activeCampaign, $id);
        if ($status === 'activated') {
            $this->updatePropertyLink($id, $activeCampaign['id']);
        }

    }

    private function syncAgents($activeCampaign, $id): void
    {
        $users = $activeCampaign['users'];

        if (!empty($users)) {
            $agents = [];
            foreach ($users as $user) {
                if ($user['isActive']) {
                    $agents[] = $user['email'];
                }
            }

            if (!empty($agents)) {
                $agentIds = [];
                foreach ($agents as $agent) {
                    $agentIds[] = $this->getAgentId($agent);
                }
                update_post_meta($id, 'agentIDs', implode(',', $agentIds));
            }
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
               $featureImages[] = $imageHost.$featuredImage['originalUrl'];
           }
       }


        if (!empty($gallery)) {
            $images = [];

            foreach ($gallery as $image) {
                $images[] = $imageHost.$image;
            }
            update_post_meta($id, 'galleryImages', implode(',', $images));
        }


        if (!empty($featureImages)) {
            $featureImage = $featureImages[0];
        } else {
            $featureImage = $gallery[0];
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
            if ($heading['provider'] === 'WEBSITE') {
                $title = $heading['content'];
            }
        }


        foreach ($text['webAddress'] as $address) {
            if (!$address['provider']) {
                $defaultWebAddress = $address['content'];
            }
            if ($address['provider'] === 'WEBSITE') {
                $webAddress = $address['content'];
            }
        }


        foreach ($text['bodies'] as $body) {
            if (!$body['provider']) {
                $defaultDescription = nl2br($body['content']);
            }
            if ($body['provider'] === 'WEBSITE') {
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

    private function syncAddress($id, $activeCampaign): void
    {
        $address = $activeCampaign['providerCampaigns']['data'][0]['websiteAd']['propertyDetails'];

        // General
        update_post_meta($id, 'streetNumber', $address['streetNumber']);
        update_post_meta($id, 'unit', $address['unit']);
        update_post_meta($id, 'streetName', $address['streetName']);
        update_post_meta($id, 'suburb', $address['suburb']);
        update_post_meta($id, 'region', $address['region']);
        update_post_meta($id, 'city', $address['city']);
        update_post_meta($id, 'country', $address);
        update_post_meta($id, 'category', $activeCampaign['property']['category']);
        update_post_meta($id, 'propertyType', $activeCampaign['property']['type']);

        $fullAddressNumber = $address['unit'] ? $address['unit'].'/'.$address['streetNumber'] : $address['streetNumber'];
        $fullAddress = $fullAddressNumber.' '.$address['streetName'].', '.$address['suburb'].', '.$address['city'];


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

        if ($data->date) {
            update_post_meta($id, 'priceDate', $data->date);
        } else {
            update_post_meta($id, 'priceDate', null);
        }


        if ($data->price) {
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
                $formattedOpenHomes[] = $decodedOpenHome->from .'#'. $decodedOpenHome->to;
            }

            update_post_meta($id, 'openhomes', implode(',', $formattedOpenHomes));
        } else {
            update_post_meta($id, 'openhomes', '');
        }

    }

    private function createOrUpdateProperty($activeCampaign)
    {
        $id = null;
        list($title, $description, $webAddress) = $this->getAdvertisingText($activeCampaign);

        $status = $activeCampaign['status'] === "ACTIVATED" ? 'publish' : 'trash';

        $args = [
            'posts_per_page' => 1,
            'post_type' => 'property',
            'meta_key' => 'utogiInternalId',
            'meta_value' => $activeCampaign['id'],
            'post_status' => ['publish'],
        ];

        // Run the query
        $existingProperty = new WP_Query( $args );
        if ( $existingProperty->have_posts()) {
            while ( $existingProperty->have_posts() ) {
                $existingProperty->the_post();
                $id = $existingProperty->post->ID;
                $existingProperty->post->post_title = $title;
                $existingProperty->post->post_content = $description;
                $existingProperty->post->post_name = $webAddress;
                wp_update_post($existingProperty->post);
            }
            return $id;
        }

        return wp_insert_post(array(
            'post_title'=> $title ?? $activeCampaign['address'],
            'post_type'=>'property',
            'post_content'=> $description?? $activeCampaign['address'],
            'post_status' 	=> $status,
            "post_name" => $webAddress,
        ));
    }

    /**
     * @param $agent
     * @return int
     */
    private function getAgentId($agent)
    {
        $args = [
            'posts_per_page' => -1,
            'post_type' => 'agent',
            'meta_query' => [
                'relation' => 'AND',
                [
                    'key' => 'email',
                    'value' => $agent,
                    'compare' => 'LIKE'
                ],
            ]
        ];
        [$data] = query_posts($args);
        return $data->ID;
    }

}
