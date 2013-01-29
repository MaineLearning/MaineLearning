<?php
/*
Plugin Name: Children Index Shortcode
Plugin URI: http://MyWebsiteAdvisor.com/tools/wordpress-plugins/children-index-shortcode/
Description: Shortcode Creates an Index of the child pages.
Version: 1.1
Author: MyWebsiteAdvisor
Author URI: http://MyWebsiteAdvisor.com
*/

/*
Children Index Shortcode (Wordpress Plugin)
Copyright (C) 2011 MyWebsiteAdvisor
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


//tell wordpress to register the children_index shortcode
add_shortcode("children-index", "sc_show_children_index");

function sc_show_children_index($atts, $content = null){
		
	if(isset($atts['id']) && is_numeric($atts['id'])){
		//id specified by shortcode attribute
		$parent_id = $atts['id'];
	}else{
		//get the id of the current article that is calling the shortcode
		$parent_id = get_the_ID();
	}
	
	$output = "";
	
	$i = 0;
	$args = array(
		'post_parent' => $parent_id,
		'post_type' => 'page'
	);
	
	if(isset($atts['order'])) $args['order'] = $atts['order'];
	if(isset($atts['orderby'])) $args['orderby'] = $atts['orderby'];
	
	if ( $children = get_children($args))
	{
		$output .= "<ul>";
		foreach( $children as $child ) {
			$title = $child->post_title;
			$link = get_permalink($child->ID);	
 			
			$output .= '<li><a href="'.$link.'" >'.$title.'</a></li>' . "\n";
			
			if ( $grandchildren = get_children(array(
				'post_parent' => $child->ID,
				'order' => 'ASC',
				'post_type' => 'page')))
			{
				$output .= '<ul>' . "\n";
				foreach( $grandchildren as $grandchild ) {
					$title = $grandchild->post_title;
					$link = get_permalink($grandchild->ID);	
					
					$output .= '<li><a href="'.$link.'" >'.$title.'</a></li>' . "\n";
				}
				$output .= '</ul>' . "\n";
			} 
		}
		$output .= "</ul>";
	} 

	return  $output;
}


?>