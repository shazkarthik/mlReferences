<?php

/**
 * Plugin Name: mlReferences
 * Plugin URI: http://medialeg.ch/Products/mlReferences
 * Description: This plugin allows to import and use literature references managed in <a href="http://endnote.com" target="_blank">Endnote</a>. Imported references can be integrated into wordpress pages and posts. References lists are generated per page/post or for a group of pages/posts on demand.
 * Author: medialeg
 * Version: 1.0
 * Author URI: http://medialeg.ch/Products
 */

libxml_use_internal_errors(true);

require_once('vendors/php-csv-utils-0.3/Csv/Dialect.php');
require_once('vendors/php-csv-utils-0.3/Csv/Writer.php');
require_once('vendors/PHPExcel-1.8/Classes/PHPExcel/IOFactory.php');

require_once('modules/actions.php');
require_once('modules/end_note.php');
require_once('modules/pages.php');
require_once('modules/spreadsheet.php');
require_once('modules/utilities.php');
require_once('modules/wp.php');
if (defined('WP_CLI') && WP_CLI) {
    require_once('modules/cli.php');
}

register_activation_hook(__FILE__, 'mlReferences_register_activation_hook');
register_deactivation_hook(__FILE__, 'mlReferences_register_deactivation_hook');

add_action('init', 'mlReferences_init');
add_action('admin_init', 'mlReferences_admin_init');
add_action('admin_menu', 'mlReferences_admin_menu');
add_action('add_meta_boxes', 'mlReferences_add_meta_boxes');
add_action('save_post', 'mlReferences_save_post');
add_action('wp_head', 'mlReferences_wp_head', 90);

add_filter('the_content', 'mlReferences_the_content', 90);
