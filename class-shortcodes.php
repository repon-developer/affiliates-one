<?php


class AffiliatesOne_Shortcodes {
    function __construct() {
        add_shortcode( 'affiliate_one', [$this, 'affiliate_one_shorcode']);
    }

    function affiliate_one_shorcode($atts, $content = null) {
        $atts = shortcode_atts([
            'field' => ''
        ], $atts, 'affiliate_one');

        extract($atts);

        $field_data = get_post_meta( get_the_id(), $field, true);

        return $field_data;
    }    

}


