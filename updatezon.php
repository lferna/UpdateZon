<?php


/*

Plugin Name: UpdateZon

Plugin URI: http://www.updatezon.com

Description: Keep prices of all your Amazon affiliate products updated. Forget about manually updating and saves time.

Version: 0.1

Author: Luis Fern&aacute;ndez.

Author URI: http://updatezon.com/  

*/

//Variable
define('UPZ_PATH', ABSPATH . 'wp-content/plugins/updatezon/');
//Se crean las tablas
require_once UPZ_PATH . 'config.php';
require_once UPZ_PATH . 'install.php';
require_once UPZ_PATH . 'functions.php';
require_once UPZ_PATH . 'shortcodes.php';
require_once UPZ_PATH . 'products.php';
require_once UPZ_PATH . 'charts.php';

register_activation_hook( __FILE__, 'create_plugin_database_table' );

function updatezon_plugin_init() {


    add_menu_page( 'UpdateZon', 'UpdateZon', 'manage_options', 'updatezon_search', 'updatezon_search',plugins_url('images/logo_uz.png', __FILE__) );	
	add_submenu_page( 'updatezon_search', 'UpdateZon Búsqueda', 'Buscar', 'manage_options','updatezon_search');
	add_submenu_page( 'updatezon_search', 'UpdateZon Productos', 'Productos', 'manage_options','updatezon_products', 'uz_view_products');
	add_submenu_page( 'updatezon_search', 'UpdateZon Ajustes', 'Ajustes', 'manage_options','updatezon_settings', 'uz_plugin_setting');

	
	//si existe la sesión, la destruimos antes de crear otra
    if (isset($_SESSION))
    {
    
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }    
    session_destroy();
    }
    
	session_start();	
 }
  
//init
add_action( 'admin_menu', 'updatezon_plugin_init' );

?>