<?php

function create_plugin_database_table() {
    global $wpdb;
    $table_name_items  = $wpdb->prefix . 'uz_items';	
    $table_name_prices = $wpdb->prefix . 'uz_prices';	
	$charset_collate = $wpdb->get_charset_collate();
 
 if($wpdb->get_var("show tables like '$table_name_prices'") != $table_name_prices) 
	{
    $sql_prices = "CREATE TABLE $table_name_prices (
        item_id varchar(100) NOT NULL,
		price decimal(7,2),
        date datetime,
        priceOld decimal(7,2)        
        )$charset_collate;";
	}

	if($wpdb->get_var("show tables like '$table_name_items'") != $table_name_items) 
	{	
    $sql_items = "CREATE TABLE $table_name_items (
        id varchar(100) NOT NULL,
		url varchar(500),
        longUrl varchar(2000),
		image varchar(500),
        title varchar(500),
		currency varchar(5),
        postid varchar(100) NOT NULL,		
        UNIQUE KEY  business (id)
        )$charset_collate;";
    
	}
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql_prices );
    dbDelta( $sql_items  );    
}


?>