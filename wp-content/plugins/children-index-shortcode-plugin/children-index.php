<?php
/*
Plugin Name: Children Index Shortcode
Plugin URI: http://MyWebsiteAdvisor.com
Description: Children Index Shortcode Plugin
Version: 1.0
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
		
	$parent_id = get_the_ID();
	$output = "";
	
	$i = 0;
	
	if ( $children = get_children(array(
		'post_parent' => $parent_id,
		'post_type' => 'page')))
	{
		foreach( $children as $child ) {
			$title = $child->post_title;
			$link = get_permalink($child->ID);	
 			
			$output .= '<li><a href="'.$link.'" >'.$title.'</a></li>' . "\n";
			
			if ( $grandchildren = get_children(array(
				'post_parent' => $child->ID,
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
	} 

	return  $output;
}


?>