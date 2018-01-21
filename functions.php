<?php


wp_register_script('jquery', 'http://code.jquery.com/jquery-latest.min.js');
wp_enqueue_script( 'jquery');


/**
 * Desactivamos el item
 */
function uz_deactivate_item(){

$items="";
$checkboxArray = $_POST['check'];

foreach($checkboxArray as $checkbox){

    $item = $checkbox;
    
    $items.= "'".$item."'";   
	
	if (end($checkboxArray)!==$checkbox){
		$items.=",";
	}
	
	$postid = $_POST['postid_'.$item];
	uz_delete_posts ($postid);	
}

uz_delete_items($items);	
	
}

function uz_delete_posts($clave){

	$args = array( 
	'post_type' => 'attachment',
	'numberposts' => -1,
    'post_parent' => $clave    
	);
	
	$posts = get_posts( $args );
	
	if (is_array($posts)){	
		foreach ($posts as $post_id){
			wp_delete_post($post_id->ID);   
		}
	}	 
	
	wp_delete_post($clave);   
	
}


function uz_plugin_post(){

$checkboxArray = $_POST['check'];

foreach($checkboxArray as $checkbox){

    $item = $checkbox;
	$title  = $_POST['title_'.$item];
	$description  = $_POST['description_'.$item];
	uz_insert_item($item,$title,$description);
    
}

}

