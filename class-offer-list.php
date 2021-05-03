<?php

if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class AffiliatesOne_Offers_List extends WP_List_Table {
    function __construct() {
        $this->handle_filters();
        parent::__construct();
    }

    function handle_filters() {
        if ( !wp_verify_nonce( $_POST['affiliate_one_overview'], '_affiliate_one_overview') ) {
            return;
        }
        
        if ( $_POST['filter_action'] != 'Filter') return;
        
        $permalink = add_query_arg( 'category-group', $_POST['category-group']);
        if ( empty($_POST['category-group'])) {
            $permalink = remove_query_arg('category-group', $permalink);
        }

        if ( !empty($_POST['search-offer'])) {
            $permalink = add_query_arg('search-offer', $_POST['search-offer'], $permalink);
        } else {
            $permalink = remove_query_arg('search-offer', $permalink);
        }

        wp_safe_redirect( $permalink);
        exit;
    }

    public function get_bulk_actions() {
        $actions = [
            'bulk-publish' => 'Publish'
        ];

        return $actions;
    }

    public function process_bulk_action() {
        if ( !wp_verify_nonce( $_POST['affiliate_one_overview'], '_affiliate_one_overview') ) {
            return;
        }

        $action = $_POST['action'];

        if ( 'bulk-publish' !== $action ) {
            return;
        }

        $offers = is_array($_SESSION['affiliates_one_offers']) ? $_SESSION['affiliates_one_offers'] : [];       
        
        $offer_ids = is_array($_POST['offers']) ? $_POST['offers'] : [];
        while ($offer_id = current($offer_ids)) {
            next($offer_ids);

            $offer_items = array_filter($offers, function($item) use($offer_id)  {
                return $item->id == $offer_id;
            });

            affiliates_one_save_post(current($offer_items));
        }
    }

    function handle_action() {
        if (!wp_verify_nonce( $_REQUEST['_nonce'], 'publish-offer')) {
            return;
        }

        $get_offers = (array) $_SESSION['affiliates_one_offers'];

        $offer_items = array_filter($get_offers, function($item)  {
            return $item->id == $_REQUEST['publish-offer'];
        });


        $offer = current($offer_items);
        if ( !$offer ) return;
        
        $post_id = affiliates_one_save_post($offer);

        $permalink = remove_query_arg( ['publish-offer', '_nonce']);
        exit(wp_safe_redirect( $permalink ));        
    }

    function extra_tablenav( $which ) {
        global $cat_id;
 
        if ( 'top' !== $which ) {
            return;
        }

        $category_group = absint( $_GET['category-group']) ; ?>
        <div class="alignleft actions">
        <input style="float:left" type="text" name="search-offer" value="<?php echo $_GET['search-offer'] ?>">
        <select name="category-group">
            <option value="">Select Category Group</option>
            <?php
            foreach (get_affiliatesone_category_groups() as $key => $category) {
                printf('<option value="%s" %s>%s</option>', $key, selected( $category_group, $key), $category);
            } ?>
        </select>
        
        <?php
            submit_button( __( 'Filter' ), '', 'filter_action', false, array( 'id' => 'post-query-submit' ) );
            ?>
        </div>
        <?php
    }

    function get_offers($current_page = 1, $per_page = 15) {
        global $wpdb;

        $query_arg = [
            //'locale' => 'zh-TW',
            'page' => $current_page,
            'per_page' => $per_page,
            'api_key' => 'ac4b122e8941812664950edb11ca1854'
        ];

        if ( !empty( $_GET['search-offer'] ) ) {
            $query_arg['ids'] = $_GET['search-offer'];
        }

        if ( absint( $_GET['category-group'] ) > 0 ) {
            $query_arg['category_group_ids'] = $_GET['category-group'];
        }

        $offers = $_SESSION['affiliates_one_offers'];

        //if prev session and current session is same then return value from session
        if ( $_SESSION['affiliatesone_query_args'] !== $query_arg || empty($_SESSION['affiliates_one_offers'])) {
            $response = @file_get_contents(add_query_arg($query_arg, 'https://api.affiliates.com.tw/api/v1/affiliates/offers.json'));
            $result = json_decode($response);
            $_SESSION['affiliates_one_page'] = $result->page;
            $_SESSION['affiliates_one_per_page'] = $result->per_page;
            $_SESSION['affiliates_one_total'] = $result->data_count_total;

            $offers = $result->data->offers;
        }

        //set session, if session query not change get offer from session for loading quickly
        $_SESSION['affiliatesone_query_args'] = $query_arg;

        if ( !is_array($offers) ) {
            $offers = [];
        }

        array_walk($offers, function(&$offer) use($wpdb) {
            $offer->name_id = sprintf("(%d) %s", $offer->id, $offer->name);

            $offer->categories = implode(', ', (array) $offer->categories);

            $country_flag = array_map(function($country) {
                $flag = str_replace(' ', '-', $country);
                if ( file_exists(AO_DIR . 'flags/'. $flag . '.png') ) {
                    return sprintf('<img class="flag" src="%1$sflags/%2$s.png" alt="%3$s" title="%3$s" />', AO_URI, $flag, $country);
                }                
                return $country;                
            }, $offer->countries);
            
            $offer->flags = implode(' ', $country_flag);

            $exist = $wpdb->get_var("SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'affiliates_one_offer' AND meta_value = '$offer->id'");
            $offer->published = (boolean) $exist;
        });

        $_SESSION['affiliates_one_offers'] = $offers;        
        
        return $offers;
    }

    
    public function prepare_items() {
        $this->process_bulk_action();
        $this->handle_action();
        $columns = $this->get_columns();

        $per_page = 15;
        $current_page = $this->get_pagenum();

        $this->items = $this->get_offers($current_page, $per_page);
    
        $this->set_pagination_args( array('total_items' => absint( $_SESSION['affiliates_one_total'] ), 'per_page'    => $per_page) );

        $this->_column_headers = array($columns);
        
    }
    
    public function get_columns() {
        $columns = array(
            'cb'            => '<input type="checkbox" />',
            'name_id'       => __('Name', 'affiliates-one'),
            'thumbnail'     => __('Thumbnail', 'affiliates-one'),
            'categories'    => __('Categories', 'affiliates-one'),
            'flags'         => __('Countries', 'affiliates-one'),
            'status'        => __('Status', 'affiliates-one'),
            'published'     => __('Publish Status', 'affiliates-one')
        );

        return $columns;
    }

    
    public function column_default( $item, $column_name ) {
        switch( $column_name ) {
            case 'name_id':
            case 'categories':
            case 'flags':
            case 'status':
            case 'published':
                return $item->$column_name;

            default:
                return print_r( $item, true ) ;
        }
    }

    function column_cb( $item ) {
        return sprintf('<input type="checkbox" name="offers[]" value="%s" />', $item->id);
    }

    function column_thumbnail( $item ) {
        if ( $item->brand_image_url ) {
            return '<img src="'.$item->brand_image_url.'" />';
        }
        
    }

    function column_published( $offer ) {
        if ( $offer->published ) {
            return _e('Published', 'affiliates-one');
        }

        $permalink = add_query_arg([
            'publish-offer' => $offer->id,
            '_nonce' => wp_create_nonce( 'publish-offer' )
        ]);

        printf('<a class="button button-primary" href="%s">%s</a>', $permalink, __('Publish', 'affiliates-one') );
    }
}



class AffiliatesOne_Offer_page {
    var $table_offer;

    function __construct() {
        if ( !session_id() ) {
            session_start();
        }

        add_action( 'admin_menu',             array($this, 'register_admin_menu_page' ));         
    }

    function register_admin_menu_page() {
        $this->table_offer = new AffiliatesOne_Offers_List();
        $this->table_offer->prepare_items();

        $hook = add_menu_page(
            __( 'Affiliates One', 'affiliates-one' ),
            __( 'Affiliates One', 'affiliates-one' ),
            'manage_options',
            'affiliates-one-offers',
            array($this, 'affiliates_one_menu_callback'),
           'dashicons-awards',
        );
    }


    function affiliates_one_menu_callback() {?>

        <div class="wrap affiliates-one-wrap">
            <div id="icon-users" class="icon32"></div>
            <h2>Offers Overview</h2>
            <?php ///$this->table_offer->prepare_items(); ?>
            <form method="post">
                <?php wp_nonce_field('_affiliate_one_overview', 'affiliate_one_overview'); ?>
                <?php $this->table_offer->display(); ?>
            </form>
        </div>
        <?php
    }

}
