<?php 
$version = get_bloginfo('version');

/*
*
* All the functions for the settings page
*
*/
function wpapper_register_settings() {
	add_settings_section('gcm_setting-section', '', 'gcm_section_callback', 'wpapper-gcm');
	add_settings_field('api-key', __('Api Key','wpapper_gcm'), 'api_key_callback', 'wpapper-gcm', 'gcm_setting-section');
	//add_settings_field('snpi', __('New post info','wpapper_gcm'), 'snpi_callback', 'wpapper-gcm', 'gcm_setting-section');
    //add_settings_field('supi', __('Updated post info','wpapper_gcm'), 'supi_callback', 'wpapper-gcm', 'gcm_setting-section');
    add_settings_field('abd', __('Display admin bar link','wpapper_gcm'), 'abd_callback', 'wpapper-gcm', 'gcm_setting-section' );
	add_settings_field('debug', __('Show debug response','wpapper_gcm'), 'debug_callback', 'wpapper-gcm', 'gcm_setting-section' );
	register_setting( 'wpapper-gcm-settings-group', 'gcm_setting', 'wpapper_gcm_settings_validate' );
}
 
function wpapper_gcm_load_textdomain() {
  load_plugin_textdomain( 'wpapper_gcm', false, basename( dirname( __FILE__ ) ) . '/lang' );
}

function gcm_section_callback() {
    echo __('Required settings for the plugin and the App.','wpapper_gcm');
}

function api_key_callback() {
    $options = get_option('gcm_setting');
    ?>
    <input type="text" name="gcm_setting[api-key]" size="41" value="<?php echo $options['api-key']; ?>" />
    <?php
}

function snpi_callback(){
    $options = get_option('gcm_setting');
	$html = '<input type="checkbox" id="snpi" name="gcm_setting[snpi]" value="1"' . checked( 1, $options['snpi'], false ) . '/>';
	echo $html;
}

function supi_callback(){
    $options = get_option('gcm_setting');
	$html= '<input type="checkbox" id="supi" name="gcm_setting[supi]" value="1"' . checked( 1, $options['supi'], false ) . '/>';
	echo $html;
}

function abd_callback() {
    $options = get_option('gcm_setting');
    $html = '<input type="checkbox" id="abd" name="gcm_setting[abd]" value="1"' . checked( 1, $options['abd'], false ) . '/>';
	echo $html;
}

function debug_callback() {
    $options = get_option('gcm_setting');
    $html = '<input type="checkbox" id="debug" name="gcm_setting[debug]" value="1"' . checked( 1, $options['debug'], false ) . '/>';
	echo $html;
}

function wpapper_gcm_settings_validate($arr_input) {
        $options = get_option('gcm_setting');
        $options['api-key'] = trim( $arr_input['api-key'] );
	    $options['snpi'] = trim( $arr_input['snpi'] );
        $options['supi'] = trim( $arr_input['supi'] );
        $options['abd'] = trim( $arr_input['abd'] );      
		$options['debug'] = trim( $arr_input['debug'] ); 
        return $options;
}

/*
*
* Send notification for post update
*
*/
function wpapper_update_notification($new_status, $old_status, $post) {
    $options = get_option('gcm_setting');
    if($options['snpi'] != false){
        if ($old_status == 'publish' && $new_status == 'publish' && 'post' == get_post_type($post)) {
            $post_title = get_the_title($post);
            $post_url = get_permalink($post);
            $post_id = get_the_ID($post);
            $post_author = get_the_author_meta('display_name', $post->post_author);
            $message = $post_title . ";" . $post_url . ";". $post_id . ";" . $post_author . ";";

            // Send notification
            $up = "update";
            wpapper_sendGCM($message, $up);
        }
    }
}

/*
*
* Send notification for new post
*
*/
function wpapper_new_notification($new_status, $old_status, $post) {
    $options = get_option('gcm_setting');
    if($options['snpi'] != false){
        if ($old_status != 'publish' && $new_status == 'publish' && 'post' == get_post_type($post)) {

    	    $post_title = get_the_title($post);
    	    $post_url = get_permalink($post);
	        $post_id = get_the_ID($post);
	        $post_author = get_the_author_meta('display_name', $post->post_author);
	        $message = $post_title . ";" . $post_url . ";". $post_id . ";" . $post_author . ";";

            // Send notification
	        $np = "new_post";
            wpapper_sendGCM($message, $np);
        }
   }
}

