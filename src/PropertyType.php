<?php

namespace UtogiMarketing;

class PropertyType
{
   public function initPropertyPostType() {
        $labels = [
            'name'               => _x( 'Utogi Properties', 'post type general name' ),
            'singular_name'      => _x( 'Utogi Property', 'post type singular name' ),
            'edit_item'          => __( 'Edit Property' ),
            'new_item'           => __( 'New Property' ),
            'all_items'          => __( 'All Property' ),
            'view_item'          => __( 'View Property' ),
            'search_items'       => __( 'Search Property' ),
            'not_found'          => __( 'No property found' ),
            'not_found_in_trash' => __( 'No property found in the Trash' ),
            'parent_item_colon'  => '',
            'menu_name'          => 'Utogi Properties'
        ];
        $args = [
            'labels'        => $labels,
            'description'   => 'Utogi Properties',
            'public'        => true,
            'menu_position' => 5,
            'supports'      => ['title', 'editor', 'thumbnail', 'excerpt'],
            'has_archive'   => true,
            'menu_icon'          => 'dashicons-admin-multisite',
            /* 'capabilities'  => [
                 'create_posts' => 'do_not_allow',
             ],*/
        ];
        register_post_type( 'utogi_properties', $args );
    }

   public function initCustomField() {
       add_meta_box( 'utogi_property_details', __( 'Property Details', 'utogi_property_details' ), [$this, 'customFieldView'], 'utogi_properties' );
   }

   public function customFieldView($post) {
       include plugin_dir_path( __FILE__ ) . 'View/property-details.php';
   }
}