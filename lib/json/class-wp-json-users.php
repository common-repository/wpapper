<?php

class WP_JSON_Users {
	/**
	 * Server object
	 *
	 * @var WP_JSON_ResponseHandler
	 */
	protected $server;
	protected $route = 'users';

	/**
	 * Constructor
	 *
	 * @param WP_JSON_ResponseHandler $server Server object
	 */
	public function __construct( WP_JSON_ResponseHandler $server ) {
		$this->server = $server;
	}

	/**
	 * Register the user-related routes
	 *
	 * @param array $routes Existing routes
	 * @return array Modified routes
	 */
	public function register_routes( $routes ) {
		$user_routes = array(
            $this->route => array(
                "get_users" => array( array( $this, 'get_users' ), WP_JSON_Server::READABLE ),
				"get_current_user" => array( array( $this, 'get_current_user' ), WP_JSON_Server::READABLE ),
                "get_user" => array( array( $this, 'get_user' ), WP_JSON_Server::READABLE ),
				"edit_metas" => array( array( $this, 'edit_user_metas'), WP_JSON_Server::READABLE | WP_JSON_Server::CREATABLE ),
				"edit_user" => array( array( $this, 'edit_user'), WP_JSON_Server::READABLE | WP_JSON_Server::CREATABLE ),
				"get_user_metas" => array( array( $this, 'get_user_metas'),  WP_JSON_Server::READABLE | WP_JSON_Server::CREATABLE ),
				"get_posts" => array( array( $this, 'get_user_posts'),  WP_JSON_Server::READABLE | WP_JSON_Server::CREATABLE ),
				"getTplList" => array( array( $this, 'getTplList'),  WP_JSON_Server::READABLE | WP_JSON_Server::CREATABLE ),
				"upload_avatar" => array( array( $this, 'upload_avatar'),  WP_JSON_Server::READABLE | WP_JSON_Server::CREATABLE ),
            ),	
		);
		return array_merge( $routes, $user_routes );
	}

	/**
	 * Retrieve users.
	 *
	 * @param array $filter Extra query parameters for {@see WP_User_Query}
	 * @param string $context optional
	 * @param int $page Page number (1-indexed)
	 * @return array contains a collection of User entities.
	 */
	public function get_users( $filter = array(), $context = 'view', $page = 1 ) {
        #return array(); //先关闭
		if ( ! current_user_can( 'list_users' ) ) {
            wpapper_json_error(BigAppErr::$user['code'],BigAppErr::$user['msg'],wpapper_lan("don't allow to list users"));
		}

		$args = array(
			'orderby' => 'user_login',
			'order'   => 'ASC'
		);
		$args = array_merge( $args, $filter );

		$args = apply_filters( 'json_user_query', $args, $filter, $context, $page );

		// Pagination
		$args['number'] = empty( $args['number'] ) ? 10 : absint( $args['number'] );
		$page           = absint( $page );
		$args['offset'] = ( $page - 1 ) * $args['number'];

		$user_query = new WP_User_Query( $args );

		if ( empty( $user_query->results ) ) {
			return array();
		}

		$struct = array();

		foreach ( $user_query->results as $user ) {
			$struct[] = $this->prepare_user( $user, $context );
		}

		return $struct;
	}

	/**
	 * Retrieve the current user
	 *
	 * @param string $context
	 * @return mixed See
	 */
	public function get_current_user( $context = 'view' ) {
		
		//$current_user_id = get_current_user_id(); todo 
		$current_user_id = apply_filters( 'determine_current_user', false );
		
		if ( empty( $current_user_id ) ) {
            wpapper_json_error(BigAppErr::$user['code'],BigAppErr::$user['msg'],wpapper_lan("need login"));
		}

		$response = $this->get_user( $current_user_id, $context );
		

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		if ( ! ( $response instanceof WP_JSON_ResponseInterface ) ) {
			$response = new WP_JSON_Response( $response );
		}

		return $response;
	}
	
