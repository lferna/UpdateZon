<?php

function uz_view_products (){
        $products_per_page=10;     
        $page = 0;
        
        $updatezon_settings   	= get_option('updatezon_plugin_settings');
        $api_key =  $updatezon_settings['google_short_api'];
        $key="";
        if ($api_key!=null){
            $key  = "&key=".$api_key;
        }     
        
        /************/
	     if( isset($_POST['updatezon_plugin_deactivate'] ) ){
			 if (!isset($_POST['check'])){
				echo '<script>alert("Por favor, seleccione al menos un producto");</script>';
			}
			else{
			    uz_deactivate_item();
			}
		 }
        /************/		
        /************/
	     if( isset($_POST['updatezon_plugin_submit_next'] ) ){
            $page = $_POST['page']+1;            
        }        

 $total = uz_products_count();
 $rows = uz_products_resume($page,$products_per_page); 
 
 $num_rows = count($rows);
 
 
 $total_pages = ceil($total/$products_per_page);     

 $total_pages = $total_pages==0?1:$total_pages;
    echo '
		<h1>UpdateZon - Mis Productos</h1>
		<div class="separador" style="margin-top:60px;"></div>
        <form method="post">
		<table width="100%" cellpadding="10px;">
		<tbody>
		<tr>
			<th align="left">Nombre producto</th><th></th><th></th>
            <th align="left">Post</th>
            <th align="left">Enlace</th>
			<th align="left">Precio actual</th>
            <th align="left">Nº. Clicks</th>
			<th><input type="checkbox" onclick="toggle();"></th>
		</tr>';

		
		$disabledNext = ""; 
        $disabledPrevious = "";
    	if (($page+1 == $total_pages))
        {
		$disabledNext = "disabled";
        }        
        if ($page+1 == 1){
          $disabledPrevious = "disabled";
        }    
		
        
        for ($i  = 0;$i<($num_rows<$products_per_page?$num_rows:$products_per_page);$i++){

        
        /**ESTADISTICAS DE CLICKS**/
        $url = "https://www.googleapis.com/urlshortener/v1/url?shortUrl=".$rows[$i]->url.$key."&projection=FULL";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_ENCODING, 1);
        $response = json_decode(curl_exec($ch),true);
        curl_close($ch);
        $clicks = $response['analytics']['allTime']['shortUrlClicks'];       
       
       if (!isset($clicks)){
            $clicks = "<a href='/wp-admin/admin.php?page=updatezon_settings'>Error API Key</a>";
        }
        /**/
		$color = "background-color: rgb(181, 212, 245);";
        if ($i % 2 == 0){
			$color = "background-color: rgb(215, 219, 225);";
		}
		
 
		echo'
        <tr style="'.$color.'">
			<input type="hidden" value="'.$rows[$i]->id.'" name="id">
            <input type="hidden" value="'.$page.'" name="page">
            <input type="hidden" value="'.$rows[$i]->postid.'" name="postid_'.$rows[$i]->id.'" >
			<td colspan="3">'.$rows[$i]->title.'</td>
            <td><a href="/?p='.$rows[$i]->postid.'"><label>Link</label></td>
			<td><a href="'.$rows[$i]->url.'"><label>Link</label></td><td><label>'.$rows[$i]->price.'</label></td>
            <td><label>'.$clicks.'</label></td>
			<td align="center" ><input type="checkbox" name="check[]" value="'.$rows[$i]->id.'"/></td>
        </tr>	
		';    
        }    
		echo '
		</tbody>
		</table>
				
					<script language="JavaScript">
				function toggle() {
				  checkboxes = document.getElementsByName("check[]");				
				 
					 for (var i = 0; i < checkboxes.length; i++) {
						 if (checkboxes[i].type == "checkbox") {
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
		
        <div style="margin-top:50px;">
		<div style="float: left;width: 40%;">
            <input type="submit" name="updatezon_plugin_deactivate" class="button button-primary" value="Eliminar">
        </div>
        <div style="display: inline-block; margin: 0 auto; width: 50%;">
            <input type="submit" name="updatezon_plugin_submit_previous" class="button button-primary" value="Anterior" '.$disabledPrevious.'>           
            <div style="display: inline-block; margin: 5px;">'.($page+1).' / '.$total_pages.' pág.</div>
            <input type="submit" name="updatezon_plugin_submit_next" class="button button-primary" value="Siguiente" '.$disabledNext.'>
        </div>
        <div class="separador" style="margin-top:60px;"></div>
		</div>
		
		</form>
		';
 } 

?>