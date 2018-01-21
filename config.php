<?php

//styles
wp_register_style('styleUzon', plugins_url('css/styles.css', __FILE__));
wp_enqueue_style( 'styleUzon');



 function uz_plugin_setting() {
        
        $amazonLocale_array = uz_get_amazon_store();
        
        if( isset($_POST['updatezon_plugin_submit'] ) ) :
        
        $options = get_option( 'updatezon_plugin_settings', array() );

        $options['amazon_associate_tag'] = $_POST['amazon_associate_tag'];
        $options['amazon_access_key_id'] = $_POST['amazon_access_key_id'];
        $options['amazon_secret_access_key'] = $_POST['amazon_secret_access_key'];
		$options['amazon_local_store'] = $_POST['amazon_local_store'];	        
		$options['amazon_color'] = $_POST['amazon_color'];		
        $options['google_short_api'] = $_POST['google_short_api'];     
		$options['espinner_key_api'] = $_POST['espinner_key_api'];
		$options['espinner_key_email'] = $_POST['espinner_key_email'];		
		$options['espiner_active'] = $_POST['espiner_active'];				
		$options['currency'] = $_POST['currency'];
		$options['position'] = $_POST['position'];
		$options['content'] = $_POST['content'];
		$options['presentation'] = $_POST['presentation'];
		$options['positionUzon'] = $_POST['positionUzon'];		
		$options['height'] = $_POST['height'];	
		$options['amazon_button'] = $_POST['amazon_button'];
		
		update_option( 'updatezon_plugin_settings', $options);
		uz_update_price();
?>

    <div id="message" class="updated fade">

      <p>

        <strong>

          Cambios guardados

        </strong>

      </p>

    </div>



<?php

      endif;

  
 echo '
 
 <div class="wpseo_content_cell" id="wpseo_content_top">
        <div class="metabox-holder">
        <h1>UpdateZon - Ajustes</h1>
        <div class="separador"></div>
        <h2>Program Settings</h2>
        <form method="post">';
        settings_fields('updatezon_plugin_settings');
        do_settings_sections('updatezon_plugin_settings');
        $updatezon_settings   	= get_option('updatezon_plugin_settings');
		
		if (empty($updatezon_settings['height'])){
		$updatezon_settings['height'] = 250;
		}
		
		    echo '
       
                    <div class="controls">
                    <div>
                       <div class="uzonsection">
                            <div>
                            <h4 class="width20 inline">Tienda Amazon</h4>
                                <select class="inline" name="amazon_local_store">
                                    <optgroup label="Selecciona tu tienda">
                                    ';
                                    foreach($amazonLocale_array as $key => $value):
                                    echo '<option value="'.$value.'"'; 
                                    if( $updatezon_settings['amazon_local_store']===$value ) 
                                    echo 'selected';echo'>'.$value.'</option>'; 
                                    endforeach; echo '
                                    </optgroup>
                                </select>
                            </div>
                            <div>                            
                            <h4 class="width20 inline"><a href="https://affiliate-program.amazon.com/">Amazon Associate Tag (*)</a></h4>
                                <div class="width60 inline">
                                    <div class="controls">
                                        <input id="amazon_associate_tag" class="of-input" name="amazon_associate_tag" type="text" value="'. $updatezon_settings['amazon_associate_tag'] .'">
                                    </div>
                                </div>    
                            </div>
                            <div>
                            <h4 class="width20 inline"><a href="https://affiliate-program.amazon.com/">Amazon Access Key ID (*)</a></h4>
                                 <div class="width60 inline">
                                    <div class="controls">
                                        <input id="amazon_access_key_id" class="of-input" name="amazon_access_key_id" type="text" value="'. $updatezon_settings['amazon_access_key_id'] .'">
                                    </div>
                                </div>
                            </div>
                            <div>
                            <h4 class="width20 inline"><a href="https://affiliate-program.amazon.com/">Amazon Secret Key ID (*)</a></h4>
                                 <div class="width60 inline">
                                    <div class="controls">
                                        <input id="amazon_secret_access_key" class="of-input" name="amazon_secret_access_key" type="text" value="'. $updatezon_settings['amazon_secret_access_key'] .'">
                                    </div>
                                </div>
                            </div>    
                            <div>
                            <h4 class="width20 inline"><a href="https://console.developers.google.com/">Google Shortener URL API (**)</a></h4>
                                 <div class="width60 inline">
                                    <div class="controls">
                                        <input id="google_short_api" class="of-input" name="google_short_api" type="text" value="'. $updatezon_settings['google_short_api'] .'">
                                    </div>
                                </div>
                            </div>   
							<div>
                            <h4 class="width20 inline"><a href="http://espinner.net/">ESPinner Key</a></h4>
                                 <div class="width60 inline">
                                    <div class="controls">
                                        <input id="espinner_key_api" class="of-input" name="espinner_key_api" type="text" value="'. $updatezon_settings['espinner_key_api'] .'">
                                    </div>
                                </div>
                            </div>
							<div>
                            <h4 class="width20 inline"><a href="http://espinner.net/">Email ESPinner</a></h4>
                                 <div class="width60 inline">
                                    <div class="controls">
                                        <input id="espinner_key_email" class="of-input" name="espinner_key_email" type="text" value="'. $updatezon_settings['espinner_key_email'] .'">
                                    </div>
                                </div>
                            </div> 
							<div>
							<em>(*)  Obligatorio</em><br/>
							<em>(**) Recomendado para ver nº clicks</em>
							</div>
							<div class="separador"></div>
                       </div>  
                             
                            </div>
							<div class="uzoncurrency">
							<div class="uzonwidth400">
							<h4 class="heading">Selecciona el formato de presentación de los productos<br/><em>Podrás cambiarlo individualmente editando la entrada</em></h4></div>
							<div class="inputcurrency"><select name="presentation">
							<optgroup label="Select el formato">
							<option value="list"'; 
							if( $updatezon_settings['presentation']==='list' ) 
							echo 'selected';echo'>Horizontal</option>
                            <option value="grid"'; 
							if( $updatezon_settings['presentation']==='grid' ) 
							echo 'selected';echo'>Vertical</option>
                            </optgroup>
							</select>    
                            </div>
							</div>		
							
							<div class="uzoncurrency">
							<div class="uzonwidth400">
							<h4 class="heading">Selecciona la posición de la información de los productos<br/><em>Podrás cambiarlo individualmente editando la entrada</em></h4></div>
							<div class="inputcurrency"><select name="positionUzon">
							<optgroup label="Select la posición">
							<option value="before"'; 
							if( $updatezon_settings['positionUzon']==='before' ) 
							echo 'selected';echo'>Al principio de la publicación</option>
                            <option value="after"'; 
							if( $updatezon_settings['positionUzon']==='after' ) 
							echo 'selected';echo'>Al final de la publicación</option>
							<option value="both"'; 
							if( $updatezon_settings['positionUzon']==='both' ) 
							echo 'selected';echo'>En ambos sitios</option>
                            </optgroup>
							</select>    
                            </div>
							</div>	
							
							<div class="uzoncurrency">
							<h4 class="heading inline" style="width:400px">Las entradas de cada producto deben contener: </h4>
							'; 
							$content = $updatezon_settings['content'];
							$checkedDescription  = "";
							$checkedImages		 = "";
							
							if (empty($content)){
								$checkedDescription = "checked";
								$checkedImages = "checked";
							}							
							if (in_array("description",$content)){
								$checkedDescription = "checked";
							}
							if (in_array("images",$content)){
								$checkedImages = "checked";
							}
							echo '
							<input type="checkbox" id="content[]" class="of-input inline" name="content[]" value="description" '.$checkedDescription.'>Descripción
							<input type="checkbox" id="content[]" class="of-input inline" name="content[]" value="images" '.$checkedImages.'>Imágenes							
							</div>
							
							<div class="uzoncurrency">
							<h4 class="heading"><em>Spinnear</em> texto. Necesitarás una clave de <a href="http://espinner.net/">ESPinner</a></h4>
							<input id="espiner_active" class="of-input" name="espiner_active" type="checkbox" value="active" ';
							if($updatezon_settings['espiner_active']==='active' ) 
							echo 'checked="checked"';echo'
							/>Sí, <em>spinneame</em> el texto.
							</div>
                            <div class="uzoncurrency">
							<h4 class="heading">Convierte el botón de "Compra ahora" por "Añadir al carrito" (duración de la cookie de 90 días)</h4>
							<input id="amazon_button" class="of-input" name="amazon_button" type="checkbox" value="Add-to-cart" ';
							if($updatezon_settings['amazon_button']==='Add-to-cart' ) 
							echo 'checked="checked"';echo'
							/>Botón "Añadir al carrito"
							</div>
                            <div class="uzoncurrency">
							<div class="textposition"><h4 class="heading">Selecciona la moneda</h4></div>
							<div class="inputcurrency"><select name="currency">
							<optgroup label="Select la moneda">
							';
							$currency = uz_get_concurrency();							
                            foreach($currency as $value):
                            echo '<option value="'.$value.'"'; 
							if( $updatezon_settings['currency']===$value ) 
							echo 'selected';echo'>'.$value.'</option>'; 
                            endforeach; echo '
							</optgroup>
							</select>    
                            </div>
							</div>
							<div class="uzoncurrency">
							<div class="textposition"><h4 class="heading">Elige la posición del símbolo de moneda</h4></div>
							<div class="inputcurrency"><select name="position">
							<optgroup label="Select la posición">
							<option value="0"'; 
							if( $updatezon_settings['position']==='0' ) 
							echo 'selected';echo'>Antes del precio</option>
                            <option value="1"'; 
							if( $updatezon_settings['position']==='1' ) 
							echo 'selected';echo'>Después del precio</option>
                            </optgroup>
							</select>    
                            </div>
							</div>                            
							<div class="uzoncurrency">							
							<div>
							<div class="textposition"><h4 class="heading">Selecciona la altura del gráfico de precios:</h4></div>
							<div class="inputsize"><input type="text" class="heightgraph" name="height" id="height" value="'.$updatezon_settings['height'].'">px
							</div>
							</div>			
							</div>
                            <div class="uzoncurrency">
                            <div class="textposition"><h4 class="heading">Selecciona el color del gráfico:</h4></div>
							<div class="inputgraph"><input type="text" class="colorpicker" name="amazon_color" id="amazon_color" value="'.$updatezon_settings['amazon_color'].'">
							</div>
                        </div>
					</div>
               
            <div class="save">
			<p class="submit">
            <input type="submit" name="updatezon_plugin_submit" class="button button-primary" value="Guardar cambios" />			
            </p>
			</div>
        </form>
       </div>
    </div>
    ';

     register_setting( 'updatezon_plugin_settings', 'amazon_associate_tag' );
     register_setting( 'updatezon_plugin_settings', 'amazon_access_key_id' );
     register_setting( 'updatezon_plugin_settings', 'amazon_secret_access_key' );
     register_setting( 'updatezon_plugin_settings', 'amazon_local_store' );	
	 register_setting( 'updatezon_plugin_settings', 'amazon_color' );									
     register_setting( 'updatezon_plugin_settings', 'google_short_api');
	 register_setting( 'updatezon_plugin_settings', 'espinner_key_api');	 
	 register_setting( 'updatezon_plugin_settings', 'espinner_key_email');	 	 
	 register_setting( 'updatezon_plugin_settings', 'espiner_active');	 	 	 
     register_setting( 'updatezon_plugin_settings', 'currency' );		
	 register_setting( 'updatezon_plugin_settings', 'position' );		
	 register_setting( 'updatezon_plugin_settings', 'presentation' );	
	 register_setting( 'updatezon_plugin_settings', 'positionUzon' );		 
	 register_setting( 'updatezon_plugin_settings', 'height' );			
	 register_setting( 'updatezon_plugin_settings', 'content' );				 
	 register_setting( 'updatezon_plugin_settings', 'amazon_button' );  
	 
  }


?>