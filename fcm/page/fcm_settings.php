<?php

function wpapper_display_fcm_setting_page() {
$plugins_url = plugins_url();
?>
  <div class="wrap">
        <?php screen_icon(); ?>
	<h2 class="">Push Notification Settings, we use Google Firebase Cloud Messagin </h2>
	
	<div id="poststuff">
	
    	<?php if( isset($_GET['settings-updated']) ) { ?>
        <div id="message" class="updated">
            <p><strong><?php _e('Settings saved','wpapper_gcm') ?></strong></p>
        </div>
        <?php } ?>
	
		<div id="post-body" class="metabox-holder columns-1">
	
			<div id="post-body-content">
					<div class="postbox">
					  <h3><?php _e('GCM Settings','wpapper_gcm'); ?></h3>
					  <h3>More info at. <a href="https://firebase.google.com/docs/cloud-messaging/">Firebase Cloud Messaging</a></h3>
						<div class="inside">
							<div id="settings">
							 <form method="post" action="options.php">
					           <?php settings_fields('wpapper-gcm-settings-group'); ?>
					           <?php do_settings_sections('wpapper-gcm'); ?>
	                              <?php submit_button(); ?>
	                           </form>
	                        </div>
							<p>Read <a target="_blank" href="https://firebase.google.com/docs/cloud-messaging/">FCM</a> for help!</p>
						</div> 
					</div>
                    <p><i>More info at <a href="http://wpapper.com">WpApper.com</a></i></p>
			</div>
		</div> 
		<br class="clear">
	</div>
</div>
<?php
}

?>