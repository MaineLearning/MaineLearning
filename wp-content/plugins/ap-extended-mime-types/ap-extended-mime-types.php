<?php
/*
	Plugin Name: AP Extended Mime Types
	Plugin URI: http://ardentpixels.com/josh/wordpress/plugins/ap-extended-mime-types/
	Description: Extends the allowed uploadable MIME types to include a WIDE range of file types. 
	Version: 1.1
	Author: Josh Maxwell (Ardent Pixels)
	Author URI: http://ardentpixels.com/josh
	License: GPL2
*/

/*  Copyright 2011 Josh Maxwell & Ardent Pixels

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// Add the filter
add_filter('upload_mimes', 'ap_extended_mime_types');

// Function to add mime types
function ap_extended_mime_types ( $mime_types=array() ) {

	// add your extension & app info to mime-types.txt in this format
	//	doc,doct application/msword
	//	pdf application/pdf
	//	etc...
	$file = '/ap-extended-mime-types/mime-types.txt';
	$file = plugins_url() . $file;
	$mime_file_lines = file($file);

	foreach ($mime_file_lines as $line) {
		//Catch all sorts of line endings - CR/CRLF/LF
		$mime_type = explode(' ',rtrim(rtrim($line,"\n"),"\r"));
		$mime_types[$mime_type[0]] = $mime_type[1];
	}

	// add as many as you like
	return $mime_types;
}

?>