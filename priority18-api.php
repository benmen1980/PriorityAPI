<?php
/**
* @package     PriorityAPI
* @author      Ante Laca <ante.laca@gmail.com>
* @copyright   2018 Roi Holdings
*
* @wordpress-plugin
* Plugin Name: Priority 18 API 
* Plugin URI: http://www.roi-holdings.com
* Description: Priority is an ERP system, it delivers advanced solutions based on innovative technologies, from cloud and on-premise, to APIs and mobile
* Version: 1.2.3.4
* Author: Roi Holdings
* Author URI: http://www.roi-holdings.com
* Licence: GPLv2
* Text Domain: p18a
* Domain Path: languages  
* 
*/

namespace PriorityAPI;

defined('ABSPATH') or die('No direct script access!');
$plugin_data = get_file_data(__FILE__, array('Version' => 'Version'), false);
$plugin_version = $plugin_data['Version'];
// Priority API
define('P18A_VERSION'       , $plugin_version);
define('P18A_SELF'          , __FILE__);
define('P18A_URI'           , plugin_dir_url(__FILE__));
define('P18A_DIR'           , plugin_dir_path(__FILE__)); 
define('P18A_ASSET_DIR'     , trailingslashit(P18A_DIR)    . 'assets/');
define('P18A_ASSET_URL'     , trailingslashit(P18A_URI)    . 'assets/');
define('P18A_CLASSES_DIR'   , trailingslashit(P18A_DIR)    . 'includes/classes/');
define('P18A_ADMIN_DIR'     , trailingslashit(P18A_DIR)    . 'includes/admin/');
// define plugin name and plugin admin url
define('P18A_PLUGIN_NAME'      , 'Priority 18 API');
define('P18A_PLUGIN_ADMIN_URL' , sanitize_title(P18A_PLUGIN_NAME));
require P18A_CLASSES_DIR . 'api.php';
require_once( P18A_DIR . 'includes/front/shortcodes/sample_shortcode.php' );
API::instance()->run();


// Load the updater.
require ( plugin_dir_path( __FILE__ ) ). 'update_this_plugin.php';

// Initialize your extension of the update class passing in the current plugin version, directory and slug.
//Current Version, Directory name, Plugin_Slug (main file name without extensioni.e this current file )
//Github username, github repo name, branch (optional, default = main)
$updater = new \NexVis\WordPress\Update_This_Plugin( '1.3.0', 'PriorityAPI-master', 'priority18-api', 'benmen1980', 'PriorityAPI','master' );

// Initialize the class which sets up the filters for `transient_update_plugins` and `site_transient_update_plugins`
$updater->init();