function uz_insert_post_amazon($params){

            $updatezon_settings   	= get_option('updatezon_plugin_settings');
            $local_store 			= $updatezon_settings['amazon_local_store'];			
			$presentation 			= $updatezon_settings['presentation'];			
			$positionUzon       	= $updatezon_settings['positionUzon'];			
			$contentPosition		= $updatezon_settings['content'];			
			$content = "";
	
			
			$item = $params['dataItems']['id'];
			$title = $params['dataItems']['title'];
			$image = $params['dataItems']['image'];
			
			$region = str_replace("Amazon.","",$local_store); // extract region
            $shortcode = '[updatezon chart="'.$item.'" store="'.$region.'"]';
           
		    if ($positionUzon=='before' || $positionUzon=='both'){
				$content .= $shortcode."<br/>";
			}
			
			$description = $params['dataPost']['description'];
			$category = $params['dataPost']['category'];
			
			$terms = array();
			
			if (!empty($category)){
			$terms = uz_insert_terms($category);
			}
			
			$images_array = $params['dataPost']['images_array'];

			if (!in_array("description",$contentPosition)){
				$description="";
			}
			if (!in_array("images",$contentPosition)){
				$image = "";
				$images_array = null;
			}
			
			$my_post = array(
			  'post_title'    => $title,			 
			  'post_status'   => 'publish',
			  'post_author'   => 1,
			  'post_category' => $terms  //meter las categorias
			);

			// Insert the post into the database
			$post_id = wp_insert_post( $my_post );
			
			$attach_id = uz_upload_images ($image,$post_id);
			set_post_thumbnail( $post_id, $attach_id );  
			
			foreach ($images_array as $single_image)
			{
			$attach_id = uz_upload_images($single_image, $post_id);
			$description .= "<img src=\'".wp_get_attachment_url($attach_id)."'/><br/>";
			}		
			
			$content .= $description;
			
            if ($positionUzon=='after' || $positionUzon=='both'){
				$content .= "<br/>".$shortcode;
			}
			
			$my_post = array(
			  'ID'           => $post_id,			
			  'post_content' => html_entity_decode($content)
			);		
			
			
			// Update the post into the database
			wp_update_post( $my_post );
			
			return $post_id;
}


 function uz_insert_terms ($category){

 	  $terms = array();
	  $parent_term = term_exists( $category, 'category' ); // array is returned if taxonomy is given
	  $parent_term_id = $parent_term['term_id']; // get numeric term id
		if (empty($parent_term_id)){
			$tax_insert_id = wp_insert_term($category, 'category');
			$terms[] = $tax_insert_id['term_id'];
		}else{
			array_push ($terms,$parent_term['term_id']);
		}
	 
	
	 return $terms;
 }


 function uz_upload_images ($image,$post_id){
	 
	 		$upload_dir = wp_upload_dir();
			
			$image_data = file_get_contents($image);
			$filename = sanitize_file_name(basename($image));
			
			if(wp_mkdir_p($upload_dir['path']))
				$file = $upload_dir['path'] . '/' . $filename;
			else
				$file = $upload_dir['basedir'] . '/' . $filename;
			file_put_contents($file, $image_data);

			$wp_filetype = wp_check_filetype($filename, null );
			$attachment = array(
				'post_mime_type' => $wp_filetype['type'],
				'post_title' => $filename,
				'post_content' => '',
				'post_status' => 'inherit'
			);
			$attach_id = wp_insert_attachment( $attachment, $file, $post_id );
			require_once(ABSPATH . 'wp-admin/includes/image.php');
			$attach_data = wp_generate_attachment_metadata( $attach_id, $file );
			wp_update_attachment_metadata( $attach_id, $attach_data );
	 
			return $attach_id;
	 
 }
  
 
  function uz_plugin_search() {
		$amazonLocale_array = uz_get_amazon_store();
        
		$form_data = unserialize( $_SESSION['form_data'] );
		$page 	  = $form_data['amazon_page'];
		
		$checkbox = $form_data['amazon_checkbox_'.$page];			
		
        /*
        Obtenemos los items que tenemos registrados como opciones de bbdd
        Con esto sacaremos todo lo que hayamos publicado y lo dejaremos checkeado.
        */
        $options = get_option( 'updatezon_items_settings');		  	
        $products_id = uz_get_active_items();
		
        $lookup_result = uz_amazon_keyword_lookup();		
		
		if (count ($lookup_result->Items->Item)>0)
			{						
				echo '
			<h1>UpdateZon - Búsqueda de Productos</h1>
			<div class="separador" style="margin-top:60px;"></div>
			<form method="post">
			<table width="100%" cellpadding="10px;" style="border-spacing:0;">
			<tbody><tr>
				<th align="center">Imagen</th>
				<th align="left">Nombre producto</th><th></th><th></th>
				<th align="left">Enlace</th>
				<th align="left">Precio</th>
				<th><input type="checkbox" onclick="toggle();"></th>
			</tr>';
			$i = 0;
			foreach($lookup_result->Items->Item as $itemKey=>$item)
					{
					
                    $totalPages = $lookup_result->Items->TotalPages;
                    $itemKey = $item->ASIN;
					$url = $item->DetailPageURL;
					$title = $item->ItemAttributes->Title;
					$image = $item->MediumImage->URL;
                    $category= $item->ItemAttributes->Binding; 
					
					if(empty($category)){
						$category= $item->ItemAttributes->ProductGroup; 
					}
					
					$page = $lookup_result->Items->Request->ItemSearchRequest->ItemPage;
                    $feature="";
                    
                    if(isset($item->EditorialReviews) and isset($item->EditorialReviews->EditorialReview) 
                        and isset($item->EditorialReviews->EditorialReview->Content))
                    {
                    $feature=$item->EditorialReviews->EditorialReview->Content;
                    }
                    
                    foreach($item->ItemAttributes->Feature as $featureKey=>$featureValue)
                    {
                    $featureConcat=$featureValue."<br/>";
                    }
                 
                    $feature.="<br/>".$featureConcat;
					$price=0;
					if (isset($item->ItemAttributes->ListPrice->FormattedPrice)){
                         $price	= $item->ItemAttributes->ListPrice->FormattedPrice;
					}
				
					if(isset($item->Offers->TotalOffers) &&  $item->Offers->TotalOffers )
					{
						$price	= $item->Offers->Offer->OfferListing->Price->FormattedPrice; 
					}
			
			$checked = "";
			
			if (($checkbox!=null && in_array($itemKey,$checkbox)) || in_array($itemKey,$products_id)){
				$checked = "checked disabled";				
			}


            if (strcmp ($page,$totalPages)==0)
            {
                $disabledNext = "disabled";
            }
            if ($page == 1){
                $disabledPrevious = "disabled";
            }

            $color = "background-color: rgb(181, 212, 245);";
			if ($i % 2 == 0){
				$color = "background-color: rgb(215, 219, 225);";
			}			
            
			if (!empty($price)){ echo '
			
			<script language="JavaScript">
				function toggle() {
				  checkboxes = document.getElementsByName("check[]");				
				 
					 for (var i = 0; i < checkboxes.length; i++) {
						 if (checkboxes[i].type == "checkbox" && !checkboxes[i].disabled) {
                            if (checkboxes[i].checked)
                            {
                            checkboxes[i].checked = "";
							}else{
							 checkboxes[i].checked = true;
							 }
						 }
				 }			 
				 
				}
			
			</script>
			<tr style="'.$color.'">
			<td><img height="60px" src="'.$image.'"></td>
			<td colspan="3"><input type="hidden" value="'.$page.'" name="page">
            <input style="width:100%;" type="text" size="100%" value="'.$title.'" name="title_'.$itemKey.'"></td>
			<td><a href="'.$url.'"><label>Enlace</label></td><td><label>'.$price.'</label></td>
			<td align="center" ><input type="checkbox" name="check[]" value="'.$itemKey.'" '.$checked.'/></td>
			</tr>
			<tr style="'.$color.'">
			<td><strong>Descripción</strong></td>
			<td colspan="6"><input type="text" style="width:100%" value="'.htmlspecialchars($feature).'" name="description_'.$itemKey.'"></td>
			</tr>';  }
           $i=$i+1;
					}
					echo '
					</tbody>
		</table>		
        <div style="float: left;width: 40%; margin-top:20px;">
            <input type="submit" name="updatezon_plugin_submit_post" class="button button-primary" value="Publicar">
        </div>
        <div style="display: inline-block; margin: 0 auto; width: 50%; margin-top:20px;">
            <input type="submit" name="updatezon_plugin_submit_previous" class="button button-primary" value="Anterior" '.$disabledPrevious.'>
            <div style="display: inline-block; margin: 5px;">'.$page.' / '.$totalPages.' pages</div>
            <input type="submit" name="updatezon_plugin_submit_next" class="button button-primary" value="Siguiente" '.$disabledNext.'>
        </div>
        <div class="separador" style="margin-top:60px;"></div>
        </form>';
				}
				else{
						echo "<h2>No hemos encontrado resultados. Por favor, afine la búsqueda</h2>";			
				}
		
					
  }


  function updatezon_search(){
  
         /************/
		if( isset($_POST['updatezon_plugin_submit_search'] ) ) :

			$form_data['amazon_keyword_search'] = $_POST['amazon_keyword_search'] ;
			$form_data['amazon_page'] = 1;
			$_SESSION['form_data'] = serialize($form_data);
			
            uz_plugin_search();				
				
        endif;
       /************/
       		$form_data = array();
		
        /************/
	     if( isset($_POST['updatezon_plugin_submit_post'] ) ) :		
                uz_plugin_post();
				uz_plugin_search();				
        endif;
        /************/
		/************/
        if( isset($_POST['updatezon_plugin_submit_next'] ) ) :					
				
				
                $page = $_POST['page'];		
				$form_data = unserialize( $_SESSION['form_data'] );
				
				$form_data['amazon_page'] = $page+1 ;								
				$form_data['checkbox_page'] = $page;
				$form_data['amazon_checkbox_'.$page] = $_POST['check'];						
				
				$_SESSION['form_data'] = serialize($form_data);
				
				uz_plugin_search();				
        endif;
        /************/
		/************/
        if( isset($_POST['updatezon_plugin_submit_previous'] ) ) :				
                $page = $_POST['page'];
				$form_data = unserialize( $_SESSION['form_data'] );
				$form_data['amazon_page'] = $page-1 ;
				$form_data['checkbox_page'] = $page;
				$form_data['amazon_checkbox_'.$page] = $_POST['check'];	
				
				$_SESSION['form_data'] = serialize($form_data);
				 
               uz_plugin_search();
			
        endif;
        /************/
  
  		    echo '
        <div class="wpseo_content_cell" id="wpseo_content_top">
            <div class="metabox-holder">
            <h1>UpdateZon - Búsqueda de Productos</h1>
            <div class="separador"></div>
            <h2>Encuentra tu producto</h2>
            <form method="post">
                 <div class="controls">
                       <div class="uzonsection">                                                    
                            <h4 class="width20 inline">Buscador de productos</h4>
                             <div class="width60 inline">
                                    <input id="amazon_keyword_search" class="of-input" name="amazon_keyword_search" type="text">										
                                    <input type="submit" name="updatezon_plugin_submit_search" class="button button-primary" value="Buscar" />			                                
                            </div>                     
                       </div>                       
				</div>
        </form>
       </div>
    </div>
    ';
  
  }
  
  function uz_google_short_url($long_url){
  
    $key="";

    $updatezon_settings   	= get_option('updatezon_plugin_settings');
    $api_key = $updatezon_settings['google_short_api'];
    
    
    $url = "https://www.googleapis.com/urlshortener/v1/url?key=".$api_key;
 
    $ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch,CURLOPT_POST,1);
    curl_setopt($ch,CURLOPT_POSTFIELDS,json_encode(array("longUrl"=>$long_url.$_POST['qrname'])));
	curl_setopt($ch,CURLOPT_HTTPHEADER,array("Content-Type: application/json"));
    
	$response = json_decode(curl_exec($ch),true);
   
    
	curl_close($ch);

    $short_url = $response['id'];

    return $short_url;
  
  }
  
  

