<?php

if( function_exists('acf_add_local_field_group') ):
    acf_add_local_field_group(array (
        'key' => 'contributor',
        'title' => 'Contributor',
        'fields' => get_contributor_acf_fields(),
        'location' => array (
            array (
                array (
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'contributor',
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

function get_contributor_acf_fields() {
    return array(
        get_contributor_first_name_field(),
        get_contributor_last_name_field(),
        get_contributor_email_field(),
        get_contributor_link_field(),
        get_contributor_linked_objects_field()
    );
}

function get_contributor_first_name_field() {
    return array (
        'key' => 'field_contributor_first_name',
        'label' => 'First name',
        'name' => 'first_name',
        'type' => 'text',
        'required' => 1,
        'wrapper' => array (
            'width' => '50',
        )
    );
}

function get_contributor_last_name_field() {
    return array (
        'key' => 'field_contributor_last_name',
        'label' => 'Last name',
        'name' => 'last_name',
        'type' => 'text',
        'required' => 1,
        'wrapper' => array (
            'width' => '50',
        )
    );
}

function get_contributor_email_field() {
    return array (
        'key' => 'field_contributor_email',
        'label' => 'E-mail',
        'name' => 'email',
        'type' => 'text',
        'required' => 1,
        'wrapper' => array (
            'width' => '50',
        )
    );
}

function get_contributor_link_field() {
    return array (
        'key' => 'field_contributor_link',
        'label' => 'Link',
        'name' => 'link',
        'type' => 'text',
        'required' => 0,
        'wrapper' => array (
            'width' => '50',
        )
    );
}

function get_contributor_linked_objects_field() {
    return array (
        'key' => 'field_contributor_linked_sights',
        'label' => 'linked objects',
        'name' => 'linked_sights',
        'type' => 'post_object',
        'required' => 0,
        'post_type' => 'contributor',
        'return_format' => 'id',
        'multiple' => 1
    );
}
