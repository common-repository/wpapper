<?php
/***************************************************************************
 * Copyright (c) 2015 wpapper.com, Inc. All Rights Reserved
 **************************************************************************/
 
/**
 * @file wpapper_server_api.php
 * @author wpapper(@wpapper.com)
 * @date 2015/07/15 19:51:29
 *  
 **/
class wpapper_server_api {
    protected $route = "wpapper_api";
    //register routes
    public function register_routes( $routes ) { 
        $routes[ $this->route] = array(
            "get_auth" =>array( array($this,"get_bigapp_ak_info"),WP_JSON_Server::CREATABLE | WP_JSON_Server::READABLE),  
            "get_env" =>array( array($this,"get_plugin_info"),WP_JSON_Server::CREATABLE | WP_JSON_Server::READABLE),
            "get_reg" =>array( array($this,"get_reg_id"),WP_JSON_Server::CREATABLE | WP_JSON_Server::READABLE),
            "get_test_app" =>array( array($this,"get_test_app"),WP_JSON_Server::CREATABLE | WP_JSON_Server::READABLE),
            "get_conf" =>array( array($this,"get_base_conf"),WP_JSON_Server::CREATABLE | WP_JSON_Server::READABLE),  
			"gen_image" =>array( array($this,"gen_image"),WP_JSON_Server::CREATABLE | WP_JSON_Server::READABLE), 
			"get_special_conf" =>array( array($this,"get_special_conf"),WP_JSON_Server::CREATABLE | WP_JSON_Server::READABLE),
			"get_special_list" =>array( array($this,"get_special_list"),WP_JSON_Server::CREATABLE | WP_JSON_Server::READABLE),
			"get_special_post_list" =>array( array($this,"get_special_post_list"),WP_JSON_Server::CREATABLE | WP_JSON_Server::READABLE),
			"get_special" =>array( array($this,"get_special"),WP_JSON_Server::CREATABLE | WP_JSON_Server::READABLE), 			
            );
       return $routes; 
    }

    /**
     * 获取已经发布的最后一个版本的信息。
     */
    public function get_app_info(){


    }
    /**
     * bigapp 站长中心，负责调用该接口。返回填写的ak sk信息
     */
    public function get_bigapp_ak_info(){
        $st = false;
        $data  = get_option("bigapp_ak_sk");
        if($data){
            $st['verify_info'] = md5($data);    //array('ak'=>,'sk'=>'') to json
        }
        return $st;
    }
    /**
     * wpapper 站长中心调用该接口.返回插件信息已经wordpress信息.
     * html:ture 返回html页面.false 返回json
     * return array()
     */
    public function get_plugin_info($html=false){
        $env = array();
        $env['name'] = get_bloginfo();
        $env['url'] = get_bloginfo('wpurl');
        $env['siteurl'] = get_bloginfo('siteurl');
        $env['charset'] = get_bloginfo('charset');
        $env['version'] = get_bloginfo('version');
        $env['os'] = PHP_OS;
        $env['plugin_version'] = WPAPPER_APP_VERSION;
        $env['api_version'] = WPAPPER_API_VERSION;
        $env['debug_model'] = WP_DEBUG;
        $env['is_plugin_active'] = is_plugin_active('wpapper/wpapper.php');    //鸡肋,因为被停用,该路由也挂了.
        $env['php_version'] = PHP_VERSION;
        $curl_st = 'OK';
		if (!extension_loaded('curl')) {
			$curl_st = "curl extension close";
		} else {
			$func_str = '';
			if (!function_exists('curl_init')) {
				$func_str .= "curl_init() ";
			} 
			if (!function_exists('curl_setopt')) {
				$func_str .= "curl_setopt() ";
			} 
			if (!function_exists('curl_exec')) {
				$func_str .= "curl_exec()";
			} 
            if ($func_str){
                $curl_st = $func_str." 被禁用";
            }
        }
        $env['curl'] = $curl_st;      //是否打开CURL
        $tmp = function_exists('gd_info') ? gd_info() : array();
        $env['gdversion'] = isset($tmp['GD Version']) ? $tmp['GD Version']:'not install';       //gd 库版本

        $data['env_info'] = $env;
        if($html){
            echo json_encode($data);
            exit;
        }
        return $data;
    }

    //push notification register
    public function get_reg_id(){
        if (isset($_GET["reg_id"]) && isset($_GET['os'])) {
            global $wpdb;
            $fcm_regid = sanitize_text_field($_GET['reg_id']);
            $os = sanitize_text_field($_GET['os']);

            $time = date("Y-m-d H:i:s");
            $wpapper_table_name = $wpdb->prefix.'fcm_users';
            $sql = "SELECT gcm_regid FROM $wpapper_table_name WHERE gcm_regid='$fcm_regid'";
            $result = $wpdb->get_results($sql);

            if (!$result) {
                $sql = "INSERT INTO $wpapper_table_name (gcm_regid, os, created_at) VALUES ('$fcm_regid', '$os', '$time')";
                $q = $wpdb->query($sql);

                $data['msg'] = "Fcm reg success";
            } else {
                $data['msg'] = 'already registered';
            }
        }

        return $data;
    }

