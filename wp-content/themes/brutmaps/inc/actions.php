<?php
// Remove Menu
add_action( 'admin_menu', 'remove_menus' );

function remove_menus(){

    remove_menu_page( 'index.php' );                  // Консоль
    remove_menu_page( 'edit.php' );                   // Записи
    remove_menu_page( 'upload.php' );                 // Медиафайлы
    remove_menu_page( 'edit.php?post_type=page' );    // Страницы
    remove_menu_page( 'edit-comments.php' );          // Комментарии
    remove_menu_page( 'themes.php' );                 // Внешний вид
    remove_menu_page( 'plugins.php' );                // Плагины
    remove_menu_page( 'users.php' );                  // Пользователи
    remove_menu_page( 'tools.php' );                  // Инструменты
    remove_menu_page( 'options-general.php' );        // Параметры

}

// Add Google Map Key
function my_acf_init() {

    acf_update_setting('google_api_key', '***REMOVED-GOOGLE-API-KEY***' );
}

add_action( 'acf/init', 'my_acf_init' );