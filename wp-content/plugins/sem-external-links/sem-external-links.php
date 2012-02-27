<?php
/*
Plugin Name: External Links
Plugin URI: http://www.semiologic.com/software/external-links/
Description: Marks outbound links as such, with various effects that are configurable under <a href="options-general.php?page=external-links">Settings / External Links</a>.
Version: 4.0.6
Author: Denis de Bernardy
Author URI: http://www.getsemiologic.com
Text Domain: external-links
Domain Path: /lang
*/

/*
Terms of use
------------

This software is copyright Mesoconcepts (http://www.mesoconcepts.com), and is distributed under the terms of the GPL license, v.2.

http://www.opensource.org/licenses/gpl-2.0.php
**/


load_plugin_textdomain('external-links', false, dirname(plugin_basename(__FILE__)) . '/lang');


/**
 * external_links
 *
 * @package External Links
 **/

class external_links {
	/**
	 * styles()
	 *
	 * @return void
	 **/

	function styles() {
		$folder = plugin_dir_url(__FILE__);
		wp_enqueue_style('external-links', $folder . 'sem-external-links.css', null, '20090903');
	} # styles()
	
	
	/**
	 * filter()
	 *
	 * @param array $anchor
	 * @return array $anchor
	 **/

	function filter($anchor) {
		# disable in feeds
		if ( is_feed() )
			return $anchor;
		
		# ignore local urls
		if ( external_links::is_local_url($anchor['attr']['href']) )
			return $anchor;
		
		# no icons for images
		$is_image = (bool) preg_match("/^\s*<\s*img\s.+?>\s*$/is", $anchor['body']);
		
		$o = external_links::get_options();
		
		if ( !in_array('external', $anchor['attr']['class']) )
			$anchor['attr']['class'][] = 'external';
		
		if ( !$is_image && $o['icon'] && !in_array('external_icon', $anchor['attr']['class'])
			&& !in_array('no_icon', $anchor['attr']['class'])
			&& !in_array('noicon', $anchor['attr']['class']) )
			$anchor['attr']['class'][] = 'external_icon';
		
		if ( $o['nofollow'] && !function_exists('strip_nofollow')
			&& !in_array('nofollow', $anchor['attr']['rel'])
			&& !in_array('follow', $anchor['attr']['rel']) )
			$anchor['attr']['rel'][] = 'nofollow';
		
		if ( $o['target'] && empty($anchor['attr']['target']) )
		 	$anchor['attr']['target'] = '_blank';
		
		return $anchor;
	} # filter()
	
	
	/**
	 * is_local_url()
	 *
	 * @param string $url
	 * @return bool $is_local_url
	 **/

	function is_local_url($url) {
		if ( in_array(substr($url, 0, 1), array('?', '#')) || strpos($url, '://') === false )
			return true;
		elseif ( preg_match("~/go(/|\.)~i", $url) )
			return false;
		
		static $site_domain;
		
		if ( !isset($site_domain) ) {
			$site_domain = get_option('home');
			$site_domain = parse_url($site_domain);
			$site_domain = $site_domain['host'];
			$site_domain = preg_replace("/^www\./i", '', $site_domain);
			
			# The following is not bullet proof, but it's good enough for a WP site
			if ( $site_domain != 'localhost' && !preg_match("/\d+(\.\d+){3}/", $site_domain) ) {
				if ( preg_match("/\.([^.]+)$/", $site_domain, $tld) ) {
					$tld = end($tld);
				} else {
					$site_domain = false;
					return false;
				}
				
				$site_domain = substr($site_domain, 0, strlen($site_domain) - 1 - strlen($tld));
				
				if ( preg_match("/\.([^.]+)$/", $site_domain, $subtld) ) {
					$subtld = end($subtld);
					if ( strlen($subtld) <= 4 ) {
						$site_domain = substr($site_domain, 0, strlen($site_domain) - 1 - strlen($subtld));
						$site_domain = explode('.', $site_domain);
						$site_domain = array_pop($site_domain);
						$site_domain .= ".$subtld";
					} else {
						$site_domain = $subtld;
					}
				}
				
				$site_domain .= ".$tld";
			}
			
			$site_domain = strtolower($site_domain);
		}
		
		if ( !$site_domain )
			return false;
		
		$link_domain = parse_url($url);
		$link_domain = $link_domain['host'];
		$link_domain = preg_replace("/^www\./i", '', $link_domain);
		$link_domain = strtolower($link_domain);
		
		if ( $site_domain == $link_domain ) {
			return true;
		} elseif ( function_exists('is_multisite') && is_multisite() ) {
			return false;
		} else {
			$site_elts = explode('.', $site_domain);
			$link_elts = explode('.', $link_domain);
			
			while ( ( $site_elt = array_pop($site_elts) ) && ( $link_elt = array_pop($link_elts) ) ) {
				if ( $site_elt !== $link_elt )
					return false;
			}
			
			return empty($link_elts) || empty($site_elts);
		}
	} # is_local_url()
	
	
	/**
	 * get_options
	 *
	 * @return array $options
	 **/

	function get_options() {
		static $o;
		
		if ( !is_admin() && isset($o) )
			return $o;
		
		$o = get_option('external_links');
		
		if ( $o === false )
			$o = external_links::init_options();
		
		return $o;
	} # get_options()
	
	
	/**
	 * init_options()
	 *
	 * @return array $options
	 **/

	function init_options() {
		$o = get_option('sem_external_links_params');
		
		if ( $o !== false )
			delete_option('sem_external_links_params');
		
		$o = wp_parse_args($o, array(
			'global' => true,
			'icon' => true,
			'target' => false,
			'nofollow' => false,
			));
		
		update_option('external_links', $o);
		
		return $o;
	} # init_options()
	
	
	/**
	 * admin_menu()
	 *
	 * @return void
	 **/
	
	function admin_menu() {
		add_options_page(
			__('External Links', 'external-links'),
			__('External Links', 'external-links'),
			'manage_options',
			'external-links',
			array('external_links_admin', 'edit_options')
			);
	} # admin_menu()
} # external_links


function external_links_admin() {
	include_once dirname(__FILE__) . '/sem-external-links-admin.php';
}

add_action('load-settings_page_external-links', 'external_links_admin');


if ( !is_admin() ) {
	if ( !class_exists('anchor_utils') )
		include dirname(__FILE__) . '/anchor-utils/anchor-utils.php';
	
	$o = external_links::get_options();
	
	if ( $o['icon'] )
		add_action('wp_print_styles', array('external_links', 'styles'), 5);
	
	add_filter(($o['global'] ? 'ob_' : '' ) . 'filter_anchor', array('external_links', 'filter'));
	
	unset($o);
} else {
	add_action('admin_menu', array('external_links', 'admin_menu'));
}
?>