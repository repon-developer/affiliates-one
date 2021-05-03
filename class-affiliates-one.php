<?php

class AffiliatesOne {
    static $instance;

    function __construct() {
        $this->load();

        add_action( 'admin_enqueue_scripts', array($this, 'admin_enqueue_scripts' ));
        add_action( 'admin_menu', [$this, 'register_admin_menu_page']);
    }

    public static function get_instance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    function load() {
        require_once AO_DIR . 'class-shortcodes.php';
        new AffiliatesOne_Shortcodes();

        require_once AO_DIR . 'class-offer-list.php';
        new AffiliatesOne_Offer_page(); 
    }

    function admin_enqueue_scripts($hook) {
        if ( 'toplevel_page_affiliates-one-offers' == $hook || 'affiliates-one_page_affiliates-one-logs' == $hook ) {
            wp_enqueue_style( 'affiliates-one', AO_URI . 'assets/affiliates-one.css', false, '1.0.1' );
        }
    }

    function register_admin_menu_page() {
        add_submenu_page(
            'affiliates-one-offers', 
            __('Affiliates one logs', 'affiliates-one'), 
            __('Logs', 'affiliates-one'), 
            'manage_options', 
            'affiliates-one-logs', 
            [$this, 'affiliates_one_log_page']
        );
    }

    function affiliates_one_log_page() {
        echo '<div class="wrap"><div id="icon-tools" class="icon32"></div>';
            echo '<h2>' . __('Affiliates one logs', 'affiliates-one') . '</h2>';

            echo '<div class="affiliates-one-logs">';
            $logs = fopen( AO_DIR . 'logs.txt','r');
            while ($line = fgets($logs)) {
                echo($line) . '<br>' . "\r\n <hr>";
            }
            fclose($logs);
            echo '</div>';

        echo '</div>';
    }
}