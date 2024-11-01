<?php

require_once dirname(dirname(__FILE__)) . "/options.php";

function wpapper_display_send_notification_page() {
    global $wpdb;
    $wpapper_table_name = $wpdb->prefix.'fcm_users';
    $result = $wpdb->get_var( "SELECT COUNT(*) FROM $wpapper_table_name" );

    if($result != false) {
        $num_rows = $result;
    }else {
        $num_rows = 0;
    }
    $info = sprintf(__('Currently %s users are registered','wpapper_gcm'),$num_rows);
  
?>

<div class="wrap">
	<h2 class="">Send Push Notification</h2>
	<div id="poststuff">
		<div id="post-body" class="metabox-holder columns-2"> 
			<!-- main content -->
			<div id="post-body-content">
                <div class="postbox">
                    <h3>More info at<a href="http://wpapper.com">click here</a></h3>
						<div class="inside">
							 <form method="post" action="#">
							     <p><?php _e('Push message title:','wpapper_gcm'); ?></p>
                                 <input id="title", name="title", type="text">
                                 <p><?php _e('Push message body:','wpapper_gcm'); ?></p>
                                 <textarea id="body" name="body" type="text" cols="20" rows="5" ></textarea>
                                 <?php submit_button('Send'); ?>
	                         </form>
						</div> 
					</div>
					<p><b><?php _e('Info','wpapper_gcm'); ?> &nbsp;&nbsp;</b> <?php echo $info ?></p>
					<p></p>
			</div>
		</div> 
		<br class="clear">
	</div>
</div> 
<?php
}

if(isset($_POST['body']) && isset($_POST['title'])) {
    $title = sanitize_text_field($_POST['title']);
    $body = sanitize_text_field($_POST["body"]);
    wpapper_sendGCM($title, $body);
}
?>