function uz_get_amazon_store()
{
	$amazon_store = array(
    						"es"=>"Amazon.es",
							"us"=>"Amazon.com",
							"ca"=>"Amazon.ca",
							"cn"=>"Amazon.cn",
							"de"=>"Amazon.de",
							"fr"=>"Amazon.fr",
							"it"=>"Amazon.it",
							"jp"=>"Amazon.co.jp",
							"uk"=>"Amazon.co.uk",
						);
	return $amazon_store;
}



function uz_get_concurrency()
{
	$concurrency = array(
    						"EUR",
							"USD",
							"CAD",
							"CNY",
							"JPY",
							"GBP",
						);
	return $concurrency;
}

function uz_get_store_concurrency($store)
{
	if ($store==='es' || $store==='de' || $store==='fr' || $store==='it'){
		return "EUR";
	}
	
	if ($store==='us'){
		return "USD";
	}
	
	if ($store==='ca'){
		return "CAD";
	}
	
	if ($store==='cn'){
		return "CNY";
	}
	
	if ($store==='jp'){
		return "JPY";
	}

	if ($store==='uk'){
		return "GBP";
	}

}


function uz_aws_signed_request($params)
{
	// get api credentials
    $updatezon_settings   	= get_option('updatezon_plugin_settings');
	$associate_tag    	= $updatezon_settings['amazon_associate_tag'];
	$access_key_id 		= $updatezon_settings['amazon_access_key_id'];
	$secret_access_key	= $updatezon_settings['amazon_secret_access_key'];
	$local_store 		= $updatezon_settings['amazon_local_store'];
    
    if ( !$associate_tag || !$access_key_id || !$secret_access_key ) {
		return false ;
	}

	$region = str_replace("Amazon.","",$local_store); // extract region

	$params["AssociateTag"]	= $associate_tag;

    $method = "GET";
    $host = "webservices.amazon.".$region;
    $uri = "/onca/xml";

    $params["Service"] = "AWSECommerceService";
    $params["AWSAccessKeyId"] = $access_key_id;

    //timestamp
    $params["Timestamp"] = gmdate("Y-m-d\TH:i:s\Z");

    // API version
    $params["Version"] = "2011-08-31";
    
    ksort($params);

    // create the canonicalized query
    $canonicalized_query = array();

    foreach ($params as $param=>$value)
    {
        $param = str_replace("%7E", "~", rawurlencode($param));
        $value = str_replace("%7E", "~", rawurlencode($value));
        $canonicalized_query[] = $param."=".$value;
    }

    $canonicalized_query = implode("&", $canonicalized_query);

    // create the string to sign
    $string_to_sign = $method."\n".$host."\n".$uri."\n".$canonicalized_query;

    // calculate HMAC with SHA256 and base64-encoding
    $signature = base64_encode(hash_hmac("sha256", $string_to_sign, $secret_access_key, True));

    // encode the signature for the request
    $signature = str_replace("%7E", "~", rawurlencode($signature));

    // create request
    $request = "http://".$host.$uri."?".$canonicalized_query."&Signature=".$signature;
	
	$data="";

    $resp = simplexml_load_file($request);	
    return $resp;

}

