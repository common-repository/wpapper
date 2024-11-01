/*
 * 发布管理页面JS
 */

var jq = jQuery.noConflict();
var UZ;
(function ($) {

    UZ = function () {

        this.init = function() {
            var thiso = this;

            $("#subbtn").click(function(){
				thiso.submit();
            });
			
            set_value("list_style",_wpapper_obj.list_style);
            set_value("title_bar_color",_wpapper_obj.title_bar_color);
            set_value("sliding_menu_color",_wpapper_obj.sliding_menu_color);

            $("#style_setting_img").attr('src',_wpapper_obj.style_setting_url);
            $("#list_img").attr('src',_wpapper_obj.list_url);
            $("#one_column_img").attr('src',_wpapper_obj.one_column_url);
            $("#two_column_img").attr('src',_wpapper_obj.two_column_grid_url);
            $("#two_staggered_img").attr('src',_wpapper_obj.two_staggered_url);
        };

        this.submit = function() {
            var params = {
                "list_style": get_text_value("list_style"),
                "title_bar_color": get_text_value("title_bar_color"),
                "sliding_menu_color": get_text_value("sliding_menu_color")
            };
            //print_r(params);
            //return;


            var thiso = this;
            $.ajax({
                type: "get",
                async: false,
                url: _wpapper_obj.ajax_url,
                data: params,
                dataType: "json",
                success: function (res) {
                    if (res.error_code==0) {
					    alert("Success");
					} else {
                        if (res.error_code==100803) {
                            alert("No permission");
                        } else {
                            alert(res.error_msg);
                        }
					}
                },
                error: function (data) {
					print_r(data); return;
                    alert("Fail");
                }
            });
        };
    };

    $(function () {
        var app = new UZ();
        app.init();
    })

})(jq);
