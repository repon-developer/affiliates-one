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
        $get_last_page = get_option('affiliates_one_last_page', 0);
        $current_page = absint( $get_last_page ) + 1;

        $result = get_affiliates_one_offers(['page' => $current_page, 'per_page' => AFFILIATES_ONE_PER_PAGE]);
        if ( !is_array($result->data->offers)) {
            return;
        }

        if ( ( $current_page * AFFILIATES_ONE_PER_PAGE ) > $result->data_length ) {
            $current_page = 0;
        }

        update_option( 'affiliates_one_last_page', $current_page);
        
        while ($offer = current($result->data->offers)) {
            next($result->data->offers);
            AffiliatesOne_Query::save_creatives($offer->id);
        }
    }   
}

add_action( 'init', function(){
    if ( !isset($_GET['cron']) ) return;
    do_action( 'affiliates_one_get_offers_hook');
    exit;
});

add_action( 'initddd', function(){
    if ( !isset($_GET['cron_s']) ) return;

    
    global $wpdb;

    $result = $wpdb->get_results("SELECT * FROM $wpdb->options WHERE `option_name` LIKE '%ffiliates_one_offer_%'");
    

    var_dump('<pre>', $result);



    exit;
});