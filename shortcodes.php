<?php

function uz_shortcode($atts, $content = null) {
    extract(shortcode_atts(array(
    'store' => '',
    'chart' => '',
	'style' => ''
), $atts)); 
    global $wpdb;
    $table_name_items  = $wpdb->prefix . 'uz_items';	
    $table_name_prices = $wpdb->prefix . 'uz_prices';	
	
	$id=$chart;

	$query = "SELECT currency,price,date,longUrl,url,title,image,priceOld FROM $table_name_items itm,$table_name_prices pri WHERE itm.id = pri.item_id AND (itm.id = %s)";
	$query.=" order by date desc limit 1";
    $row = $wpdb->get_row( $wpdb->prepare($query,$id) );
    
   	$updatezon_settings 	= get_option('updatezon_plugin_settings');
	$amazon_button 			= $updatezon_settings['amazon_button']; 
	$position 				= $updatezon_settings['position']; 
	$url = $row->url;
	if (empty($row->url)){
		$url = $row->longUrl;
	}
	$button = '
	<a href="'.$url.'" target="_blank" rel="nofollow">
    <img alt="buy button" src="'.plugins_url('images/amazon.png', __FILE__).'">
	</a>
	';
		if (!empty($amazon_button)){
			$button='
			<form id="formulario'.$id.'" method="GET" action="http://www.amazon.'.strtolower($store).'/gp/aws/cart/add.html"> 
				<input type="hidden" name="AssociateTag" value="'. $updatezon_settings['amazon_associate_tag_'.strtolower($store).''] .'">
				<input type="hidden" name="SubscriptionId" value="'. $updatezon_settings['amazon_access_key_id_'.strtolower($store).''] .'">
				<input type="hidden" name="ASIN.1" value="'.$id.'"/>
				<input type="hidden" name="Quantity.1" value="1"/>
				<a href="#" onclick=document.getElementById("formulario'.$id.'").submit(); rel="nofollow">
				<img alt="buy button" src="'.plugins_url('images/amazon.png', __FILE__).'">
				</a>
			</form> 
			';
		}
	

	$currency_position = $row->currency .' '. $row->price;
	$currency_position_old = $row->currency .' '. $row->priceOld;
    if ($position==1){
	$currency_position = $row->price .' '. $row->currency;
	$currency_position_old = $row->priceOld .' '. $row->currency;
	}
   
    $var;
    if(!empty($price)){
        $var = $currency_position;
    }
    if(!empty($date)){
        $var = $row->date . '  UTC';
    }
    if(!empty($url)){       
        $var = $url;
    }

	if(empty($style)){
		
		$style				= $updatezon_settings['presentation']; 
	}
    
    

	
    $iconImage;
	if ($row->priceOld > $row->price){
    $iconImage = plugins_url('images/down.png', __FILE__);
    }else if ($row->priceOld < $row->price){
    $iconImage = plugins_url('images/up.png', __FILE__);
    }else{
	$iconImage = plugins_url('images/equals.png', __FILE__);
	}
    
    $program_store = "amazon" . '.' .strtolower($row->store);

    $div;
    if( $style==='grid' ){
    $div=' 
	
	<div class="updatezon-item-grid-view">
	<div class="updatezon-item-grid-view-image">
    <a rel="nofollow"  target="_blank" href="'.$url.'">
	<img height="100px;" src="'.$row->image.'">
	</a>
	</div>
	<div class="updatezon-item-grid-view-title">
	<a rel="nofollow"  target="_blank" href="'.$url.'">'.$row->title.'
	</a>
	</div>
	<div class="updatezon-item-grid-view-old-price">
    '.$currency_position_old.'
    </div>
    <div class="updatezon-item-grid-view-icon">
    <img src="'.$iconImage.'" alt="icon">
    </div>
    <div class="updatezon-item-grid-view-price">'.$currency_position.'
	</div>
	<div class="updatezon-item-grid-view-date">'.$row->date.' UTC
	</div>
	<div class="updatezon-item-grid-view-link">
    '.$button.'
	</div>
	</div>
	
	';
    }else{
    
    $div='
    <div class="updatezon-products-list-view">
    <div class="updatezon-item-list-view" style="border:none;">
    <div class="updatezon-item-list-view-left">
    <div class="updatezon-item-list-view-image">
    <a href="'.$url.'" target="_blank" rel="nofollow">
    <img src="'.$row->image.'" height="100px;" title="" alt="">
    </a>
    </div>
    </div>
    <div class="updatezon-item-list-view-right">
    <div class="updatezon-item-list-view-title">
    <a href="'.$url.'" target="_blank" rel="nofollow">'.$row->title.'</a>
    </div>
    <div class="updatezon-item-grid-view-old-price">
    '.$currency_position_old.'
    </div>
    <div class="updatezon-item-grid-view-icon">
    <img src="'.$iconImage.'" alt="icon">
    </div>
    <div class="updatezon-item-list-view-price">'.$currency_position.'</div>
    <div class="updatezon-item-list-view-date">'.$row->date.' UTC</div>
    <div class="clear">
    </div>    
    <div class="updatezon-item-list-view-link">
    '.$button.'
    </div>
    </div>
    </div>
    <div class="clear"></div>
    </div>
    ';
    }
    
    $result = wp_updatezon_chart($chart);    
    $div .= "<br/>" . $result;
    
    return $div;
    
    
}

//shortcodes
add_shortcode('updatezon','uz_shortcode');


?>