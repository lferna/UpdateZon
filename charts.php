<?php

//scripts
wp_register_script('scriptUzon', plugins_url('js/script.js', __FILE__));
wp_enqueue_script( 'scriptUzon');

wp_register_script('d3MinUzon', 'https://cdnjs.cloudflare.com/ajax/libs/d3/3.5.2/d3.min.js');
wp_enqueue_script( 'd3MinUzon');

wp_register_script('d3Uzon',  plugins_url('js/nv.d3.js', __FILE__));
wp_enqueue_script( 'd3Uzon');

//styles charts
wp_register_style('d3', plugins_url('css/nv.d3.css', __FILE__));
wp_enqueue_style( 'd3');

wp_enqueue_style( 'wp-color-picker' );
wp_enqueue_script( 'color_picker', plugins_url('js/color_picker.js', __FILE__ ), array( 'wp-color-picker' ), false, true );


function wp_updatezon_chart($chart) {
    
    $values;
    $updatezon_settings   	= get_option('updatezon_plugin_settings');
	$store = $updatezon_settings['amazon_local_store'];
	$color;
    $results="return[";
    
	
	global $wpdb;
	$table_name_prices  = $wpdb->prefix . 'uz_prices';	
 
	$charts = explode(',',$chart);
	
	$array_chart="";		
			foreach($charts as $key => $chart){						
				
				$rows="";
				$row="";
				$values="";
				
				$color    	= $updatezon_settings['amazon_color'];		
				
				$query = "SELECT price,date FROM $table_name_prices  WHERE item_id = '$chart' order by date desc";
				
				$rows = $wpdb->get_results($query);
				$begin=$array_chart.'{key:"'.$store.'",values:[';
				
				
				$final="";
				if ($key<sizeof($charts)-1){
				$final = ",";
				}
				
				
				foreach ($rows as $clave=> $row){								
				$coma="";
				if ($clave<sizeof($rows)-1){
				$coma = ",";
				}else{
				$coma = "]";
				}				
					$mili = strtotime($row->date)*1000;                
					$values = $values.'['. $mili .' , '. $row->price.'] '.$coma; 
				}
			
				$end=',color:"'.$color.'"}'.$final;
				$array_chart = $begin.$values.$end;				
		} 
		
		$results =$results . $array_chart . "];";	

	$height = $updatezon_settings['height'];
	

	if (empty($height)){
		$updatezon_settings['height'] = 200;
	}
	
	$chart = '

    <div id="chart">
    <svg></svg>
    </div>
   <style>
   
   #chart, svg {
            margin: 0px;
            padding: 0px;
			float:left;
            width: 100%;
            height: '.$height.'px;
        }
</style>   
  <script>
     nv.addGraph(function() {
        chart = nv.models.lineChart()
            .x(function(d) { return d[0] })
            .y(function(d) { return d[1] })
            .options({
                transitionDuration: 300,
                //useInteractiveGuideline: true
            })
        ;


           chart.xAxis.tickFormat(function(d) {              
            return d3.time.format("%m/%d/%y")(new Date(d))
        });

        
         chart.yAxis.tickFormat(function(d) { return d3.format(",f")(d) });

        
        
        d3.select("#chart svg")
            .datum(cumulativeTestData())
            .call(chart);

        //TODO: Figure out a good way to do this automatically
        nv.utils.windowResize(chart.update);

        chart.dispatch.on("stateChange", function(e) { nv.log("New State:", JSON.stringify(e)); });
        

        return chart;
    });
    
        function cumulativeTestData() {
		'.$results.'
		}
    
    </script>
';

    return $chart;
}


?>