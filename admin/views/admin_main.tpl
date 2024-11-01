<script type="text/javascript"> var _wpapper_obj = {bigapp_data}; </script>
<script type="text/javascript">
    String.prototype.isEmail = function() {
        var val = this;
        if (!val.match(/^[a-zA-Z0-9_-]+@[a-zA-Z0-9_-]+(\.[a-zA-Z0-9_-]+)+$/) || val == "") {
            return false;
        } else {
            return true;
        }
    };
    function checkValue() {
        var email = document.getElementsByName("email")[0].value;
        var url = document.getElementsByName("url")[0].value

        if (email == '' || url == '') {
            alert("Empty");
            return false;
        } else if (!email.isEmail()) {
            alert("Email error!");
            return false;
        } else {
            alert("Has been sent!");
            window.location.reload();
            return true;
        }
    }

</script>

<table>
    <tr>
        <td><h3>Welcome to Wpapper, you can create native app (Android & iOS) at
                <a href="http://wpapper.com/?page_id=31" target="_blank">Create Mobile App</a></h3>
            <h3>and you can see the demo at <a target="_blank" href="https://play.google.com/store/apps/details?id=com.wpapper.blog">Google Play Store</a></h3>
        </td>
    </tr>
    <tr>
        <td>

        </td>
    </tr>
</table>
<hr style="height:1px;border:none;border-top:1px dashed #555555;" />
<form action="http://wpapper.com/?wpapper_app=1&api_route=wpapper_api&action=get_test_app" method="post" target="myIframe" >
<p>Get Test App(We will send generated apk link to this email)</p>
<table class="form-table">
    <tbody><tr>
        <th scope="row"><label for="email">Email:</label></th>
        <td><input name="email" type="text" id="email" value="" class="regular-text"></td>
    </tr>
    <tr>
        <th scope="row"><label for="url">Web url:</label></th>
        <td><input name="url" type="text" id="url" value="" class="regular-text"></td>
    </tr>
    </tbody></table>
<p class="submit"><input type="submit" class="button button-primary" onclick="return checkValue();" value="Get test app"></p>
</form>
<hr style="height:1px;border:none;border-top:1px dashed #555555;" />
<iframe name="myIframe" style="display:none"></iframe>
<form id="uz">
            <table class="wp-list-table widefat plugins">
                <thead>
                    <tr>
                        <th scope="col" id="name" class="manage-column column-name" style="">Function</th>
                        <th scope="col" id="description" class="manage-column column-description" style="">Function Description</th>
                    </tr>
                </thead>

                <tbody>
                    <tr class="active" id="menu_info">
                        <td class="plugin-title"><strong>Menu Setting</strong>
                            <div class="row-actions visible">
                                <a class="tpl-link" href="javascript:;">Setting</a> |
                                <a class="tpl-switch" href="javascript:;" title="停用该插件">Stop</a>
                            </div>
                        </td>
                        <td class="column-description">
                            Set up the menu displayed on the mobile APP.
                        </td>
                    </tr>
                </tbody>

                <tbody>
                    <tr class="active" id="fcm_setting_info">
                        <td class="plugin-title"><strong>Push Notification Settings</strong>
                            <div class="row-actions visible">
                                <a class="tpl-link" href="javascript:;">Setting</a>
                            </div>
                        </td>
                        <td class="column-description">

                        </td>
                    </tr>
                </tbody>

                <tbody>
                    <tr class="active" id="send_notification_info">
                        <td class="plugin-title"><strong>Send Push Notification</strong>
                            <div class="row-actions visible">
                                <a class="tpl-link" href="javascript:;">Send</a>
                            </div>
                        </td>
                        <td class="column-description">

                        </td>
                    </tr>
                </tbody>
                <tbody>
                <tr class="active" id="app_style">
                    <td class="plugin-title"><strong>App Theme Settings</strong>
                        <div class="row-actions visible">
                            <a class="tpl-link" href="javascript:;">Setting</a>
                        </div>
                    </td>
                    <td class="column-description">

                    </td>
                </tr>
                </tbody>
            </table>

    </form>
<script>
    (function($){
        $(function(){
            //$("#image-simple_img").attr('src',_wpapper_obj.data.menu_info.simple_img);
            uz.drawIndex(_wpapper_obj.data,_wpapper_obj.ajax_url);
        });
    })(jQuery)

</script>
