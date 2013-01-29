<?php
/*
Plugin Name: Newsletter Sign-Up
Plugin URI: http://DannyvanKooten.com/wordpress-plugins/newsletter-sign-up/
Description: Adds various ways for your visitors to sign-up to your mailinglist (checkbox, widget, form)
Version: 1.7.9
Author: Danny van Kooten
Author URI: http://DannyvanKooten.com
License: GPL2
*/

/*  Copyright 2010  Danny van Kooten  (email : danny@vkimedia.com)

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

require('frontend/NewsletterSignUpWidget.php');
require('frontend/NewsletterSignUp.php');

$NewsletterSignUp = NewsletterSignUp::getInstance();

if(is_admin()) {
	require('backend/NewsletterSignUpAdmin.php');
	$NewsletterSignUpAdmin = new NewsletterSignUpAdmin();
}

/**
* Displays the comment checkbox, call this function if your theme does not use the 'comment_form' action in the comments.php template.
*/
function nsu_checkbox() {
    $NewsletterSignUp = NewsletterSignUp::getInstance();
    $NewsletterSignUp->output_checkbox();
}

/**
* Deprecated
* Just an alias for nsu_checkbox(), for backwards compatibility.
*/
function ns_comment_checkbox()
{
	nsu_checkbox();
}

/**
* Outputs a sign-up form, for usage in your theme files.
*/
function nsu_signup_form()
{
	$NewsletterSignUp = NewsletterSignUp::getInstance();
	$NewsletterSignUp->output_form(true);
}