	public function edit_user_metas( $context = 'view' ) {

		$current_user_id = apply_filters( 'determine_current_user', false );
		
		if ( empty( $current_user_id ) ) {
            wpapper_json_error(BigAppErr::$user['code'],BigAppErr::$user['msg'],wpapper_lan("need login"));
		}
		
		$allow_keys = array('fav','avatar','feedback');//收藏，反馈，头像
		
		$meta_key = isset($_POST['meta_key'])?sanitize_text_field($_POST['meta_key']):null;
		$meta_value = isset($_POST['meta_value'])?sanitize_text_field($_POST['meta_value']):null;
		$unique = isset($_POST['unique'])?true:false;
		
		if(empty($meta_key) || !in_array($meta_key,$allow_keys)){
            wpapper_json_error(BigAppErr::$user['code'],BigAppErr::$user['msg'],wpapper_lan("meta key not allow."));
		}
		
		$metas = get_user_meta($current_user_id, $meta_key);
		$result = false;
		if($metas){
			if($unique){
				$result = update_user_meta( $current_user_id, $meta_key, $meta_value );
			}else{
				$result = update_user_meta( $current_user_id, $meta_key, $meta_value );
			}
			
		}else{
			$result = add_user_meta( $current_user_id, $meta_key, $meta_value );
		}
		return array('result' => $result);
	}
	
	public function get_user_metas( $context = 'view' ) {

		$current_user_id = apply_filters( 'determine_current_user', false );
		
		if ( empty( $current_user_id ) ) {
            wpapper_json_error(BigAppErr::$user['code'],BigAppErr::$user['msg'],wpapper_lan("need login"));
		}
		
		$allow_keys = array('fav','avatar','feedback');
		
		$meta_key = isset($_REQUEST['meta_key'])?$_REQUEST['meta_key']:null;
		
		if(empty($meta_key) || !in_array($meta_key,$allow_keys)){
            wpapper_json_error(BigAppErr::$user['code'],BigAppErr::$user['msg'],"meta key not allow.");
		}
		
		$result = get_user_meta($current_user_id, $meta_key);
		
		return array($meta_key => $result);
	}

	/**
	 * Retrieve a user.
	 *
	 * @param int $id User ID
	 * @param string $context
	 * @return response
	 */
	public function get_user( $id, $context = 'view' ) {
		$id = (int) $id;
		//$current_user_id = get_current_user_id(); todo 
		$current_user_id = apply_filters( 'determine_current_user', false );

		if ( $current_user_id !== $id && ! current_user_can( 'list_users' ) ) {
            wpapper_json_error(BigAppErr::$user['code'],"Sorry, you are not allowed to view this user.");
		}

		$user = get_userdata( $id );

		if ( empty( $user->ID ) ) {
            wpapper_json_error(BigAppErr::$user['code'],"Invalid user ID .");
		}

		return $this->prepare_user( $user, $context );
	}

	/**
	 *
	 * Prepare a User entity from a WP_User instance.
	 *
	 * @param WP_User $user
	 * @param string $context One of 'view', 'edit', 'embed'
	 * @return array
	 */
	protected function prepare_user( $user, $context = 'view' ) {
		$user_fields = array(
			'ID'          => $user->ID,
			'username'    => $user->user_login,
			'name'        => $user->display_name,
			'first_name'  => $user->first_name,
			'last_name'   => $user->last_name,
			'nickname'    => $user->nickname,
			'slug'        => $user->user_nicename,
			'URL'         => $user->user_url,
			'avatar'      => wpapper_json_get_avatar_url( $user->user_email ),
			'description' => $user->description,
		);

		$user_fields['registered'] = date( 'c', strtotime( $user->user_registered ) );

		if ( $context === 'view' || $context === 'edit' ) {
			$user_fields['roles']        = $user->roles;
			$user_fields['capabilities'] = $user->allcaps;
			$user_fields['email']        = false;
		}

		if ( $context === 'edit' ) {
			// The user's specific caps should only be needed if you're editing
			// the user, as allcaps should handle most uses
			$user_fields['email']              = $user->user_email;
			$user_fields['extra_capabilities'] = $user->caps;
		}

		$user_fields['meta'] = array(
			'links' => array(
				'self' => get_json_url_users_get_user( $user->ID ),
				'archives' => get_json_url_users_get_posts( $user->ID ),
			),
		);

		return apply_filters( 'json_prepare_user', $user_fields, $user, $context );
	}

