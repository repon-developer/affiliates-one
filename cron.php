<?php

class AffiliatesOne_Cron {

    function __construct() {
        add_action('add_option_affiliates_one_autopost', [$this, 'handle_affiliates_one_autopost_action'], 12, 2);
        add_action('update_option_affiliates_one_autopost', [$this, 'handle_affiliates_one_autopost_action'], 12, 2);
        add_action( 'affiliates_one_get_offers_hook', [$this, 'get_offers_hook_callback']);
    }

    function handle_affiliates_one_autopost_action($old_value, $autopost) {
        if ($autopost) {
            if (! wp_next_scheduled ( 'affiliates_one_get_offers_hook' )) {
                wp_schedule_event( time(), 'affiliates_one', 'affiliates_one_get_offers_hook' );
            }

            return;
        }

        wp_clear_scheduled_hook('affiliates_one_get_offers_hook');
    }

    function get_offers_hook_callback() {
        $result = get_affiliates_one_offers(['per_page' => 1]);
        if ( !is_object($result) ) {
            return;
        }

        $pages = ceil($result->data_length / AFFILIATES_ONE_PER_PAGE);
        $pages = 1;

        for ($page_no=1; $page_no <= $pages; $page_no++) {
            $result = get_affiliates_one_offers(['page' => $page_no, 'per_page' => AFFILIATES_ONE_PER_PAGE]);

            if ( !is_array($result->data->offers)) {
                continue;
            }
            
            
            while ($offer = current($result->data->offers)) {
                next($result->data->offers);
                update_option( 'affiliates_one_offer_' . $offer->id, $offer);
                //AffiliatesOne_Query::save_creatives($offer->id);
            }
        }
    }   
}

add_action( 'init', function(){
    if ( !isset($_GET['cron']) ) return;
    do_action( 'affiliates_one_get_offers_hook');
    exit;
});

add_action( 'init', function(){
    if ( !isset($_GET['cron_s']) ) return;

    
    global $wpdb;

    $result = $wpdb->get_results("SELECT * FROM $wpdb->options WHERE `option_name` LIKE '%affiliates_one_offer_%'");
    

    var_dump('<pre>', $result);



    exit;
});