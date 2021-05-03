<?php

class AffiliatesOne_Core {
    function __construct() {
        add_action( 'init', [$this, 'offer_url_post_type_taxonomy'] );
        //add_filter( 'post_type_link', [$this, 'offer_link_post_link'], 10, 2 );
    }

    function offer_url_post_type_taxonomy() {
        $args = array(
            'labels'             => array(
                'name'                  => _x( 'Offer Links', 'Post type general name', 'affiliates-one' ),
                'singular_name'         => _x( 'Offer Link', 'Post type singular name', 'affiliates-one' ),
                'menu_name'             => _x( 'Offer Links', 'Admin Menu text', 'affiliates-one' ),
                'name_admin_bar'        => _x( 'Link', 'Add New on Toolbar', 'affiliates-one' ),
                'add_new'               => __( 'Add New', 'affiliates-one' ),
                'add_new_item'          => __( 'Add New Link', 'affiliates-one' ),
                'new_item'              => __( 'New Link', 'affiliates-one' ),
                'edit_item'             => __( 'Edit Link', 'affiliates-one' ),
                'view_item'             => __( 'View Links', 'affiliates-one' ),
                'all_items'             => __( 'All Links', 'affiliates-one' ),
                'search_items'          => __( 'Search Links', 'affiliates-one' ),
                'not_found'             => __( 'No Links found.', 'affiliates-one' ),
                'not_found_in_trash'    => __( 'No Links found in Trash.', 'affiliates-one' ),
            ),
            'public'             => true,
            'show_ui'            => true,
            'query_var'          => true,
            'rewrite'            => array( 'slug' => 'go/%link_type%' ),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => 20,
            'supports'           => array( 'title', 'editor'),
            'taxonomies'         => array( 'offer_type',),
            'show_in_rest'       => true
        );
          
        register_post_type( 'offer_link', $args );

        $labels = array(
            'name'              => _x( 'Offer Types', 'taxonomy general name', 'affiliates-one' ),
            'singular_name'     => _x( 'Offer Type', 'taxonomy singular name', 'affiliates-one' ),
            'search_items'      => __( 'Search Offer Types', 'affiliates-one' ),
            'all_items'         => __( 'All Offer Types', 'affiliates-one' ),
            'parent_item'       => __( 'Parent Type', 'affiliates-one' ),
            'parent_item_colon' => __( 'Parent Type:', 'affiliates-one' ),
            'edit_item'         => __( 'Edit Type', 'affiliates-one' ),
            'update_item'       => __( 'Update Type', 'affiliates-one' ),
            'add_new_item'      => __( 'Add New Type', 'affiliates-one' ),
            'new_item_name'     => __( 'New Type Name', 'affiliates-one' ),
            'menu_name'         => __( 'Offer Type', 'affiliates-one' ),
        );
     
        register_taxonomy( 'offer_type', array( 'offer_link' ), array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
        ) );
    }

    function offer_link_post_link($post_link, $id = 0) {
        $post = get_post($id);
        if ( is_object( $post ) ){
            $terms = wp_get_object_terms( $post->ID, 'offer_type' );
            if( $terms ){
                return str_replace( '%link_type%' , $terms[0]->slug , $post_link );
            } else {
                return str_replace( '/%link_type%' , '' , $post_link );
            }
        }

        return $post_link;  
    }
}