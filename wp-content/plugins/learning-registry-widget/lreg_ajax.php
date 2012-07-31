<?PHP
		
	/**
	 * Add actions for both logged in and not logged in users
	 */
	add_action('wp_ajax_nopriv_lreg_search', 'lreg_get');
	add_action('wp_ajax_lreg_search', 'lreg_get');

	function nsdl_dc($data, $link){
	
		$xml = new SimpleXMLElement(stripslashes($data));
		$set = $xml->getNamespaces(true);
		$ns_dc = $xml->children($set['dc']); 
		return "<li><a target='_blank' href='" . $link . "'>" . $ns_dc->title . "</a></li>";		
			
	}
	
	function lreg_get() {
	
		if(strpos($_POST['node_url'],"http")!==FALSE){

			$ch = curl_init();
			curl_setopt($ch,CURLOPT_URL,$_POST['node_url'] . "slice?any_tags=" . $_POST['term']);
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
			curl_setopt($ch,CURLOPT_MAXREDIRS,10);
			curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,100);
			curl_setopt($ch,CURLOPT_HTTP_VERSION,'CURLOPT_HTTP_VERSION_1_1');
			curl_setopt($ch,CURLOPT_PORT,443);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			$data = curl_exec($ch);
						
			$process = json_decode($data);
			
			if($process){
			
				$returned_data = "<p>Related Content</p><ul class='learning_registry'>";
				
				$counter = 0;
					
				foreach($process->documents as $documents){
				
					if($counter!=$_POST['max']){
							
						switch($documents->resource_data_description->payload_schema[0]){
					
							case "nsdl_dc":
							case "NSDL DC 1.02.020": $returned_data .= nsdl_dc($documents->resource_data_description->resource_data,$documents->resource_data_description->resource_locator); break;
							default: break;

						}
						
						$counter++;
						
					}else{
					
						break;
					
					}
				
				}		
				
				echo $returned_data . "</ul>";
			
			}
		
		}
		
		die(); // this is required to return a proper result
		
	}
	
?>