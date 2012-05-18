<?php
/*
Plugin Name: Search Everything
Plugin URI: https://github.com/sproutventure/search-everything-wordpress-plugin/
Description: Adds search functionality without modifying any template pages: Activate, Configure and Search. Options Include: search highlight, search pages, excerpts, attachments, drafts, comments, tags and custom fields (metadata). Also offers the ability to exclude specific pages and posts. Does not search password-protected content.
Version: 6.9.3.1
Author: Dan Cameron of Sprout Venture
Author URI: http://sproutventure.com/
*/

/*
 This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, version 2.

 This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 */

if ( !defined('WP_CONTENT_DIR') )
define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );

if (!defined('DIRECTORY_SEPARATOR'))
{
	if (strpos(php_uname('s'), 'Win') !== false )
	define('DIRECTORY_SEPARATOR', '\\');
	else
	define('DIRECTORY_SEPARATOR', '/');
}
define('SE_ABSPATH', dirname(__FILE__) . DIRECTORY_SEPARATOR);

$SE = new SearchEverything();
//add filters based upon option settings

Class SearchEverything {

	var $logging = false;
	var $options;
	var $wp_ver23;
	var $wp_ver25;
	var $wp_ver28;

	function SearchEverything(){
		global $wp_version;
		$this->wp_ver23 = ($wp_version >= '2.3');
		$this->wp_ver25 = ($wp_version >= '2.5');
		$this->wp_ver28 = ($wp_version >= '2.8');
		$this->options = get_option('se_options');

		if (is_admin()) {
			include ( SE_ABSPATH  . 'views/options.php' );
			$SEAdmin = new se_admin();
		}
		//add filters based upon option settings
		if ("Yes" == $this->options['se_use_tag_search'] || "Yes" == $this->options['se_use_category_search'] || "Yes" == $this->options['se_use_tax_search'])
		{
			add_filter('posts_join', array(&$this, 'se_terms_join'));
			if ("Yes" == $this->options['se_use_tag_search'])
				{
					$this->se_log("searching tags");
				}
			if ("Yes" == $this->options['se_use_category_search'])
				{
					$this->se_log("searching categories");
				}
			if ("Yes" == $this->options['se_use_tax_search'])
				{
					$this->se_log("searching custom taxonomies");
				}
		}

		if ("Yes" == $this->options['se_use_page_search'])
		{
			add_filter('posts_where', array(&$this, 'se_search_pages'));
			$this->se_log("searching pages");
		}

		if ("Yes" == $this->options['se_use_excerpt_search'])
		{
			$this->se_log("searching excerpts");
		}

		if ("Yes" == $this->options['se_use_comment_search'])
		{
			add_filter('posts_join', array(&$this, 'se_comments_join'));
			$this->se_log("searching comments");
			// Highlight content
			if("Yes" == $this->options['se_use_highlight'])
			{
				add_filter('comment_text', array(&$this,'se_postfilter'));
			}
		}

		if ("Yes" == $this->options['se_use_draft_search'])
		{
			add_filter('posts_where', array(&$this, 'se_search_draft_posts'));
			$this->se_log("searching drafts");
		}

		if ("Yes" == $this->options['se_use_attachment_search'])
		{
			add_filter('posts_where', array(&$this, 'se_search_attachments'));
			$this->se_log("searching attachments");
		}

		if ("Yes" == $this->options['se_use_metadata_search'])
		{
			add_filter('posts_join', array(&$this, 'se_search_metadata_join'));
			$this->se_log("searching metadata");
		}



		if ($this->options['se_exclude_posts_list'] != '')
		{
			$this->se_log("searching excluding posts");
		}

		if ($this->options['se_exclude_categories_list'] != '')
		{
			add_filter('posts_join', array(&$this, 'se_exclude_categories_join'));
			$this->se_log("searching excluding categories");
		}

		if ("Yes" == $this->options['se_use_authors'])
		{

			add_filter('posts_join', array(&$this, 'se_search_authors_join'));
			$this->se_log("searching authors");
		}

		add_filter('posts_search', array(&$this, 'se_search_where'), 10, 2);

		add_filter('posts_where', array(&$this, 'se_no_revisions'));

		add_filter('posts_request', array(&$this, 'se_distinct'));

		add_filter('posts_where', array(&$this, 'se_no_future'));

		add_filter('posts_request', array(&$this, 'se_log_query'), 10, 2);

		// Highlight content
		if("Yes" == $this->options['se_use_highlight'])
		{
            add_filter('the_content', array(&$this,'se_postfilter'), 11);
            add_filter('the_title', array(&$this,'se_postfilter'), 11);
            add_filter('the_excerpt', array(&$this,'se_postfilter'), 11);
		}
	}


	// creates the list of search keywords from the 's' parameters.
	function se_get_search_terms()
	{
		global $wp_query, $wpdb;
		$s = isset($wp_query->query_vars['s']) ? $wp_query->query_vars['s'] : '';
		$sentence = isset($wp_query->query_vars['sentence']) ? $wp_query->query_vars['sentence'] : false;
		$search_terms = array();

		if ( !empty($s) )
		{
			// added slashes screw with quote grouping when done early, so done later
			$s = stripslashes($s);
			if ($sentence)
			{
				$search_terms = array($s);
			} else {
				preg_match_all('/".*?("|$)|((?<=[\\s",+])|^)[^\\s",+]+/', $s, $matches);
				$search_terms = array_map(create_function('$a', 'return trim($a, "\\"\'\\n\\r ");'), $matches[0]);
			}
		}
		return $search_terms;
	}

	// add where clause to the search query
	function se_search_where($where, $wp_query){

		if(!$wp_query->is_search())
			return $where;

		global $wpdb;

		$searchQuery = $this->se_search_default();

		//add filters based upon option settings
		if ("Yes" == $this->options['se_use_tag_search'])
		{
			$searchQuery .= $this->se_build_search_tag();
		}
		if ("Yes" == $this->options['se_use_category_search'] || 'Yes' == $this->options['se_use_tax_search'])
		{
			$searchQuery .= $this->se_build_search_categories();
		}
		if ("Yes" == $this->options['se_use_metadata_search'])
		{
			$searchQuery .= $this->se_build_search_metadata();
		}
		if ("Yes" == $this->options['se_use_excerpt_search'])
		{
			$searchQuery .= $this->se_build_search_excerpt();
		}
		if ("Yes" == $this->options['se_use_comment_search'])
		{
			$searchQuery .= $this->se_build_search_comments();
		}
		if ("Yes" == $this->options['se_use_authors'])
		{
			$searchQuery .= $this->se_search_authors();
		}
		if ($searchQuery != '')
		{
			$where = preg_replace('#\(\(\(.*?\)\)\)#', '(('.$searchQuery.'))', $where);

		}
		if ($this->options['se_exclude_posts_list'] != '')
		{
			$where .= $this->se_build_exclude_posts();
		}
		if ($this->options['se_exclude_categories_list'] != '')
		{
			$where .= $this->se_build_exclude_categories();

		}
		$this->se_log("global where: ".$where);
		return $where;
	}
	// search for terms in default locations like title and content
	// replacing the old search terms seems to be the best way to
	// avoid issue with multiple terms
	function se_search_default(){

		global $wp_query, $wpdb;

		$n = (isset($wp_query->query_vars['exact']) && $wp_query->query_vars['exact']) ? '' : '%';
		$search = '';
		$seperator = '';
		$terms = $this->se_get_search_terms();

		// if it's not a sentance add other terms
		$search .= '(';
			foreach($terms as $term){
				$search .= $seperator;


					$search .= sprintf("((%s.post_title LIKE '%s%s%s') OR (%s.post_content LIKE '%s%s%s'))", $wpdb->posts, $n, $term, $n, $wpdb->posts, $n, $term, $n);


				$seperator = ' AND ';
			}

		$search .= ')';
		return $search;
	}

	// Exclude post revisions
	function se_no_revisions($where)
	{
		global $wp_query, $wpdb;
		if (!empty($wp_query->query_vars['s']))
		{
			if(!$this->wp_ver28)
			{
				$where = 'AND (' . substr($where, strpos($where, 'AND')+3) . ") AND $wpdb->posts.post_type != 'revision'";
			}
			$where = ' AND (' . substr($where, strpos($where, 'AND')+3) . ') AND post_type != \'revision\'';
		}
		return $where;
	}

	// Exclude future posts fix provided by Mx
	function se_no_future($where)
	{
		global $wp_query, $wpdb;
		if (!empty($wp_query->query_vars['s']))
		{
			if(!$this->wp_ver28)
			{
				$where = 'AND (' . substr($where, strpos($where, 'AND')+3) . ") AND $wpdb->posts.post_status != 'future'";
			}
				$where = 'AND (' . substr($where, strpos($where, 'AND')+3) . ') AND post_status != \'future\'';
		}
		return $where;
	}

	// Logs search into a file
	function se_log($msg)
	{

		if ($this->logging)
		{
			$fp = fopen( SE_ABSPATH . "logfile.log","a+");
			if ( !$fp )
			{
				echo 'unable to write to log file!';
			}
			$date = date("Y-m-d H:i:s ");
			$source = "search_everything plugin: ";
			fwrite($fp, "\n\n".$date."\n".$source."\n".$msg);
			fclose($fp);
		}
		return true;
	}

	//Duplicate fix provided by Tiago.Pocinho
	function se_distinct($query)
	{
		global $wp_query, $wpdb;
		if (!empty($wp_query->query_vars['s']))
		{
			if (strstr($query, 'DISTINCT'))
			{}
			else
			{
				$query = str_replace('SELECT', 'SELECT DISTINCT', $query);
			}
		}
		return $query;
	}

	//search pages (except password protected pages provided by loops)
	function se_search_pages($where)
	{
		global $wp_query, $wpdb;
		if (!empty($wp_query->query_vars['s']))
		{

			$where = str_replace('"', '\'', $where);
			if ('Yes' == $this->options['se_approved_pages_only'])
			{
				$where = str_replace("post_type = 'post'", " AND 'post_password = '' AND ", $where);
			} else { // < v 2.1
				$where = str_replace('post_type = \'post\' AND ', '', $where);
			}
		}
		$this->se_log("pages where: ".$where);
		return $where;
	}

	// create the search excerpts query
	function se_build_search_excerpt()
	{
		global $wp_query, $wpdb;
		$s = $wp_query->query_vars['s'];
		$search_terms = $this->se_get_search_terms();
		$exact = $wp_query->query_vars['exact'];
		$search = '';

		if ( !empty($search_terms) ) {
			// Building search query
			$n = ($exact) ? '' : '%';
			$searchand = '';
			foreach($search_terms as $term) {
				$term = addslashes_gpc($term);
				$search .= "{$searchand}($wpdb->posts.post_excerpt LIKE '{$n}{$term}{$n}')";
				$searchand = ' AND ';
			}
			$sentence_term = $wpdb->escape($s);
			if (!$sentence && count($search_terms) > 1 && $search_terms[0] != $sentence_term )
			{
				$search = "($search) OR ($wpdb->posts.post_excerpt LIKE '{$n}{$sentence_term}{$n}')";
			}
			if ( !empty($search) )
			$search = " OR ({$search}) ";
		}
		$this->se_log("excerpt where: ".$where);
		return $search;
	}


	//search drafts
	function se_search_draft_posts($where)
	{
		global $wp_query, $wpdb;
		if (!empty($wp_query->query_vars['s']))
		{
			$where = str_replace('"', '\'', $where);
			if(!$this->wp_ver28)
			{
				$where = str_replace(" AND (post_status = 'publish'", " AND ((post_status = 'publish' OR post_status = 'draft')", $where);
			}
			else
			{
				$where = str_replace(" AND ($wpdb->posts.post_status = 'publish'", " AND ($wpdb->posts.post_status = 'publish' OR $wpdb->posts.post_status = 'draft'", $where);
			}
			$where = str_replace(" AND (post_status = 'publish'", " AND (post_status = 'publish' OR post_status = 'draft'", $where);
		}
		$this->se_log("drafts where: ".$where);
		return $where;
	}

	//search attachments
	function se_search_attachments($where)
	{
		global $wp_query, $wpdb;
		if (!empty($wp_query->query_vars['s']))
		{
			$where = str_replace('"', '\'', $where);
			if(!$this->wp_ver28)
			{
				$where = str_replace(" AND (post_status = 'publish'", " AND (post_status = 'publish' OR post_type = 'attachment'", $where);
				$where = str_replace("AND post_type != 'attachment'","",$where);
			}
			else
			{
				$where = str_replace(" AND ($wpdb->posts.post_status = 'publish'", " AND ($wpdb->posts.post_status = 'publish' OR $wpdb->posts.post_type = 'attachment'", $where);
				$where = str_replace("AND $wpdb->posts.post_type != 'attachment'","",$where);
			}
		}
		$this->se_log("attachments where: ".$where);
		return $where;
	}

	// create the comments data query
	function se_build_search_comments()
	{
		global $wp_query, $wpdb;
		$s = $wp_query->query_vars['s'];
		$search_terms = $this->se_get_search_terms();
		$exact = $wp_query->query_vars['exact'];

		if ( !empty($search_terms) ) {
			// Building search query on comments content
			$n = ($exact) ? '' : '%';
			$searchand = '';
			$searchContent = '';
			foreach($search_terms as $term) {
				$term = addslashes_gpc($term);
				if ($this->wp_ver23)
				{
					$searchContent .= "{$searchand}(cmt.comment_content LIKE '{$n}{$term}{$n}')";
				}
				$searchand = ' AND ';
			}
			$sentense_term = $wpdb->escape($s);
			if (!$sentence && count($search_terms) > 1 && $search_terms[0] != $sentense_term )
			{
				if ($this->wp_ver23)
				{
					$searchContent = "($searchContent) OR (cmt.comment_content LIKE '{$n}{$sentense_term}{$n}')";
				}
			}
			$search = $searchContent;
			// Building search query on comments author
			if($this->options['se_use_cmt_authors'] == 'Yes')
			{
				$searchand = '';
				$comment_author = '';
				foreach($search_terms as $term) {
					$term = addslashes_gpc($term);
					if ($this->wp_ver23)
					{
						$comment_author .= "{$searchand}(cmt.comment_author LIKE '{$n}{$term}{$n}')";
					}
					$searchand = ' AND ';
				}
				$sentence_term = $wpdb->escape($s);
				if (!$sentence && count($search_terms) > 1 && $search_terms[0] != $sentence_term )
				{
					if ($this->wp_ver23)
					{
						$comment_author = "($comment_author) OR (cmt.comment_author LIKE '{$n}{$sentence_term}{$n}')";
					}
				}
				$search = "($search) OR ($comment_author)";
			}
			if ('Yes' == $this->options['se_approved_comments_only'])
			{
				$comment_approved = "AND cmt.comment_approved =  '1'";
				$search = "($search) $comment_approved";
			}
			if ( !empty($search) )
			$search = " OR ({$search}) ";
		}
		$this->se_log("comments where: ".$where);
		$this->se_log("comments sql: ".$search);
		return $search;
	}

	// Build the author search
	function se_search_authors()
	{
		global $wp_query, $wpdb;
		$s = $wp_query->query_vars['s'];
		$search_terms = $this->se_get_search_terms();
		$n = (isset($wp_query->query_vars['exact']) && $wp_query->query_vars['exact']) ? '' : '%';
		$search = '';
		$searchand = '';

		if ( !empty($search_terms) ) {
			// Building search query
			foreach($search_terms as $term) {
				$term = addslashes_gpc($term);
				if ($this->wp_ver23)
				{
					$search .= "{$searchand}(u.display_name LIKE '{$n}{$term}{$n}')";
				} else {
					$search .= "{$searchand}(u.display_name LIKE '{$n}{$term}{$n}')";
				}
				$searchand = ' OR ';
			}
			$sentence_term = $wpdb->escape($s);
			if (count($search_terms) > 1 && $search_terms[0] != $sentence_term )
			{
				if ($this->wp_ver23)
				{
					$search .= " OR (u.display_name LIKE '{$n}{$sentence_term}{$n}')";
				} else {
					$search .= " OR (u.display_name LIKE '{$n}{$sentence_term}{$n}')";
				}
			}



			if ( !empty($search) )
			$search = " OR ({$search}) ";

		}

		$this->se_log("user where: ".$search);
		return $search;
	}

	// create the search meta data query
	function se_build_search_metadata()
	{
		global $wp_query, $wpdb;
		$s = $wp_query->query_vars['s'];
		$search_terms = $this->se_get_search_terms();
		$n = (isset($wp_query->query_vars['exact']) && $wp_query->query_vars['exact']) ? '' : '%';
		$search = '';

		if ( !empty($search_terms) ) {
			// Building search query
			$searchand = '';
			foreach($search_terms as $term) {
				$term = addslashes_gpc($term);
				if ($this->wp_ver23)
				{
					$search .= "{$searchand}(m.meta_value LIKE '{$n}{$term}{$n}')";
				} else {
					$search .= "{$searchand}(meta_value LIKE '{$n}{$term}{$n}')";
				}
				$searchand = ' AND ';
			}
			$sentence_term = $wpdb->escape($s);
			if (count($search_terms) > 1 && $search_terms[0] != $sentence_term )
			{
				if ($this->wp_ver23)
				{
					$search = "($search) OR (m.meta_value LIKE '{$n}{$sentence_term}{$n}')";
				} else {
					$search = "($search) OR (meta_value LIKE '{$n}{$sentence_term}{$n}')";
				}
			}

			if ( !empty($search) )
			$search = " OR ({$search}) ";

		}
		$this->se_log("meta where: ".$search);
		return $search;
	}

	// create the search tag query
	function se_build_search_tag()
	{
		global $wp_query, $wpdb;
		$s = $wp_query->query_vars['s'];
		$search_terms = $this->se_get_search_terms();
		$exact = $wp_query->query_vars['exact'];
		$search = '';

		if ( !empty($search_terms) )
		{
			// Building search query
			$n = ($exact) ? '' : '%';
			$searchand = '';
			foreach($search_terms as $term)
			{
				$term = addslashes_gpc($term);
				if ($this->wp_ver23)
				{
					$search .= "{$searchand}(tter.name LIKE '{$n}{$term}{$n}')";
				}
				$searchand = ' AND ';
			}
			$sentence_term = $wpdb->escape($s);
			if (!$sentence && count($search_terms) > 1 && $search_terms[0] != $sentence_term )
			{
				if ($this->wp_ver23)
				{
					$search = "($search) OR (tter.name LIKE '{$n}{$sentence_term}{$n}')";
				}
			}
			if ( !empty($search) )
			$search = " OR ({$search}) ";
		}
		$this->se_log("tag where: ".$search);
		return $search;
	}

	// create the search categories query
	function se_build_search_categories()
	{
		global $wp_query, $wpdb;
		$s = $wp_query->query_vars['s'];
		$search_terms = $this->se_get_search_terms();
		$exact = $wp_query->query_vars['exact'];
		$search = '';

		if ( !empty($search_terms) )
		{
			// Building search query for categories slug.
			$n = ($exact) ? '' : '%';
			$searchand = '';
			$searchSlug = '';
			foreach($search_terms as $term)
			{
				$term = addslashes_gpc($term);
				$searchSlug .= "{$searchand}(tter.slug LIKE '{$n}".sanitize_title_with_dashes($term)."{$n}')";
				$searchand = ' AND ';
			}
			if (!$sentence && count($search_terms) > 1 && $search_terms[0] != $s )
			{
				$searchSlug = "($searchSlug) OR (tter.slug LIKE '{$n}".sanitize_title_with_dashes($s)."{$n}')";
			}
			if ( !empty($searchSlug) )
			$search = " OR ({$searchSlug}) ";

			// Building search query for categories description.
			$searchand = '';
			$searchDesc = '';
			foreach($search_terms as $term)
			{
				$term = addslashes_gpc($term);
				$searchDesc .= "{$searchand}(ttax.description LIKE '{$n}{$term}{$n}')";
				$searchand = ' AND ';
			}
			$sentence_term = $wpdb->escape($s);
			if (!$sentence && count($search_terms) > 1 && $search_terms[0] != $sentence_term )
			{
				$searchDesc = "($searchDesc) OR (ttax.description LIKE '{$n}{$sentence_term}{$n}')";
			}
			if ( !empty($searchDesc) )
			$search = $search." OR ({$searchDesc}) ";
		}
		$this->se_log("categories where: ".$search);
		return $search;
	}

	// create the Posts exclusion query
	function se_build_exclude_posts()
	{
		global $wp_query, $wpdb;
		$excludeQuery = '';
		if (!empty($wp_query->query_vars['s']))
		{
			$excludedPostList = trim($this->options['se_exclude_posts_list']);
			if ($excludedPostList != '')
			{
				$excl_list = implode(',', explode(',',$excludedPostList));
				$excludeQuery = ' AND ('.$wpdb->posts.'.ID NOT IN ( '.$excl_list.' ))';
			}
			$this->se_log("ex posts where: ".$excludeQuery);
		}
		return $excludeQuery;
	}

	// create the Categories exclusion query
	function se_build_exclude_categories()
	{
		global $wp_query, $wpdb;
		$excludeQuery = '';
		if (!empty($wp_query->query_vars['s']))
		{
			$excludedCatList = trim($this->options['se_exclude_categories_list']);
			if ($excludedCatList != '')
			{
				$excl_list = implode(',', explode(',',$excludedCatList));
				if ($this->wp_ver23)
				{
					$excludeQuery = " AND ( ctax.term_id NOT IN ( ".$excl_list." ))";
				}
				else
				{
					$excludeQuery = ' AND (c.category_id NOT IN ( '.$excl_list.' ))';
				}
			}
			$this->se_log("ex category where: ".$excludeQuery);
		}
		return $excludeQuery;
	}

	//join for excluding categories - Deprecated in 2.3
	function se_exclude_categories_join($join)
	{
		global $wp_query, $wpdb;

		if (!empty($wp_query->query_vars['s']))
		{

			if ($this->wp_ver23)
			{
				$join .= " LEFT JOIN $wpdb->term_relationships AS crel ON ($wpdb->posts.ID = crel.object_id) LEFT JOIN $wpdb->term_taxonomy AS ctax ON (ctax.taxonomy = 'category' AND crel.term_taxonomy_id = ctax.term_taxonomy_id) LEFT JOIN $wpdb->terms AS cter ON (ctax.term_id = cter.term_id) ";
			} else {
				$join .= "LEFT JOIN $wpdb->post2cat AS c ON $wpdb->posts.ID = c.post_id";
			}
		}
		$this->se_log("category join: ".$join);
		return $join;
	}

	//join for searching comments
	function se_comments_join($join)
	{
		global $wp_query, $wpdb;

		if (!empty($wp_query->query_vars['s']))
		{
			if ($this->wp_ver23)
			{
				$join .= " LEFT JOIN $wpdb->comments AS cmt ON ( cmt.comment_post_ID = $wpdb->posts.ID ) ";

			} else {

				if ('Yes' == $this->options['se_approved_comments_only'])
				{
					$comment_approved = " AND comment_approved =  '1'";
				} else {
					$comment_approved = '';
				}
				$join .= "LEFT JOIN $wpdb->comments ON ( comment_post_ID = ID " . $comment_approved . ") ";
			}

		}
		$this->se_log("comments join: ".$join);
		return $join;
	}

	//join for searching authors

	function se_search_authors_join($join)
	{
		global $wp_query, $wpdb;

		if (!empty($wp_query->query_vars['s']))
		{
			$join .= " LEFT JOIN $wpdb->users AS u ON ($wpdb->posts.post_author = u.ID) ";
		}
		$this->se_log("authors join: ".$join);
		return $join;
	}

	//join for searching metadata
	function se_search_metadata_join($join)
	{
		global $wp_query, $wpdb;

		if (!empty($wp_query->query_vars['s']))
		{

			if ($this->wp_ver23)
			$join .= " LEFT JOIN $wpdb->postmeta AS m ON ($wpdb->posts.ID = m.post_id) ";
			else
			$join .= " LEFT JOIN $wpdb->postmeta ON $wpdb->posts.ID = $wpdb->postmeta.post_id ";
		}
		$this->se_log("metadata join: ".$join);
		return $join;
	}

	//join for searching tags
	function se_terms_join($join)
	{
		global $wp_query, $wpdb;

		if (!empty($wp_query->query_vars['s']))
		{

			// if we're searching for categories
			if ( $this->options['se_use_category_search'] )
			{
				$on[] = "ttax.taxonomy = 'category'";
			}

			// if we're searching for tags
			if ( $this->options['se_use_tag_search'] )
			{
				$on[] = "ttax.taxonomy = 'post_tag'";
			}
			// if we're searching custom taxonomies
			if ( $this->options['se_use_tax_search'] )
				{
					$all_taxonomies = get_object_taxonomies('post');
					foreach ($all_taxonomies as $taxonomy)
					{
						if ($taxonomy == 'post_tag' || $taxonomy == 'category')
						continue;
						$on[] = "ttax.taxonomy = '".addslashes($taxonomy)."'";
					}
				}
			// build our final string
			$on = ' ( ' . implode( ' OR ', $on ) . ' ) ';

			$join .= " LEFT JOIN $wpdb->term_relationships AS trel ON ($wpdb->posts.ID = trel.object_id) LEFT JOIN $wpdb->term_taxonomy AS ttax ON ( " . $on . " AND trel.term_taxonomy_id = ttax.term_taxonomy_id) LEFT JOIN $wpdb->terms AS tter ON (ttax.term_id = tter.term_id) ";
		}
		$this->se_log("tags join: ".$join);
		return $join;
	}

	// Highlight the searched terms into Title, excerpt and content
	// in the search result page.
	function se_postfilter($postcontent)
	{
		global $wp_query, $wpdb;
		$s = $wp_query->query_vars['s'];
		// highlighting
		if (is_search() && $s != '')
		{
			$highlight_color = $this->options['se_highlight_color'];
			$highlight_style = $this->options['se_highlight_style'];
			$search_terms = $this->se_get_search_terms();
			foreach ( $search_terms as $term )
			{
				if (preg_match('/\>/', $term))
        			continue; //don't try to highlight this one
					$term = preg_quote($term);

				if ($highlight_color != '')
				$postcontent = preg_replace(
					'"(?<!\<)(?<!\w)(\pL*'.$term.'\pL*)(?!\w|[^<>]*>)"i'
					, '<span class="search-everything-highlight-color" style="background-color:'.$highlight_color.'">$1</span>'
					, $postcontent
					);
				else
				$postcontent = preg_replace(
					'"(?<!\<)(?<!\w)(\pL*'.$term.'\pL*)(?!\w|[^<>]*>)"i'
					, '<span class="search-everything-highlight" style="'.$highlight_style.'">$1</span>'
					, $postcontent
					);
			}
		}
		return $postcontent;
	}

	function se_log_query($query, $wp_query){
		if($wp_query->is_search)
			$this->se_log($query);
		return $query;
	}// se_log_query
} // END

?>