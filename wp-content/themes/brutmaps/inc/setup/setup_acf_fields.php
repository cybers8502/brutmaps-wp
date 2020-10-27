<?php
if( function_exists('acf_add_local_field_group') ):
    acf_add_local_field_group(array (
        'key' => 'setup',
        'title' => 'Setup',
        'fields' => get_setup_acf_fields(),
        'location' => array (
            array (
                array (
                    'param' => 'options_page',
                    'operator' => '==',
                    'value' => 'theme-setup',
                ),
            ),
        ),
        'menu_order' => 0,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen' => '',
    ));
    add_action('acf/init', 'acf_add_local_field_group');
endif;

function get_setup_acf_fields() {
    return array(
        get_emails_repeater_field(),
        get_setup_center_for_users_field(),
        get_setup_instagram_field(),
        get_setup_facebook_field()
    );
}

function get_setup_instagram_field() {
    return array (
        'key' => 'field_setup_instagram',
        'label' => 'Instagram',
        'name' => 'instagram',
        'type' => 'text',
        'required' => 0
    );
}

function get_setup_facebook_field() {
    return array (
        'key' => 'field_setup_facebook',
        'label' => 'Facebook',
        'name' => 'facebook',
        'type' => 'text',
        'required' => 0
    );
}

function get_setup_center_for_users_field() {
    return array (
        'key' => 'field_setup_initial_center_for_users',
        'label' => 'Initial center for users',
        'name' => 'initial_center_for_users',
        'type' => 'google_map',
        'required' => 1
    );
}

function get_emails_repeater_field() {
    return array (
        'key' => 'field_setup_notifiedUsers',
        'label' => 'E-mails',
        'name' => 'notifiedUsers',
        'type' => 'repeater',
        'required' => 0,
        'sub_fields' => array(
            array (
                'key' => 'email',
                'label' => 'E-mail',
                'name' => 'email',
                'type' => 'text',
                'required' => 1,
                'parent' => 'notifiedUsers'
            )
        )
    );
}
