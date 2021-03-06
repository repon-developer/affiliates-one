<?php 

function get_affiliatesone_category_groups() {
    return [
        '1001' => 'Accessories',
        '1002' => 'Adult',
        '1003' => 'APPs',
        '1004' => 'Art & Music',
        '1005' => 'Beauty',
        '1006' => 'Books/Media',
        '1007' => 'Business',
        '1008' => 'Careers',
        '1009' => 'Clothing/Apparel',
        '1010' => 'Computer & Electronics',
        '1011' => 'Department Stores/Malls',
        '1012' => 'Education',
        '1013' => 'Family',
        '1014' => 'Financial Services',
        '1015' => 'Food & Drinks',
        '1016' => 'Game & Toys',
        '1017' => 'Gifts & Flowers',
        '1018' => 'Health & Wellness',
        '1019' => 'Home & Garden',
        '1021' => 'Non-Profit',
        '1022' => 'Online Services',
        '1023' => 'Professional Services',
        '1024' => 'Recreation & Leisure',
        '1025' => 'Sports & Fitness',
        '1026' => 'Transportation',
        '1027' => 'Telecommunications',
        '1028' => 'Travel',
        '1029' => 'Miscellaneous',
        '1030' => 'Pets & Aquarium	',
    ];
}

function get_affiliates_one_offers($query_args = []) {
    $api_key = get_option( 'affiliates_one_api_key');
    
    $query_args = wp_parse_args( $query_args, [
        'locale' =>'zh-TW', 
        'api_key' => $api_key
    ]);
    

    $response = @file_get_contents(add_query_arg($query_args, 'https://api.affiliates.com.tw/api/v1/affiliates/offers.json'));
    return json_decode($response);
}

function affiliates_one_create_short_link($tracking_url, $post_slug) {
    if (!filter_var($tracking_url, FILTER_VALIDATE_URL)) {
        return false;
    }

    global $wpdb;

    $short_link_id = $wpdb->get_var("SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'offer_url' AND meta_value = '$tracking_url'");

    $short_link_id = wp_insert_post([
        'ID' => $short_link_id, 
        'post_title' => $post_slug, 
        'post_type' => 'short_link', 
        'post_status' => 'publish'
    ]);
    
    if ( $short_link_id ) {
        update_post_meta( $short_link_id, 'offer_url', $tracking_url);
        return $short_link_id;
    }

    return false;
}

