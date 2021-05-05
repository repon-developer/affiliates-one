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
            echo '<h2>' . __('Affiliates one logs', 'affiliates-one') . '</h2>';

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
            'affiliate_one_template',
            __( 'Simple Content Template', 'affiliates-one' ),
            array($this, 'simple_content_template_dropdown'),
            'affiliate_one_settings',
            'affiliates_one_settings_section'
        );

        register_setting( 'affiliate_one_settings', 'affiliate_one_template' );        
    }

    function simple_content_template_dropdown() {
        $template_id = get_option( 'affiliate_one_template');

        $get_templates = get_posts(['post_type' => 'act_template']);

        echo '<select name="affiliate_one_template">';
        echo '<option value="">'.__('Select Template', 'affiliates-one').'</option>';
        foreach ($get_templates as $template) {
            printf('<option value="%d" %s>%s</option>', $template->ID, selected($template_id, $template->ID, false),  $template->post_title);
        }
        echo '</select>';        
    }


}