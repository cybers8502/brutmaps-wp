<?php

function register_acf_options_pages() {
    if( !function_exists('acf_add_options_page') )
        return;
    $option_page = acf_add_options_page(array(
        'page_title'    => __('Setup'),
        'menu_title'    => __('Setup'),
        'menu_slug'     => 'theme-setup',
        'capability'    => 'edit_posts',
        'redirect'      => false
    ));
}

add_action('acf/init', 'register_acf_options_pages');