	/**
	 * Add author data to post data
	 *
	 * @param array $data Post data
	 * @param array $post Internal post data
	 * @param string $context Post context
	 * @return array Filtered data
	 */
	public function add_post_author_data( $data, $post, $context ) {
		$author = get_userdata( $post['post_author'] );

		if ( ! empty( $author ) ) {
			$data['author'] = $this->prepare_user( $author, 'embed' );
		}

		return $data;
	}

	/**
	 * Add author data to comment data
	 *
	 * @param array $data Comment data
	 * @param array $comment Internal comment data
	 * @param string $context Data context
	 * @return array Filtered data
	 */
	public function add_comment_author_data( $data, $comment, $context ) {
		if ( (int) $comment->user_id !== 0 ) {
			$author = get_userdata( $comment->user_id );

			if ( ! empty( $author ) ) {
				$data['author'] = $this->prepare_user( $author, 'embed' );
			}
		}

		return $data;
	}

	protected function insert_user( $data ) {
		$user = new stdClass;
		
		if ( ! empty( $data['ID'] ) ) {
			$existing = get_userdata( $data['ID'] );

			if ( ! $existing ) {
                wpapper_json_error(BigAppErr::$user['code'],"Invalid user ID",$data['ID']);
			}

			if ( ! current_user_can( 'edit_user', $data['ID'] ) ) {
                wpapper_json_error(BigAppErr::$user['code'],"Sorry, you are not allowed to edit users.");
			}

			$user->ID = $existing->ID;
			$update = true;
		} else {
			if ( ! current_user_can( 'create_users' ) ) {
                wpapper_json_error(BigAppErr::$user['code'],"Sorry, you are not allowed to create users.");
			}

			$required = array( 'username', 'password', 'email' );

			foreach ( $required as $arg ) {
				if ( empty( $data[ $arg ] ) ) {
                    wpapper_json_error(BigAppErr::$user['code'],"create user,missing parameter ",$arg);
				}
			}

			$update = false;
		}

		// Basic authentication details
		if ( isset( $data['username'] ) ) {
			$user->user_login = $data['username'];
		}

		if ( isset( $data['password'] ) ) {
			$user->user_pass = $data['password'];
		}

		// Names
		if ( isset( $data['name'] ) ) {
			$user->display_name = $data['name'];
		}

		if ( isset( $data['first_name'] ) ) {
			$user->first_name = $data['first_name'];
		}

		if ( isset( $data['last_name'] ) ) {
			$user->last_name = $data['last_name'];
		}

		if ( isset( $data['nickname'] ) ) {
			$user->nickname = $data['nickname'];
		}

		if ( ! empty( $data['slug'] ) ) {
			$user->user_nicename = $data['slug'];
		}elseif(isset($data['nickname'])){      //没有slug的情况下，使用nickname字段
			$user->user_nicename = $data['nickname'];
        }


		// URL
		if ( ! empty( $data['URL'] ) ) {
			$escaped = esc_url_raw( $user->user_url );

			if ( $escaped !== $user->user_url ) {
                wpapper_json_error(BigAppErr::$user['code'],"Invalid user URL.");
			}

			$user->user_url = $data['URL'];
		}

		// Description
		if ( ! empty( $data['description'] ) ) {
			$user->description = $data['description'];
		}

		// Email
		if ( ! empty( $data['email'] ) ) {
			$user->user_email = $data['email'];
		}
		

		// Role
		if ( ! empty( $data['role'] ) ) {
			$user->role = $data['role'];
		}

		// Pre-flight check
		$user = apply_filters( 'json_pre_insert_user', $user, $data );

		if ( is_wp_error( $user ) ) {
			return $user;
		}

		$user_id = $update ? wp_update_user( $user ) : wp_insert_user( $user );

		if ( is_wp_error( $user_id ) ) {
			return $user_id;
		}

		$user->ID = $user_id;

		do_action( 'json_insert_user', $user, $data, $update );

		return $user_id;
	}

