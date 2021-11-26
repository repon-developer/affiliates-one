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
        
        $per_page = 12;
        $pages = ceil($result->data_length / $per_page);

        for ($page_no=1; $page_no <= $pages; $page_no++) {
            $result = get_affiliates_one_offers(['page' => $page_no, 'per_page' => $per_page]);
            if ( !is_array($result->data->offers)) {
                continue;
            }

            set_transient( 'affiliates_one_offers', $result->data->offers, AO_TRANSIENT_TIME);

            while ($offer = current($result->data->offers)) {
                next($result->data->offers);
                AffiliatesOne_Query::save_creatives($offer->id);
            }
        }
    }   
}

// add_action( 'init', function(){
//     if ( !isset($_GET['cron']) ) return;

//     do_action( 'affiliates_one_get_offers_hook');
//     exit;
// });


add_action( 'init', function(){
    if ( !isset($_GET['cron']) ) return;

    
    $creatives = affiliatesone_get_creatives(3080);

    var_dump($creatives);
    

    exit;
});