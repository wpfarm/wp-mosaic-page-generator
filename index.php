<?php
/*
Plugin Name: Mosaic Page Generator
Description: Premium AI-powered location pages that boost rankings. Requires subscription with Direction.com
Version: 1.1.8
Author: Direction.com
*/


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

define('wpmpg_url',plugin_dir_url(__FILE__ ));
define('wpmpg_path',plugin_dir_path(__FILE__ ));
define('WPMPG_UPLOAD_FOLDER','mosaicpagegenerator');

$plugin = plugin_basename(__FILE__);

/* Master Class  */
require_once ('loader.php');
register_activation_hook( __FILE__, 'wpmpg_activation'); 

function  wpmpg_activation( $network_wide ) 
{
	$plugin = "wp-mosaic-page-generator/index.php";
	$plugin_path = '';	
	
	if ( is_multisite() && $network_wide ) // See if being activated on the entire network or one blog
	{ 
		activate_plugin($plugin_path,NULL,true);			
		
	} else { // Running on a single blog		   	
			
		activate_plugin($plugin_path,NULL,false);		
		
	}
}
global $wpmpg;
$wpmpg = new WpMosaicPageGenerator();