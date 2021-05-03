<?php

class AffiliatesOne {
    static $instance;

    function __construct() {
        $this->load();

        add_action( 'admin_enqueue_scripts',             array($this, 'admin_enqueue_scripts' ));
    }

    public static function get_instance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    function load() {
        require_once AO_DIR . 'class-offer-list.php';
        new AffiliatesOne_Offer_page();

        require_once AO_DIR . 'class-shortcodes.php';
        new AffiliatesOne_Shortcodes();
    }

    function admin_enqueue_scripts($hook) {
        if ( 'toplevel_page_affiliates-one-offers' != $hook ) {
            return;
        }

        wp_enqueue_style( 'affiliates-one', AO_URI . 'assets/affiliates-one.css', false, '1.0.1' );
    }


}