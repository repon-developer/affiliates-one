<?php

class AffiliatesOne_Query {

    public static function get_offer($offer_id) {
        $get_offers = get_transient('affiliates_one_offers');
        if ( !is_array($get_offers) ) {
            return false;
        }

        $current_offer = array_filter($get_offers, function($item) use($offer_id) {
            return $item->id == $offer_id;
        });

        if ( !$current_offer ) {
            return false;
        }

        $current_offer = current($current_offer);

        return (object) array_merge((array)$current_offer, ['creatives' => affiliatesone_get_creatives($current_offer->id)]);
    }

    public static function save_creative($creative) {
        $post_content = '';
        if ( $creative->promo_text_1 ) {
            $post_content .= '<p>' . $creative->promo_text_1 . '</p>';
        }
        
        if ( $creative->promo_text_2 ) {
            $post_content .= '<p>' . $creative->promo_text_2 . '</p>';
        }
        
        global $wpdb;
        $post_id = $wpdb->get_var("SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'affiliates_one_creative_id' AND meta_value = '$creative->id'");
    
        $post_args = array(
            'ID' => $post_id,
            'post_title' => $creative->offer_name,
            'post_content' => $post_content,
            'post_status' => 'publish'
        );

        $get_template = get_post(get_option('affiliates_one_template'));

        if ( is_a($get_template, 'WP_Post') ) {
            $post_args['post_title'] = str_replace('[offer_title]', $creative->offer_name, $get_template->post_title );
            $post_args['post_content'] = $get_template->post_content;
        }
    
        if ( $post_id ) {
            unset($post_args['post_content']);
        }

        if ( empty($post_args['post_content']) ) {
            $post_args['post_content'] = '';
        }

        $post_id = wp_insert_post($post_args); 
        if ( !$post_id ) return false;
        
        update_post_meta( $post_id, 'affiliates_one_creative_id', $creative->id);
        update_post_meta( $post_id, 'promo_text_1', $creative->promo_text_1);
        update_post_meta( $post_id, 'promo_text_2', $creative->promo_text_2);
        update_post_meta( $post_id, 'button_text', $creative->button_text);
        update_post_meta( $post_id, 'coupon_code', $creative->coupon_code);
        update_post_meta( $post_id, 'original_price', $creative->original_price);
        update_post_meta( $post_id, 'discount_price', $creative->discount_price);

        $short_link_id = affiliates_one_create_short_link($creative->tracking_url, $creative->id);
        if ( $short_link_id ) {
            update_post_meta( $post_id, 'creative_short_link', $short_link_id);
        }

        if ( !has_post_thumbnail( $post_id ) ) {
            $attach_id = $wpdb->get_var("SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'creative_image_url' AND meta_value = '$creative->image_url'");

            if ( !$attach_id ) {
                $attach_id = affiliatesone_image_upload($creative->image_url, ['name' => $creative->offer_name, $creative->id]);
            }

            if ( $attach_id ) {
                set_post_thumbnail( $post_id, $attach_id );
                update_post_meta( $attach_id, 'creative_image_url', $creative->image_url);
            }
        }

        return $post_id;
    }

    public static function get_creatives($offer_id) {
        $current_offer = self::get_offer($offer_id);
        return empty($current_offer->creatives) ? [] : $current_offer->creatives;
    }

    public static function has_creative($offer_id) {
        return !empty(self::get_creatives($offer_id));
    }

    public static function save_creatives($offer_id) {
        $current_offer = self::get_offer($offer_id);

        if ( $current_offer === false ) {
            affiliates_one_logs(sprintf("'%s - %d' - This Offer does not exits.", $current_offer->name, $current_offer->id));
            return false;
        }

        if ( empty($current_offer->creatives) ) {
            affiliates_one_logs(sprintf("Creatives does not exits for this offer '%s - %d'.", $current_offer->name, $current_offer->id));
            return false;
        }

        foreach ($current_offer->creatives as $creative) {
            self::save_creative($creative);
        }

        affiliates_one_logs("Updating permalink for short link");
        flush_rewrite_rules();

        return $current_offer;
    }
}