	/**
	 * Edit a user.
	 *
	 * The $data parameter only needs to contain fields that should be changed.
	 * All other fields will retain their existing values.
	 *
	 * @param int $id User ID to edit
	 * @param array $data Data construct
	 * @return true on success
	 */
	public function edit_user( $id=0, $data= array() ) {
        if($id == 0){
            $id = get_current_user_id();
        }
		$id = absint( $id );
        
		if ( empty( $id ) ) {
            wpapper_json_error(BigAppErr::$user['code'],"User ID must be supplied.");
		}

		// Permissions check
		if ( ! current_user_can( 'edit_user', $id ) ) {
            wpapper_json_error(BigAppErr::$user['code'],"Sorry, you are not allowed to edit this user.");
		}

		$user = get_userdata( $id );
		
		if ( ! $user ) {
            wpapper_json_error(BigAppErr::$user['code'],"User ID is invalid.");
		}
        $user_info = $this->get_user_info_from_post();
        $data = array_merge($user_info,$data);
        if(!$data['nickname']){      //如果为空，则使用上一次的值
            $data['nickname']= urldecode($user->user_nicename);     //主要是用于之后有一个 name过滤函数。
        }

		$data['ID'] = $user->ID;
		
		// Update attributes of the user from $data
		$retval = $this->insert_user( $data );
		
		if ( is_wp_error( $retval ) ) {
            wpapper_json_error(BigAppErr::$user['code'],"Update user info faild.",json_encode($data));
		}
		return array('status'=>0);
	}

	/**
	 * Create a new user.
	 *
	 * @param $data
	 * @return mixed
	 */
	public function create_user( $data ) {
        $status = true;
		if ( ! current_user_can( 'create_users' ) ) {
            wpapper_json_error(BigAppErr::$user['code'],BigAppErr::$user['msg'],"Sorry, you are not allowed to create users..");
		}

		if ( ! empty( $data['ID'] ) ) {
            wpapper_json_error(BigAppErr::$user['code'],BigAppErr::$user['msg'],"Cannot create existing user..");
		}

		$user_id = $this->insert_user( $data );

		if ( is_wp_error( $user_id ) ) {
            $status = false;
		}

		$response = $this->get_user( $user_id );

		if ( ! $response instanceof WP_JSON_ResponseInterface ) {
			$response = new WP_JSON_Response( $response );
		}

		$response->set( $status );

		return $response;
	}

	/**
	 * Create a new user.
	 *
	 * @deprecated
	 *
	 * @param $data
	 * @return mixed
	 */
	public function new_user( $data ) {
		_deprecated_function( __CLASS__ . '::' . __METHOD__, 'WPAPI-1.2', 'WP_JSON_Users::create_user' );

		return $this->create_user( $data );
	}

