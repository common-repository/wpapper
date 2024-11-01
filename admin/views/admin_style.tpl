<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title></title>
    <!--<script src="<%plugin_path%>/js/uz/mobile_style.js" charset="utf-8"></script> -->
</head>
<body id="uz">
<script type="text/javascript"> var _wpapper_obj = {bigapp_data}; </script>
<div class="wrap control-section wp-admin wp-core-ui js  nav-menus-php auto-fold admin-bar branch-4-2 version-4-2-2 admin-color-fresh locale-zh-cn customize-support svg menu-max-depth-0" id="wpbody-content">
	<div id="uz">
		<h2>Set the native app theme(You do not need to regenerate the app)</h2>
	</div>
	<div class="clear"></div>
	<br/>
	<br/>
    <div id="poststuff">
        <table class="tb tb2" border="1px solid #151515;" cellspacing="0">
            <tr>
                <td width='180'>Title bar color：</td>
                <td width='186'><input id='title_bar_color' value="#ffffff" class="jscolor {hash:true}" style="width:100%"/></td>
                <td rowspan="2">
                    <img id="style_setting_img" src='' style='width:180px;height:320px;'/>
                </td>
            </tr>

            <tr>
                <td width='180'>Sliding menu color：</td>
                <td width='186'><input id='sliding_menu_color' value="#000000" class="jscolor {hash:true}" style="width:100%"/></td>
            </tr>

            <tr>
                <td width='180' >List style：</td>
                <td width='186' >
                    <select id="list_style">
                        <option value="1">1:List</option>
                        <option value="2">2:One Column Grid</option>
                        <option value="3">3:Two Column Grid</option>
                        <option value="4">4:Two Staggered Column</option>
                    </select>
                </td>
                <td >
                    <img id="list_img" src='' style='width:120px;height:210px;'/>
                    <img id="one_column_img" src='' style='width:120px;height:210px;'/>
                    <img id="two_column_img" src='' style='width:120px;height:210px;'/>
                    <img id="two_staggered_img" src='' style='width:120px;height:210px;'/>
                </td>
            </tr>

            <tr>
                <td colspan="3">
                    <input type="button" id='subbtn' class="button button-primary" style='padding:2px 8px;' value="Submit"/>
                </td>
            </tr>
        </table>
    </div>
  </div>

<style>
    .yzd-input-file{ position: relative; width: 250px; overflow: hidden; border:1px #999 solid; padding: 2px; background: #f9f9f9;}
    .yzd-input-file:hover{ border-color: #0099cc;}
    .yzd-input-file .btn-file{ float: left; width: 62px; height: 20px; line-height: 0; color: #000; padding: 0; margin: 0 5px 0 0; cursor: pointer;}
    .yzd-input-file .input-file{ position: absolute; left: 0; top: 0; width: 100%; height: 100%; padding: 0; border:none; opacity: 0; filter:alpha(opacity = 0);}
    .yzd-input-file .file-result{ overflow: hidden; font-size: 12px; line-height: 20px; color: #333333; }
    .yzd-input-file .img-show{ margin-top: 5px; border:1px #ccc solid; padding: 2px;}
    .yzd-input-file .img-show img{ width: 250px;}
    .yzd-input-file.done .file-result{ display: block; }
</style>

</body>
</html>
