<?php 

	header('Access-Control-Allow-Origin: *'); 

	set_time_limit(300);
	require_once( '../../../wp-load.php' );
	header("HTTP/1.1 200 OK");
	
	uz_update_price();

	echo "Updated!";

?>