	/**
	 * Delete a user.
	 *
	 * @param int $id
	 * @param bool force
	 * @return true on success
	 */
	public function delete_user( $id, $force = false, $reassign = null ) {
		$id = absint( $id );

		if ( empty( $id ) ) {
            wpapper_json_error(BigAppErr::$user['code'],BigAppErr::$user['msg'],"Invalid user ID.");
		}

		// Permissions check
		if ( ! current_user_can( 'delete_user', $id ) ) {
            wpapper_json_error(BigAppErr::$user['code'],BigAppErr::$user['msg'],"Sory ,you are not allowed to delete this user.");
		}

		$user = get_userdata( $id );

		if ( ! $user ) {
            wpapper_json_error(BigAppErr::$user['code'],BigAppErr::$user['msg'],"Invalid user ID.");
		}

		if ( ! empty( $reassign ) ) {
			$reassign = absint( $reassign );

			// Check that reassign is valid
			if ( empty( $reassign ) || $reassign === $id || ! get_userdata( $reassign ) ) {
                wpapper_json_error(BigAppErr::$user['code'],BigAppErr::$user['msg'],"Invalid user ID.");
			}
		} else {
			$reassign = null;
		}

		$result = wp_delete_user( $id, $reassign );

		if ( ! $result ) {
            wpapper_json_error(BigAppErr::$user['code'],BigAppErr::$user['msg'],"The user cannot be deleted..");
		} else {
			return array( 'message' => __( 'Deleted user' ) );
		}
	}
    function get_user_posts($user_id){

    }
    /**
     * 判断是否安装了open_social 插件
     * 本插件依赖这个插件,实现第三方登录
     * tpl:third party login
     * return true/false
     */
    function checkTpl(){
        $flag = is_plugin_active('open-social/open-social.php');    
        if($flag == false){
            if(function_exists('open_social_activation')){
                $flag = true;
            }
        }
        return $flag;
    }
    /**
     * 获取支持哪些第三方登录
     * 如果一个都不支持,返回的就是空数组
     * return array()
     */
    function getTplList(){
        $list = array();
        if(!$this->checkTpl()){
            return $list;
        }
        $osop = get_option('osop');
        if(isset($osop['QQ']) && $osop['QQ'] == 1){       //启用
            $list[] = array('type'=>"qq",'akey'=>$osop['QQ_AKEY'],'skey'=>$osop['QQ_SKEY']);
        }
        if(isset($osop['SINA']) && $osop['SINA'] == 1){       //启用
            $list[] = array('type'=>"sina",'akey'=>$osop['SINA_AKEY'],'skey'=>$osop['SINA_SKEY']);
        }
        if(isset($osop['WECHAT']) && $osop['WECHAT'] == 1){       //启用
            $list[] = array('type'=>"wechat",'akey'=>$osop['WECHAT_AKEY'],'skey'=>$osop['WECHAT_SKEY']);
        }
        return $list;
    }
    /**
     * 上传用户图像接口
     * wpua_file:文件key
     */
    public function upload_avatar(){
        $url = "";
        if(get_option('wp_user_avatar_allow_upload',0) == 0){       //是否开启头像上传
            wpapper_json_error(BigAppErr::$user['code'],'cant allow to upload avatars');
        }
        if(!($user_id = get_current_user_id())){
            wpapper_json_error(BigAppErr::$user['code'],'need login');
        }
        // wp_handle_upload
        require_once(ABSPATH.'wp-admin/includes/file.php');
        // wp_generate_attachment_metadata
        require_once(ABSPATH.'wp-admin/includes/image.php');
        // image_add_caption
        require_once(ABSPATH.'wp-admin/includes/media.php');
		global $post,$wpdb,$blog_id;

        $wpua_size_limit = get_option('wp_user_avatar_upload_size_limit');      //文件限制
        $wpua_resize_upload = get_option('wp_user_avatar_resize_upload',0);     //是否需要resize
        $wpua_resize_w = get_option('wp_user_avatar_resize_w');     //宽
        $wpua_resize_h = get_option('wp_user_avatar_resize_h');     //高
        $wpua_resize_crop = get_option('wp_user_avatar_resize_crop');   //
        // Create attachment from upload
        if(!empty($_FILES['wpua-file'])) {
            $name = $_FILES['wpua-file']['name'];
            $type = $_FILES['wpua-file']['type'];
            $upload_dir = wp_upload_dir();
            if(is_writeable($upload_dir['path'])) {
                if(!empty($type) && preg_match('/(jpe?g|gif|png)$/i', $type)) {

                    $file = wp_handle_upload($_FILES['wpua-file'], array('test_form' => false));
                    if(isset($file['error'])){
                        wpapper_json_error(BigAppErr::$user['code'],'upload file error',$file['error']);
                    }
                    // Resize uploaded image
                    if((bool) $wpua_resize_upload == 1) {
                        // Original image
                        $uploaded_image = wp_get_image_editor($file['file']);
                        // Check for errors
                        if(!is_wp_error($uploaded_image)) {
                            // Resize image
                            $uploaded_image->resize($wpua_resize_w, $wpua_resize_h, $wpua_resize_crop);
                            // Save image
                            $resized_image = $uploaded_image->save($file['file']);
                        }
                    }
                    // Break out file info
                    $name_parts = pathinfo($name);
                    $name = trim(substr($name, 0, -(1 + strlen($name_parts['extension']))));
                    $url = $file['url'];
                    $file = $file['file'];
                    $title = $name;
                    // Use image exif/iptc data for title if possible
                    if($image_meta = @wp_read_image_metadata($file)) {
                        if(trim($image_meta['title']) && !is_numeric(sanitize_title($image_meta['title']))) {
                            $title = $image_meta['title'];
                        }
                    }
                    // Construct the attachment array
                    $attachment = array(
                        'guid'           => $url,
                        'post_mime_type' => $type,
                        'post_title'     => $title,
                        'post_content'   => ""
                    );
                    // This should never be set as it would then overwrite an existing attachment
                    if(isset($attachment['ID'])) {
                        unset($attachment['ID']);
                    }
                    // Save the attachment metadata
                    $attachment_id = wp_insert_attachment($attachment, $file);
                    if(!is_wp_error($attachment_id)) {
                        // Delete other avatar uploads by user
                        $q = array(
                            'author' => $user_id,
                            'post_type' => 'attachment',
                            'post_status' => 'inherit',
                            'posts_per_page' => '-1',
                            'meta_query' => array(
                                array(
                                    'key' => '_wp_attachment_wp_user_avatar',
                                    'value' => "",
                                    'compare' => '!='
                                )
                            )
                        );
                        $avatars_wp_query = new WP_Query($q);
                        while($avatars_wp_query->have_posts()){
                            $avatars_wp_query->the_post();
                            wp_delete_attachment($post->ID);
                        }
                        wp_reset_query();
                        $metadata = wp_generate_attachment_metadata($attachment_id,$file);
                        wp_update_attachment_metadata($attachment_id, wp_generate_attachment_metadata($attachment_id, $file));
                        // Remove old attachment postmeta about avatar
                        delete_metadata('post', null, '_wp_attachment_wp_user_avatar', $user_id, true);
                        // Create new attachment postmeta about avatar
                        update_post_meta($attachment_id, '_wp_attachment_wp_user_avatar', $user_id);
                        // Update usermeta
                        update_user_meta($user_id, $wpdb->get_blog_prefix($blog_id).'user_avatar', $attachment_id);
                    }
                }else{
                    wpapper_json_error(BigAppErr::$user['code'],'file type error');
                }
            }else{
                wpapper_json_error(BigAppErr::$user['code'],'upload file path cant write');
            }
        }else{
            wpapper_json_error(BigAppErr::$user['code'],'empty file');
        }
        return $url;
    }
    /**
     * get  data info from post 
     */
    public function get_user_info_from_post(){
        $user_info = array();
        if(isset($_POST['nickname'])){
            $user_info['nickname'] = sanitize_text_field($_POST['nickname']);
        }
        if(isset($_POST['password'])){
            $user_info['password'] = $_POST['password'];
            if(strlen($user_info['password']) < 4){
                wpapper_json_error(BigAppErr::$user['code'],'password length too short!');
            }
        }
        if(isset($_POST['description'])){
            $user_info['description'] = sanitize_text_field($_POST['description']);
        }
        if(isset($_POST['first_name'])){
            $user_info['first_name'] = sanitize_text_field($_POST['first_name']);
        }
        return $user_info;
    }
}