function affiliates_one_save_post_offer($offer) {
    if ( !is_object($offer) ) return false; 
    affiliates_one_logs(sprintf("Saving offer - %s (%s)", $offer->name, $offer->id));

    global $wpdb;

    $product_description = $offer->product_description;
    $product_description .= $offer->brand_background;

    $post_id = $wpdb->get_var("SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'affiliates_one_offer' AND meta_value = '$offer->id'");
    
    $post_args = array(
        'ID' => $post_id,
        'post_title' => $offer->name,
        'post_content' => $product_description,
        'post_excerpt' => $offer->product_description,
        'post_status' => 'publish'
    );
    
    $get_template = get_post(get_option('affiliates_one_template'));

    if ( is_a($get_template, 'WP_Post') ) {
        $post_args['post_title'] = str_replace('[offer_title]', $offer->name, $get_template->post_title );
        $post_args['post_content'] = $get_template->post_content;
    }
    
    if ( $post_id ) {
        unset($post_args['post_content']);
    }

    if ( empty($post_args['post_content']) ) {
        $post_args['post_content'] = '';
    }

    $post_id = wp_insert_post($post_args);    
    if ( !$post_id ) return;

    require_once(ABSPATH . 'wp-admin/includes/taxonomy.php'); 

    if ($category_group = $_SESSION['category-group']) {
        $category = get_affiliatesone_category_groups()[$category_group];
        $term = wp_create_term( $category, 'category');
        wp_set_post_categories($post_id, $term);
    }

    wp_set_object_terms($post_id, $offer->categories, 'category', true );
    wp_remove_object_terms($post_id, 'uncategorized', 'category');

    update_post_meta( $post_id, 'affiliates_one_offer', $offer->id);
    update_post_meta( $post_id, 'offer_name', $offer->name);
    update_post_meta( $post_id, 'short_description', $offer->short_description);
    update_post_meta( $post_id, 'brand_background', $offer->brand_background);
    update_post_meta( $post_id, 'product_description', $offer->product_description);
    
    if ( $offer->default_tracking_url ) {
        affiliates_one_logs(sprintf("Add tracking URL %s for this offer %s (%s)", $offer->default_tracking_url, $offer->name, $offer->id));

        $short_link_id = affiliates_one_create_short_link($offer->default_tracking_url, $offer->id);        
        if ( $short_link_id ) {
            update_post_meta( $post_id, 'tracking_short_link', $short_link_id);
        }
    }
    
    $creatives = affiliatesone_get_creatives($offer->id);
    if ( count($creatives) > 0 ) {
        update_post_meta( $post_id, 'discount_info', $creatives);
    } else {
        affiliates_one_logs(sprintf("Creative data is not available for offer %s (%s)", $offer->name, $offer->id));
    }

    if ( !has_post_thumbnail( $post_id ) ) {
        $attach_id = affiliatesone_image_upload($offer->brand_image_url, $offer);
        
        if ( $attach_id ) {
            affiliates_one_logs(sprintf("Add featured image for this offer %s (%s)", $offer->name, $offer->id));
            set_post_thumbnail( $post_id, $attach_id );
        }
    }

    return $post_id;
}

function affiliatesone_image_upload($image_url, $offer = null) {
    if( !wp_http_validate_url($image_url) ) {
        affiliates_one_logs(sprintf("Image URL is not valid - ", $offer->name, $offer->id));
        return false;
    }
    
    $image_data = file_get_contents( $image_url );
    $filename = basename( $image_url );
    
    $upload_dir = wp_upload_dir();
    if ( wp_mkdir_p( $upload_dir['path'] ) ) {
        $file = $upload_dir['path'] . '/' . $filename;
    } else {
        $file = $upload_dir['basedir'] . '/' . $filename;
    }

    file_put_contents( $file, $image_data );

    $wp_filetype = wp_check_filetype( $filename, null );

    $attachment = array(
        'post_mime_type' => $wp_filetype['type'],
        'post_title' => sanitize_file_name( $filename ),
        'post_content' => '',
        'post_status' => 'inherit'
    );

    $attach_id = wp_insert_attachment( $attachment, $file );
    require_once( ABSPATH . 'wp-admin/includes/image.php' );
    $attach_data = wp_generate_attachment_metadata( $attach_id, $file );
    wp_update_attachment_metadata( $attach_id, $attach_data );

    return $attach_id;
}

function affiliatesone_get_creatives($offer_id) {
    $api_key = get_option( 'affiliates_one_api_key');

    $query_arg = [
        'locale' => 'zh-TW',        
        'offer_id' => $offer_id,
        'creative_type' => 'feed',
        'api_key' => $api_key
    ];
    
    $response = @file_get_contents(add_query_arg($query_arg, 'https://api.affiliates.com.tw/api/v1/affiliates/creatives.json'));

    $result = json_decode($response);
    $creatives = is_array($result->data->creatives) ? $result->data->creatives : [];

    foreach ($creatives as $creative) {
        affiliates_one_create_short_link($creative->tracking_url, $creative->id);
    }

    return $creatives;
}

function affiliates_one_logs($line, $end = false) {
    $file = AO_DIR . 'affliates.log';

    $logs = array_filter(array_map("trim", file($file)));
    array_push($logs, $line);

    $logs = array_slice($logs, -50);    
    
    
    $fp = fopen($file, 'w');
    fwrite($fp, implode("\r\n", $logs)); 
    fclose($fp);
}
