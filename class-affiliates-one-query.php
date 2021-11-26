<?php

class AffiliatesOne_Query {

    public static function get_offer($offer_id) {
        $get_offers = (array) $_SESSION['affiliates_one_offers'];

        $current_offer = array_filter($get_offers, function($item) use($offer_id) {
            return $item->id == $offer_id;
        });

        if ( !$current_offer ) {
            return false;
        }

        $current_offer = current($current_offer);

        return (object) array_merge((array)$current_offer, ['creatives' => affiliatesone_get_creatives($current_offer->id)]);
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

        // $post_id = affiliates_one_save_post_offer($offer);


        // affiliates_one_logs("Updating permalink for short link");
        // flush_rewrite_rules();

        return $current_offer;
    }
}
