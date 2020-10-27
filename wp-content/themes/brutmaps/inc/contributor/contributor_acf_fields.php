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
        'key' => 'contributor_first_name',
        'label' => 'First name',
        'name' => 'contributor_first_name',
        'type' => 'text',
        'required' => 1,
        'wrapper' => array (
            'width' => '50',
        )
    );
}

function get_contributor_last_name_field() {
    return array (
        'key' => 'contributor_last_name',
        'label' => 'Last name',
        'name' => 'contributor_last_name',
        'type' => 'text',
        'required' => 1,
        'wrapper' => array (
            'width' => '50',
        )
    );
}

function get_contributor_email_field() {
    return array (
        'key' => 'contributor_email',
        'label' => 'E-mail',
        'name' => 'contributor_email',
        'type' => 'text',
        'required' => 1,
        'wrapper' => array (
            'width' => '50',
        )
    );
}

function get_contributor_link_field() {
    return array (
        'key' => 'contributor_link',
        'label' => 'Link',
        'name' => 'contributor_link',
        'type' => 'text',
        'required' => 0,
        'wrapper' => array (
            'width' => '50',
        )
    );
}

function get_contributor_linked_objects_field() {
    return array (
        'key' => 'contributor_linked_sights',
        'label' => 'linked objects',
        'name' => 'contributor_linked_sights',
        'type' => 'post_object',
        'required' => 0,
        'post_type' => 'contributor',
        'return_format' => 'id',
        'multiple' => 1
    );
}
