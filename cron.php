<?php

class AffiliatesOne_Cron {

    function __construct() {
        add_action( 'affiliates_one_get_offers_hook', [$this, 'get_offers_hook_callback']);
    }

    function get_offers_hook_callback() {
        $result = get_affiliates_one_offers(['per_page' => 1]);
        if ( !is_object($result) ) {
            return;
        }
        
        $per_page = 12;
        $pages = ceil($result->data_length / $per_page);

        for ($page_no=1; $page_no <= $pages; $page_no++) {
            var_dump($page_no);
            continue;

            $result = get_affiliates_one_offers(['page' => $page_no, 'per_page' => $per_page]);
            if ( !is_array($result->data->offers)) {
                continue;
            }

            while ($offer = current($result->data->offers)) {
                next($result->data->offers);
                affiliates_one_save_post_offer($offer);
            }
        }
    }
    
}


add_action( 'init', function(){
    if ( !isset($_GET['cron']) ) return;
    
    do_action( 'affiliates_one_get_offers_hook');
    exit;
});


