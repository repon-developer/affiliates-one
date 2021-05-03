<?php

class AffiliatesOne_Shortcodes {
    function __construct() {
        add_shortcode( 'affiliate_one', [$this, 'affiliate_one_shorcode']);
        add_shortcode( 'discount_info', [$this, 'affiliate_one_discount_info_shorcode']);

        add_shortcode( 'discount_info_title', [$this, 'discount_info_title']);
    }

    function affiliate_one_shorcode($atts, $content = null) {
        $atts = shortcode_atts([
            'field' => ''
        ], $atts, 'affiliate_one');

        extract($atts);

        if ( empty($field) ) return;

        if ( method_exists($this, $field)) {
            return $this->$field($atts, $content);
        }

        $field_data = get_post_meta( get_the_id(), $field, true);

        return $field_data;
    }

    function shortlink($atts, $content = null) {
        $atts = shortcode_atts([
            'class' => '',
            'permalink' => 'yes',
            'rel' => 'nofollow noreferrer noopener'
        ], $atts);

        extract($atts);

        $short_link_id = get_post_meta( get_the_id(), 'tracking_short_link', true);

        $post = get_post( $short_link_id );

        if ( !is_a($post, 'WP_Post') ) {
            return '';
        }

        if ( $permalink == 'yes') {
            return get_permalink( $post->ID);
        }

        $permalink = get_permalink( $post->ID);

        $atts['class'] = $class;
        $atts['rel'] = $rel;


        ob_start(); ?>
        <a href="<?php echo $permalink; ?>" <?php implode(' ', $atts) ?>><?php echo $content; ?></a>
        <?php return ob_get_clean();
    }

    function affiliate_one_discount_info_shorcode($atts, $content = null) {
        

        $atts = shortcode_atts( [
            
        ], $atts, 'discount_info');

        
        $discount_info = get_post_meta( get_the_id(), 'discount_info', true);
        if ( !is_array($discount_info)) {
            return '';
        }

        ob_start();

        foreach ($discount_info as $discount) {
            wp_cache_set('discount_info', $discount);
            echo do_shortcode( $content );
        }

        return ob_get_clean();
    }

    function discount_info_title($atts, $content = null) {
        $discount_info = wp_cache_get('discount_info');

        if ( !is_object($discount_info) ) {
            return '';
        }

        ob_start();
        if ( !empty($discount_info->title)) {
            echo $discount_info->title;
        }

        var_dump($discount_info);

        return ob_get_clean();
    }

}

