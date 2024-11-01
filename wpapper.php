<?php
/**
 * Plugin Name: WpApper
 * Description: Create native app(Android & iOS). The wordpress plugin for Wpapper
 * Version: 1.2.1
 * Author: WpApper
 * Author URI: http://wpapper.com/
 */

/*
    Copyright (c) 2017 wpapper (email:oldcwj@gmail.coom)

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

*/
define( 'WPAPPER_APP_VERSION', '1.2.1' );
define( 'WPAPPER_INNER_VERSION', '9' );
define( 'WPAPPER_LAST_MODIFICATION', ' 2017-07-23 11:30' );
define( 'WPAPPER_API_VERSION', '1' );
define( 'WPAPPER_MINIMUM_WP_VERSION','3.5' );
define( 'WPAPPER_API_DEBUG', true );
define( 'WPAPPER_API_RESOURCE',false);       //资源版本号

if ( !function_exists( 'add_action' ) ) {
	echo wpapper_lan('Hi there!  I\'m just a plugin, not much I can do when called directly.');
	exit;
}


if(!defined('WPAPPER_ROOT')){
    define( 'WPAPPER_ROOT', dirname(__FILE__) );
}
if(!defined('WPAPPER_FOLDER')){
    define ('WPAPPER_FOLDER',basename(WPAPPER_ROOT));
}
if(!defined('WPAPPER_URL')){
    define ('WPAPPER_URL',plugin_dir_url(WPAPPER_FOLDER).WPAPPER_FOLDER.'/');
}

require_once( WPAPPER_ROOT.'/wpapper.class.php');

require_once( WPAPPER_ROOT.'/fcm/options.php');

register_activation_hook( __FILE__, array('mobileplugin_wpapper', 'wpapper_json_api_activation') );

register_deactivation_hook( __FILE__, array("mobileplugin_wpapper", 'wpapper_json_api_deactivation') );

add_action( 'init', array('mobileplugin_wpapper','init' ) );

//add_action( 'init', array('mobileplugin_wpapper','wpapper_json_api_maybe_flush_rewrites'), 999 );

add_action( 'wp_json_server_before_serve', array('mobileplugin_wpapper', 'wpapper_json_api_default_filters'), 10, 1 );


add_action( 'template_redirect', array("mobileplugin_wpapper", 'wpapper_json_api_loaded'), -100 );

add_filter('plugin_row_meta', 'json_api_wp_version_warning', 10, 4);
function json_api_wp_version_warning($plugin_meta, $plugin_file, $plugin_data, $status) {
    if(version_compare($GLOBALS['wp_version'], WPAPPER_MINIMUM_WP_VERSION, '<') && strpos($plugin_file, 'wpapper') !== false){
        $plugin_meta[] = '<font style="line-height:30px;color:red;font-weight:bold">Sorry, WpApper Json API requires WordPress version ' . WPAPPER_MINIMUM_WP_VERSION . ' or greater.</font>';
    }
    return $plugin_meta;
}

add_action( 'login_redirect', array('mobileplugin_wpapper', 'wpapper_auth_login'),10,3);
add_action( 'registration_redirect', array('mobileplugin_wpapper', 'wpapper_auth_register'));

//设置show_in_json 控制项
add_action( 'registered_post_type', array('mobileplugin_wpapper','json_register_post_type'), 10, 2 );

add_filter( 'json_authentication_errors', array('mobileplugin_wpapper', 'wpapper_json_cookie_check_errors'), 100 );

add_action( 'auth_cookie_malformed',    array('mobileplugin_wpapper', 'wpapper_json_cookie_collect_status') );
add_action( 'auth_cookie_expired',      array('mobileplugin_wpapper', 'wpapper_json_cookie_collect_status') );
add_action( 'auth_cookie_bad_username', array('mobileplugin_wpapper', 'wpapper_json_cookie_collect_status') );
add_action( 'auth_cookie_bad_hash',     array('mobileplugin_wpapper', 'wpapper_json_cookie_collect_status') );
add_action( 'auth_cookie_valid',        array('mobileplugin_wpapper', 'wpapper_json_cookie_collect_status_valid') );
//头像
add_filter('get_avatar',array('mobileplugin_wpapper', 'wpapper_json_api_get_avatar') ) ;
//admin menu
if ( is_admin() ) {  
    require_once dirname(__FILE__) . "/lib/common/template.inc.php";
    add_action('admin_menu',array('WpApperAdmin','init'));
	add_filter( 'plugin_action_links', array('WpApperAdmin', 'wp_wpapper_plugin_action_links'),10,2);    //添加设置到列表
}
WpApperAdmin::init_api_routes();
//国际化部分
add_action( 'plugins_loaded', array('mobileplugin_wpapper','wpapper_localize'));

//fcm
function wpapper_fcm_activated() {
    global $wpdb;
    $wpapper_table_name = $wpdb->prefix.'fcm_users';

    if($wpdb->get_var("show tables like '$wpapper_table_name'") != $wpapper_table_name) {
        $sql = "CREATE TABLE " . $wpapper_table_name . " (
		`id` int(11) NOT NULL AUTO_INCREMENT,
        `gcm_regid` text,
        `os` text,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
		);";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    //add_option('wpapper_fcm_do_activation_redirect', true);
}

register_activation_hook(__FILE__, 'wpapper_fcm_activated');
