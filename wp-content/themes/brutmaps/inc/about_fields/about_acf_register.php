<?php

function register_acf_about_options_page() {
    if( !function_exists('acf_add_options_page') )
        return;
    $option_page = acf_add_options_page(array(
        'page_title'    => __('About'),
        'menu_title'    => __('About'),
        'menu_slug'     => 'theme-about',
        'capability'    => 'edit_posts',
        'redirect'      => false
    ));
}

add_action('acf/init', 'register_acf_about_options_page');