function uz_convert_currency($amount, $from, $to){
	if ($from===$to) return $amount;
    $url  = "https://www.google.com/finance/converter?a=$amount&from=$from&to=$to";
    $data = file_get_contents($url);
    preg_match("/<span class=bld>(.*)<\/span>/",$data, $converted);	
    $converted = preg_replace("/[^0-9.]/", "", $converted[1]);
    return round($converted, 3);
}

function uz_get_active_items(){
	
	global $wpdb;
	$table_name_items   = $wpdb->prefix . 'uz_items';	
	
	$rows = $wpdb->get_results( "SELECT id FROM $table_name_items");
    $products = array();
  
    foreach($rows as $row)
	{
		array_push($products,$row->id);
	}
	
	return $products;
}



function uz_products_count(){
	
    global $wpdb;
	$table_name_items   = $wpdb->prefix . 'uz_items';	
	
	$count = $wpdb->get_var( "SELECT count(1) FROM $table_name_items");
    
    return $count;
}


function uz_delete_items($items){
	
	global $wpdb;
	
	$table_name_items   = $wpdb->prefix . 'uz_items';	
	$table_name_prices  = $wpdb->prefix . 'uz_prices';	
	
	$wpdb->query( $wpdb->prepare( "DELETE FROM $table_name_items WHERE id in ($items)"));
	$wpdb->query( $wpdb->prepare( "DELETE FROM $table_name_prices WHERE item_id in ($items)"));
}

