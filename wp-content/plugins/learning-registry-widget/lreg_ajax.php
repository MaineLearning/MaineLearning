<?php

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
        $post_id = isset( $_POST['post_id'] ) ? (int) $_POST['post_id'] : 0;
        $number_items = isset( $_POST['number_items'] ) ? (int) $_POST['number_items'] : 0;
        $url_field = isset( $_POST['url_field'] ) ? $_POST['url_field'] : '';

        if ( !$post_id || !$url_field ) {
                return;
        }

        // Pull up the postmeta
        $resource_url = get_post_meta( $post_id, $url_field, true );

        // If there's no data, don't show the widget
        if ( empty( $resource_url ) ) {
                die();
        }

        // Get the node URL
        // @todo Don't hardcode me, bro
        $node = 'https://node01.public.learningregistry.net/';

        $node_action = 'obtain';

        $args = array();

        $request_uri = add_query_arg(
                array(
                        'request_id' => urlencode( $resource_url )
                ),
                $node . $node_action
        );
        $response = wp_remote_get( $request_uri, $args );

        $paradata = array();

        $darray = json_decode( $response['body'] );

        foreach( $darray->documents as $doc ) {
                foreach( $doc->document as $d ) {
                        if ( 'paradata' == $d->resource_data_type ) {
                                $data_obj = is_object( $d->resource_data ) ? $d->resource_data : json_decode( $d->resource_data );
                                $paradata[] = $data_obj;
                        }
                }
        }
//print_r( $paradata );
        $markup = '';
        if ( !empty( $paradata ) ) {
		$counter = 0;
                $markup .= '<ul>';
                foreach( $paradata as $pd ) {

			if ( $counter > $number_items ) {
				break;
			}

			// Sometimes things are embedded in Activity
			if ( isset( $pd->activity ) ) {
				$item = $pd->activity;
			} else {
				$item = $pd;
			}

			// Isolate content, then cut it at the verb
			if ( ! empty( $item->content ) ) {
				$content = $item->content;
			} else {
				continue;
			}

			if ( ! empty( $item->verb->action ) ) {
				$content = ucwords( $item->verb->action ) . ' ' . array_pop( explode( $item->verb->action, $content ) );
			} else {
				continue;
			}

			// Lose the Date stuff
			$content = preg_replace( '/(on|between).*$/', '', $content );

			// Wrap the content in a span to make it easier to style
			$content = '<span class="paradata-description">' . $content . '</span>';

			// Make sure we don't include items that have been done 0 times
			if ( isset( $item->verb->measure ) && 'count' == $item->verb->measure->measureType && empty( $item->verb->measure->value ) ) {
				continue;
			}

			// Metadata
			$metadata = '<div class="paradata-meta">';

			// Get the "description" if we have it
			if ( isset( $item->verb->context ) && isset( $item->verb->context->description ) ) {
				$url = isset( $item->verb->context->id ) ? $item->verb->context->id : '';

				$metadata .= '<span class="paradata-id">';

				if ( $url )
					$metadata .= '<a href="' . $url . '">';

				$metadata .= $item->verb->context->description;

				if ( $url )
					$metadata .= '</a>';

				$metadata .= '</span> ';
			}

			// Date - take start dates
			$date = strtotime( array_pop( array_reverse( explode( '/', $item->verb->date ) ) ) );
			if ( $date )
				$metadata .= '<span class="paradata-date">' . date( 'F j, Y', $date ) . '</span>';
			$metadata .= '</div>';
			$image = '';

                        $markup .= '<li>' . $content . $metadata . '</li>';

			$counter++;
                }
                $markup .= '</ul>';
        }
echo $markup;
die();


echo $resource_url; die();
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
