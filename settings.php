<?php

class AffiliatesOne_Settings  {
    function __construct() {
        add_action( 'admin_menu', [$this, 'register_admin_menu_settings_page'], 20);

        add_action( 'admin_init', [$this, 'affiliate_one_register_settings'] );
    }

    function register_admin_menu_settings_page() {
        add_submenu_page(
            'affiliates-one-offers', 
            __('Affiliates one settings', 'affiliates-one'), 
            __('Settings', 'affiliates-one'), 
            'manage_options', 
            'affiliates-one-settings', 
            [$this, 'affiliates_one_settings']
        );
    }

    function affiliates_one_settings() {
        echo '<div class="wrap"><div id="icon-tools" class="icon32"></div>';
            echo '<h2>' . __('Affiliates one settings', 'affiliates-one') . '</h2>';

            echo '<form method="post" action="options.php">';
                settings_fields( 'affiliate_one_settings' );
                do_settings_sections( 'affiliate_one_settings' );
                submit_button();
            echo '</form>';
        echo '</div>';
    }

    function affiliate_one_register_settings() {

        add_settings_section(
            'affiliates_one_settings_section',
            __( 'General Settings', 'affiliates-one' ), 
            '', //no callback
            'affiliate_one_settings'
        );

        add_settings_field(
            'affiliates_one_api_key',
            __( 'API key', 'affiliates-one' ),
            array($this, 'affiliates_one_api_key_field'),
            'affiliate_one_settings',
            'affiliates_one_settings_section',
            array('label_for' => 'affiliates_one_api_key')
        );
        register_setting( 'affiliate_one_settings', 'affiliates_one_api_key' ); 

        add_settings_field(
            'affiliates_one_interval',
            __( 'Interval', 'affiliates-one' ),
            array($this, 'affiliates_one_interval_field'),
            'affiliate_one_settings',
            'affiliates_one_settings_section',
            array('label_for' => 'affiliates_one_interval')
        );
        register_setting( 'affiliate_one_settings', 'affiliates_one_interval' ); 

        
        add_settings_field(
            'affiliates_one_template',
            __( 'Simple Content Template', 'affiliates-one' ),
            array($this, 'simple_content_template_dropdown'),
            'affiliate_one_settings',
            'affiliates_one_settings_section',
            array('label_for' => 'affiliates_one_template')
        );

        register_setting( 'affiliate_one_settings', 'affiliates_one_template' );
        
        
        // add_settings_field(
        //     'affiliates_one_categories',
        //     __( 'Category Group', 'affiliates-one' ),
        //     array($this, 'affiliates_one_categories_field'),
        //     'affiliate_one_settings',
        //     'affiliates_one_settings_section'
        // );

        // register_setting( 'affiliate_one_settings', 'affiliates_one_categories' );
    }

    function affiliates_one_api_key_field() {
        $api_key = get_option( 'affiliates_one_api_key');
        printf('<input type="text" id="affiliates_one_api_key" name="affiliates_one_api_key" value="%s" />', $api_key);
    }

    function affiliates_one_interval_field() {
        $interval = get_option( 'affiliates_one_interval');
        printf('<input type="text" id="affiliates_one_interval" name="affiliates_one_interval" value="%s" /> %s', $interval, __('Days', 'affiliates-one'));
        echo '<p>' . __('Interval for getting offer from Affiliates One.', 'affiliates-one') .'</p>';
    }

    function simple_content_template_dropdown() {
        $template_id = get_option( 'affiliates_one_template');

        $get_templates = get_posts(['post_type' => 'act_template']);

        echo '<select id="affiliates_one_template" name="affiliates_one_template">';
        echo '<option value="">'.__('Select Template', 'affiliates-one').'</option>';
        foreach ($get_templates as $template) {
            printf('<option value="%d" %s>%s</option>', $template->ID, selected($template_id, $template->ID, false),  $template->post_title);
        }
        echo '</select>';        
    }

    function affiliates_one_categories_field() {
        $categories = get_affiliatesone_category_groups();

        $values = get_option('affiliates_one_categories');

        echo '<ul class="category-group-list">';
        while ($category = current($categories) ) {
            next($categories);
            $cat_id =  key($categories);
            printf('<li><strong>%s</strong> <input type="text" name="affiliates_one_categories[%d]" value="%s" /></li>', $category, $cat_id, $values[$cat_id]);
        }
        echo '</ul>';
    }


}