function uz_products_resume($page,$total_pages){
	
    $limit_init = $total_pages * $page;
    
    global $wpdb;
	$table_name_items   = $wpdb->prefix . 'uz_items';	
	$table_name_prices   = $wpdb->prefix . 'uz_prices';	
    
	$rows = $wpdb->get_results( "SELECT id, postid,title,url, price FROM $table_name_items itm inner join $table_name_prices pri
								on pri.item_id = itm.id 
								group by pri.item_id order by date desc limit $limit_init , $total_pages");
    
    $products = array();
  
    foreach($rows as $row)
	{
		array_push($products,$row);
	}
	
	return $products;
}

function uz_insert_item($id,$title,$description){

	// set max update limit
	$max_limit		= 1000; 	// max limit due to amazon api call limit/hr ( 1000 )
		
    $count=0;
	$amazonLocale_array = uz_get_amazon_store();
	
    $updatezon_settings = get_option('updatezon_plugin_settings');      
    $updatezon_item     = get_option('updatezon_items_settings');    
    
	$count++;
	
	// check item count per api call. max 1000 per hour                
	if($count<$max_limit && !empty($id))
	{
		$params = uz_get_amazon_price($id,$updatezon_settings,$amazonLocale_array);
		
		$params['dataItems']['title'] = $title;
		$params['dataPost']['description'] = $description;
		
    	$postid = uz_insert_post_amazon($params);

		uz_replace_items ($params,$postid);
        uz_replace_prices ($params);
	}					

	if( $count == $max_limit ) { return; }




}

function uz_get_amazon_price($id,$updatezon_settings,$amazonLocale_array){

  global $wpdb;
  $table_name_items   = $wpdb->prefix . 'uz_items';	
  $table_name_prices  = $wpdb->prefix . 'uz_prices';	
  $currency 		    = $updatezon_settings['currency'];
  
  /*Establecemos el valor de los items en bdd*/
  $isActive_spinner = $updatezon_settings['espiner_active'];
 
  $store_locale	= array_search ($updatezon_settings['amazon_local_store'],$amazonLocale_array);                      
  
  if (empty($store_locale)) return;
  
  $local		=   $updatezon_settings['amazon_local_store'];

  $lookup_result = uz_amazon_item_lookup($id,$local);
  // update price
  
if (count ($lookup_result->Items->Item)>0)
{
	foreach($lookup_result->Items->Item as $itemKey=>$item)
	{
	  // set asin
		$itemKey = $item->ASIN;
		// set url
		$url = $item->DetailPageURL;
		
		$title = $item->ItemAttributes->Title;
		$image = $item->LargeImage->URL;
		$category= $item->ItemAttributes->Binding; 
		
		if(empty($category)){
			$category= $item->ItemAttributes->ProductGroup; 
		}
		
		$images_array = array();
		
		foreach ($item->ImageSets->ImageSet as $imageSet)
		{ 
			array_push($images_array, $imageSet->LargeImage->URL);
		}
		
		$feature="";

		if(isset($item->EditorialReviews) and isset($item->EditorialReviews->EditorialReview)
			and isset($item->EditorialReviews->EditorialReview->Content))
		{
		$feature=$item->EditorialReviews->EditorialReview->Content;
		}
		
		foreach($item->ItemAttributes->Feature as $featureKey=>$featureValue)
		{
		$featureConcat=$featureValue."<br/>";
		}
	 
		$feature.="<br/>".$featureConcat;
		
		// set price
		$new_price=0;
		if (isset($item->ItemAttributes->ListPrice->FormattedPrice)){
		$new_price	= $item->ItemAttributes->ListPrice->FormattedPrice;
		}

		{
			$new_price	= $item->Offers->Offer->OfferListing->Price->FormattedPrice; 
		}
		
		preg_match("/s*(\d+(?:[\.\,]\d+)(?:[\.\,]\d+)*)/", $new_price, $matchesNew);
		
		
		
		if ($store_locale==='es'){
		$new_price = floatval(str_replace(',', '.', str_replace('.', '', $matchesNew[0])));
		}else{
		$new_price = floatval(str_replace(',', '.', $matchesNew[0]));
		}
		
		$new_price = uz_convert_currency($new_price,uz_get_store_concurrency($store_locale),$currency);
		
		
		$row = $wpdb->get_row( $wpdb->prepare("SELECT price FROM $table_name_prices WHERE item_id = %s order by date desc limit 1",$id));
		
		if ($isActive_spinner=='active'){		
			$feature = uz_spinner_text($feature);
		}
    
		
		//si insertamos necesitamos mas datos que para actualizar
		$dataItems = array('id'     => $itemKey,                                    
					  'title' => $title,
					  'image' => $image,
                      'longUrl'    => $url,					  
					  'url'    => uz_google_short_url($url),
					  'currency' => $currency);
	
		$dataPost   = array (
					  'description' => htmlspecialchars($feature),
					  'images_array' => $images_array,
					  'category' => $category,
					  );
					  
		$dataPrices = array('item_id'     => $itemKey,                                    
					  'price' => round($new_price,2),
					  'priceOld' => floatval($row->price),
					  'date'  => current_time('mysql', 1));

        
        $params		= array(
                        "dataItems"		=> $dataItems,
					    "dataPrices"	=> $dataPrices,
						"dataPost"   	=> $dataPost
                    );
        
	
        return $params;
	}
}	
		
}

function uz_replace_items($params,$postid){
		global $wpdb;
		$table_name_items   = $wpdb->prefix . 'uz_items';	
        
		$dataItems = $params['dataItems'];
		$dataItems['postid']=$postid;
		
		$wpdb->replace($table_name_items,$dataItems);       
        
}

function uz_replace_prices($params){
        global $wpdb;
		$table_name_prices  = $wpdb->prefix . 'uz_prices';	
		
        $dataPrices = $params['dataPrices'];        
        $wpdb->replace($table_name_prices,$dataPrices);
}

function uz_amazon_item_lookup($asin_list,$local_store) 
{

    $params		= array(
                        "Operation"		=> "ItemLookup",
                        "ItemId"		=> $asin_list,
                        "ResponseGroup"	=> "Medium,Offers",                        
                        "Store"		    => $local_store,
                    );

    $Results = uz_aws_signed_request($params);
	if( $Results === false )
    {	return false;	}
    else
    {
		return $Results;
		
    }

}

function uz_update_price(){

    global $wpdb;
    $table_name_items   = $wpdb->prefix . 'uz_items';	


    // set max update limit
	$max_limit		= 1000; 	// max limit due to amazon api call limit/hr ( 1000 )
		
    $count=0;
	$amazonLocale_array = uz_get_amazon_store();
	
    $updatezon_settings = get_option('updatezon_plugin_settings');      
    $updatezon_item     = get_option('updatezon_items_settings');      

    $rows = $wpdb->get_results( "SELECT id FROM $table_name_items");
  
    foreach($rows as $row)
	{        
	
	if ($row->id=="") continue;
    
    $count++;

		// check item count per api call. max 1000 per hour                
		if($count<$max_limit)
		{
			$params = uz_get_amazon_price($row->id,$updatezon_settings,$amazonLocale_array);
            uz_replace_prices ($params);
		}					
                
		if( $count == $max_limit ) { break; }
		
	}
	
}


function cd_cleantext($text)
{
	return $text;
}

function uz_amazon_keyword_lookup()
{
	$page=1;
	$form_data = unserialize( $_SESSION['form_data'] );
	$keyword    				= $form_data['amazon_keyword_search'];
	$page    					= $form_data['amazon_page'];	
	
	$params		= array(
                        "Operation"		=> "ItemSearch",
					    "SearchIndex"	=> "All",
                        "Keywords"		=> $keyword,
                        "ResponseGroup"	=> "Medium,Offers",
						"ItemPage"		=> $page
                    );

    $Results = uz_aws_signed_request($params);		if( $Results === false )
    {	return false;	}
    else
    {
		return $Results;
		
    }

}


  function uz_spinner_text($text){
			
			//se establece un lapso de peticion de 30 segundos para que no se pisen las peticiones y de error espiner
		
			$filmpedia_settings 	= get_option('updatezon_plugin_settings');
			$api = $filmpedia_settings['espinner_key_api']; 
			$email = $filmpedia_settings['espinner_key_email']; 
			
			$params = 'content='.urlencode($text);
			$params .= '&email='.$email.'&apikey='.$api;			
			
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,'http://espinner.net/app/api/spinner');			
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.1.2) Gecko/20090729 Firefox/3.5.2 GTB5');
			curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$response = json_decode(curl_exec($ch),true);
			curl_close ($ch);			 
			
			return $response['spin_unique'];			
  
  }
  

  
?>