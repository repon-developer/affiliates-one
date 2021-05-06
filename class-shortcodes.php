<?php

class AffiliatesOne_Shortcodes {
    function __construct() {
        add_shortcode( 'affiliate_one', [$this, 'affiliate_one_shortcode']);
        add_shortcode( 'affiliate_one_shortlink', [$this, 'affiliate_one_shortlink']);

        add_shortcode( 'discount_info', [$this, 'discount_info_shortcode']);
        add_shortcode( 'discount_slug', [$this, 'discount_info_slug']);
        add_shortcode( 'discount_tracking_url', [$this, 'discount_slug_tracking_url']);
    }

    function affiliate_one_shortcode($atts, $content = null) {
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

    function name($atts, $content = null) {
        return get_the_title( get_the_id() );
    }

    function image($atts, $content = null) {
        $atts = shortcode_atts( ['size' => ''], $atts);

        if ( has_post_thumbnail( get_the_id() )) {
            return get_the_post_thumbnail( get_the_ID(), $size );
        }
    }

    function affiliate_one_shortlink($atts, $content = null) {
        $atts = shortcode_atts([
            'class' => '',
            'rel' => 'nofollow noreferrer noopener'
        ], $atts);

        extract($atts);

        $short_link_id = get_post_meta( get_the_id(), 'tracking_short_link', true);
        if ( !$short_link_id) return '';

        $post = get_post( $short_link_id );

        if ( !is_a($post, 'WP_Post') ) {
            return '';
        }

        if ( $content == null) {
            return get_permalink( $post->ID);
        }

        $permalink = get_permalink( $post->ID);

        ob_start(); ?>
        <a href="<?php echo $permalink; ?>" class="<?php echo $class ?>" rel="<?php echo $rel ?>"><?php echo do_shortcode( $content ); ?></a>
        <?php return ob_get_clean();
    }

    function discount_info_shortcode($atts, $content = null) {       
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

    function discount_info_slug($atts, $content = null) {
        $atts = shortcode_atts( [
            'field' => false
        ], $atts, 'discount_slug');

        if (empty($atts['field'])) {
            return;
        }

        
        $discount_info = wp_cache_get('discount_info');
        $method = 'discount_slug_' . $atts['field'];

        if ( !is_object($discount_info) ) {
            return null;
        }

        $field = $atts['field'];
        
        ob_start();
        if ( method_exists($this, $method)) {
            $this->$method($atts, $content, $discount_info);

        } else if ( !empty($discount_info->$field) ) {
            echo $discount_info->$field;
        }

        return ob_get_clean();
    }

    function discount_slug_title($atts, $content, $info) {
        if ( empty($info->title)) return '';        
        echo $info->title;
    }

    function discount_slug_content($atts, $content, $info) {
        if ( !empty($info->content)) {
            echo $info->content;
        }
    }

    function discount_slug_image($atts, $content, $info) {
        if ( !empty($info->image_url)) {
            echo '<img src="'.$info->image_url.'"/>';
        }
    }

    function discount_slug_start_date($atts, $content, $info) {
        if ( !empty($info->active_date_start)) {
            echo date(get_option( 'date_format'), strtotime($info->active_date_start));
        }
    }

    function discount_slug_end_date($atts, $content, $info) {
        if ( !empty($info->active_date_end)) {
            echo date(get_option( 'date_format'), strtotime($info->active_date_end));
        }
    }

    function discount_slug_tracking_url($atts, $content = null) {
        $info = wp_cache_get('discount_info');

        if ( !is_object($info) || empty($info->tracking_url)) return '';


        global $wpdb;

        $short_link_id = $wpdb->get_var("SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'offer_url' AND meta_value = '$info->tracking_url'");
        if ( !$short_link_id) {
            $short_link_id = get_post_meta( get_the_id(), 'tracking_short_link', true);
        }

        $link_post = get_post($short_link_id);
        if ( !is_a($link_post, 'WP_Post') ) {
            return '';
        }

        $offer_permalink = get_permalink( $link_post);
        return sprintf('<a href="%s">%s</a>', $offer_permalink, do_shortcode( $content ));
    }
}