/*
*
* Register ToolBar
*
*/
function wpapper_gcm_toolbar() {
    $options = get_option('gcm_setting');
    if($options['abd'] != false){
	    global $wp_admin_bar;
	    $page = get_site_url().'/wp-admin/admin.php?page=wpapper_admin&action=send_notification';
	    $args = array(
		    'id'     => 'wpapper_gcm',
		    'title'  => '<img class="dashicons dashicons-cloud">PUSH</img>', 'wpapper_gcm',
		    'href'   =>  "$page",
	    );
	    $wp_admin_bar->add_menu($args);
    }
}

/*
*
* GCM Send Notification
*
*/
function wpapper_sendGCM($title, $body) {
    $title = strip_tags($title);
    $body = strip_tags($body);

    $notification = array(
        'title' => $title ,
        'body' => $body
    );

	$options = get_option('gcm_setting');
    $apiKey = $options['api-key'];
    $url = 'https://android.googleapis.com/gcm/send';
    $id = wpapper_getIds();

	if($id >= 1000){
		$newId = array_chunk($id, 1000);
		foreach ($newId as $inner_id) {
			$fields = array(
        		'registration_ids' => $inner_id,
                //'data' => array( $type => $message ),
                'notification' => $notification,'priority'=>'high'
            );

			$headers = array(
    			'Authorization: key=' . $apiKey,
    			'Content-Type: application/json');
			
			$ch = curl_init();
			curl_setopt( $ch, CURLOPT_URL, $url );
    		curl_setopt( $ch, CURLOPT_POST, true );
    		curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers);
    		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
    		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode( $fields ));
			$result = curl_exec($ch);
		}
	}else {
		$fields = array(
        	'registration_ids' => $id,
        	//'data' => array( $type => $message ),);
            'notification' => $notification,'priority'=>'high'
        );
		$headers = array(
    		'Authorization: key=' . $apiKey,
    		'Content-Type: application/json');
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $url );
    	curl_setopt( $ch, CURLOPT_POST, true );
    	curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers);
    	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
    	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode( $fields ));
		$result = curl_exec($ch);
	}
    $answer = json_decode($result);
    $cano = wpapper_canonical($answer);
    $suc = $answer->{'success'};
    $fail = $answer->{'failure'};
	$options = get_option('gcm_setting');
    if($options['debug'] != false){
		$inf= "<div id='message' class='updated'><p><b>".__('Message sent.','wpapper_gcm')."</b><i>&nbsp;&nbsp;($body)</i></p><p>$result</p></div>";
	}else{
    	$inf= "<div id='message' class='updated'><p><b>".__('Message sent.','wpapper_gcm')."</b><i>&nbsp;&nbsp;($body)</i></p><p>".__('success:','wpapper_gcm')." $suc  &nbsp;&nbsp;".__('fail:','wpapper_gcm')." $fail </p></div>";
    }
	curl_close($ch);
	print_r($inf);    
    return $result;
}

function wpapper_getIds() {
    global $wpdb;
    $wpapper_table_name = $wpdb->prefix.'fcm_users';
    $devices = array();
    $sql = "SELECT gcm_regid FROM $wpapper_table_name";
    $res = $wpdb->get_results($sql);
    if ($res != false) {
        foreach($res as $row){
            array_push($devices, $row->gcm_regid);
        }
    }
    return $devices;
}

function wpapper_canonical($answer) {
   $allIds = wpapper_getIds();
   $resId = array();
   $errId = array();
   $err = array();
   $can = array();
   global $wpdb;
   $wpapper_table_name = $wpdb->prefix.'fcm_users';

   foreach($answer->results as $index=>$element){
    if(isset($element->registration_id)){
     $resId[] = $index;
    }
   }

   foreach($answer->results as $index=>$element){
    if(isset($element->error)){
      $errId[] = $index;
    }
   }

   for ($i=0; $i<count($allIds); $i++) {
    array_push($can, $allIds[$resId[$i]]);
   }

   for ($i=0; $i<count($allIds); $i++) {
    array_push($err,$allIds[$errId[$i]]);
   }

   if($err != null) {
	for($i=0; $i < count($err); $i++){
		$s = $wpdb->query($wpdb->prepare("DELETE FROM $wpapper_table_name WHERE gcm_regid=%s",$err[$i]));
	}
   } 
   if($can != null) {
	for($i=0; $i < count($can); $i++){
		$r = $wpdb->query($wpdb->prepare("DELETE FROM $wpapper_table_name WHERE gcm_regid=%s",$can[$i]));
	}
   }
}

add_action('plugins_loaded', 'wpapper_gcm_load_textdomain');
add_action('admin_init', 'wpapper_register_settings');
add_action('transition_post_status', 'wpapper_update_notification',10,3);
add_action('transition_post_status', 'wpapper_new_notification',10,3);
add_action('wp_before_admin_bar_render', 'wpapper_gcm_toolbar', 999);

?>