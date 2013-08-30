<?php
/*
Plugin Name: Purple Heart Rating (Free) by wp-buddy
Plugin URI: http://wp-buddy.com/plugins/purple-heart-rating/
Description: The ultimate Rating plugin which will blow you away!
Version: 1.2
Author: wp-buddy
Author URI: http://wp-buddy.com
Text Domain: purple-heart-rating-free
*/
/*  Copyright 2012-2013  WP-Buddy  (email : info@wp-buddy.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * The autoloader class
 *
 * @param string $class_name
 *
 * @return bool
 * @since 1.0
 */
function wpbphf_autoloader( $class_name ) {
	$file = trailingslashit( dirname( __FILE__ ) ) . 'classes/' . strtolower( $class_name ) . '.php';
	if ( is_file( $file ) ) {
		require_once( $file );
		return true;
	}

	return false;
}


// registering the autoloader function
try {
	spl_autoload_register( 'wpbphf_autoloader', true );
} catch ( Exception $e ) {
	function __autoload( $class_name ) {
		wpbph_autoloader( $class_name );
	}
}

$wpb_purpleheart_free = new WPB_Purple_Heart_Rating_Free( __FILE__ );