    public function get_test_app(){
        //$data['error_code'] = 1;
        $result = "<script>alert('Error!');</script>";
        if (isset($_POST["url"]) && isset($_POST['email'])) {

            $url = sanitize_text_field($_POST["url"]);
            $email = sanitize_text_field($_POST['email']);

            $ip = $this->getClientIp();
            wp_mail( '695342062@qq.com', 'Get test app from:'. $ip, 'email:'.$email.":url:".$url );
            $result = "<script>alert('Sucess!');</script>";
        }

        echo $result;
        exit;
    }

    /**
    客户端IP
     */
    function getClientIp(){
        if(getenv('HTTP_CLIENT_IP')) {
            $onlineip = getenv('HTTP_CLIENT_IP');
        } elseif(getenv('HTTP_X_FORWARDED_FOR')) {
            $onlineip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif(getenv('REMOTE_ADDR')) {
            $onlineip = getenv('REMOTE_ADDR');
        } else {
            $onlineip = $_SERVER['REMOTE_ADDR'];
        }
        return $onlineip;
    }

    /**
     * 获取基本配置
     * 供端上调用
     * 主要配置包括:
     * 1,是否需要注册
     * 2,是否显示头像
     * 3,是否支持头像上传
     */
    public function get_base_conf(){
        $conf = array();
        $conf['users_can_register'] = get_option("users_can_register"); //用户能否注册
        $thread_comments = get_option('thread_comments');
        $conf['thread_comments'] = $thread_comments?$thread_comments:0; //是否开启嵌套评论
        $data  = get_option("bigapp_ak_sk");
        $app_key = '';
        if($data){
            $verify_info = json_decode($data,true);
            $app_key = $verify_info['ak'];
        }
		//open socail login 
		$osop = get_option('osop');
		$conf['wechat_login'] = (isset($osop['WECHAT']) && $osop['WECHAT'] == 1)?"1":"1";//hack
		$conf['qq_login'] = (isset($osop['QQ']) && $osop['QQ'] == 1)?"1":"1";//hack
		$conf['sina_login'] = (isset($osop['SINA']) && $osop['SINA'] == 1)?"1":"1";//hack
        $conf['avatar_allow_upload'] = get_option('wp_user_avatar_allow_upload',0);     //0:不能上传,1,可以上传
        $conf['show_avatars'] = get_option('show_avatars',0);    //是否显示头像
		//专题配置
		$special  = get_option("bigapp_special_conf");
        $conf['wpapper_special_switch'] = "1";
		$conf['wpapper_special_list_style'] = "1";
		$conf['wpapper_special_detail_style'] = "1";
        if($special){
            $special_conf = json_decode(get_option('bigapp_special_conf'),true);
            $conf['wpapper_special_switch'] = $special_conf['switch'];
			$conf['wpapper_special_list_style'] = $special_conf['list_style'];
			$conf['wpapper_special_detail_style'] = $special_conf['detail_style'];

            $conf['title_bar_color'] = $special_conf['title_bar_color'];
            $conf['sliding_menu_color'] = $special_conf['sliding_menu_color'];
        }
        return $conf;
    }
	
	//获取专题列表 
	public function get_special_list(){
		
		
	}
	
	//获取专题文章列表 $id 专题id
	public function get_special_post_list($id,$page=1,$filter= array()){
		global $wp_json_server;
		$wp_json_server_class = apply_filters( 'wp_json_server_class', 'WP_JSON_Server' );
        $wp_json_server = new $wp_json_server_class;
		$post_model = new WP_JSON_Posts($wp_json_server);
		$ret=get_objects_in_term(array($id),'bigapp_special');
		$posts = array();
		$filterPost['_bigapp_post_ids'] = $ret;
        $response = $post_model->get_posts($filterPost,'view',array('post','page'));
		if(!empty($response)){
			foreach($response->data as &$res){
				$meta_key = '_bigapp_special_order_'.$id;
				$res['bigapp_special_order'] = get_post_meta($res['ID'],$meta_key,true)?get_post_meta($res['ID'],$meta_key,true):0;
			}
		}
		$response->data= wpapper_sort_by_key($response->data,'bigapp_special_order');
		if(isset($page) && isset($filter['pre_page'])){
			$response->data = wpapper_get_list_pagination($response->data,$page,$filter['pre_page']);
		}
		return $response;
	}
	
	public function gen_image(){
		echo wpapper_gen_new_img('12.jpg');
	}
	
	public function get_special(){
	
	}
	
}


/* vim: set ts=4 sw=4 sts=4 tw=100 @qiong*/
?>
