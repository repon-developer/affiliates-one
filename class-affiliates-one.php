<?php

class AffiliatesOne {
    static $instance;

    function __construct() {
        
        $this->load();
        
        add_filter( 'cron_schedules', array($this, 'affiliates_one_cron_schedule'));
        add_action( 'admin_enqueue_scripts', array($this, 'admin_enqueue_scripts' ));
        add_action( 'admin_menu', [$this, 'register_admin_menu_page'], 22);

        add_filter('act_load_template', array($this, 'act_load_template'));
    }

    public static function get_instance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    function load() {
        require_once AO_DIR . 'cores.php';
        new AffiliatesOne_Core();

        require_once AO_DIR . 'settings.php';
        new AffiliatesOne_Settings();
        
        require_once AO_DIR . 'cron.php';
        new AffiliatesOne_Cron();

        require_once AO_DIR . 'class-shortcodes.php';
        new AffiliatesOne_Shortcodes();

        require_once AO_DIR . 'class-offer-list.php';
        new AffiliatesOne_Offer_page(); 
    }

    function affiliates_one_cron_schedule( $schedules ) {
        $interval = get_option( 'affiliates_one_interval', 3);
        if ( absint( $interval ) == 0 ) {
            $interval = 0;
        }

        $schedules['affiliates_one'] = array(
            'interval' => DAY_IN_SECONDS * $interval,
            'display' => __( 'Affiliates One' )
        );

        return $schedules;
     }

    function admin_enqueue_scripts($hook) {
        $our_pages = ['toplevel_page_affiliates-one-offers', 'affiliates-one_page_affiliates-one-settings', 'affiliates-one_page_affiliates-one-logs'];
        if ( in_array($hook, $our_pages) ) {
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
            $logs = fopen( AO_DIR . 'affliates.log','r');
            while ($line = fgets($logs)) {
                echo($line) . '<br>' . "\r\n <hr>";
            }
            fclose($logs);
            echo '</div>';

        echo '</div>';
    }

    function act_load_template($temp_post) {
        unset($temp_post['post_title']);
        return $temp_post;
    }
}