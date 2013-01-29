<?php
/*
Plugin Name: Children Excerpt Shortcode
Plugin URI: http://MyWebsiteAdvisor.com/tools/wordpress-plugins/children-excerpt-shortcode/
Description: Shortcode creates an index-like list of child pages displaying exceprts of each page
Version: 1.2
Author: MyWebsiteAdvisor
Author URI: http://MyWebsiteAdvisor.com
*/

/*
Children Excerpt Shortcode (Wordpress Plugin)
Copyright (C) 2011 MyWebsiteAdvisor.com
Contact us at http://MyWebsiteAdvisor.com

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/



//tell wordpress to register the children-excerpt shortcode
add_shortcode("children-excerpt", "sc_show_children_excerpt");


//children excerpt shortcode worker function
function sc_show_children_excerpt($atts, $content = null){
	if($atts['length'] > 0 ){
		//maybe set a minimum length here

	}else{
		$atts['length'] = 50;
	}


	if(isset($atts['id']) && is_numeric($atts['id'])){
		//id specified by shortcode attribute
		$parent_id = $atts['id'];
	}else{
		//get the id of the current article that is calling the shortcode
		$parent_id = get_the_ID();
	}
	
	
	$output = "";
	
	$i = 0;
	
	if ( $children = get_children(array(
		'post_parent' => $parent_id,
		'post_type' => 'page')))
	{
		foreach( $children as $child ) {
			$title = $child->post_title;

			$child_excerpt = apply_filters('the_excerpt', $child->post_content);
			
			//split excerpt into array for processing
			$words = explode(' ', $child_excerpt);
			
			//chop off the excerpt based on the atts->lenth
			$words = array_slice($words, 0, $atts['length']);
			
			//merge the array of words for the excerpt back into sentances
			$child_excerpt = implode(' ', $words);

			$link = get_permalink($child->ID);
				
 			$output .= "<div>";
			$output .= "<a href='$link'><h1>$title</h1></a>";
			$output .= "<p>". $child_excerpt ."...</p>";
			$output .= "<a href='$link'>Read More!</a>";
			$output .= "</div>";
			$output .= "<hr>";
		}
	} 
	
	return $output;

}

?>