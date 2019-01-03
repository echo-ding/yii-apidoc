(function($) {
	$.extend($, {
		error_codes: {
			"SUCCESS": 0,
			"NOT_LOGIN": 1002
		},
		util: {
			getQueryString: function(name) {
				var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
				var r = window.location.search.substr(1).match(reg);
				if (r != null) return decodeURIComponent(r[2]); return null;
			},
			randomString: function(len) {
			　　len = len || 13;
			　　var $chars = 'ABCDEFGHJKMNPQRSTWXYZabcdefhijkmnprstwxyz0123456789!@#$%^&*';    /****默认去掉了容易混淆的字符oOLl,9gq,Vv,Uu,I1****/
			　　var maxPos = $chars.length;
			　　var str = '';
			　　for (i = 0; i < len; i++) {
					str += $chars.charAt(Math.floor(Math.random() * maxPos));
			　　}
			　　return str;
			}
		},
		tpl: {
			draw: function(tpl, data){
				var content = tpl.replace(/\{(\w+)\}/g, function(m, key) {
			        if(typeof(data[key]) != "undefined") {
			            return data[key];
			        } else {
			            return m;
			        }
			    });
			    return content;
			}
		},
		web: {
			request: function(interface, key, request_params, ajax_params) {
				var config = interface.get(key);
				$.ajax({
					url: config.url,
					type: config.method,
					dataType: config.dataType,
					data: request_params,
					timeout: ajax_params.hasOwnProperty("timeout") ? ajax_params.timeout : 5000,
					success: ajax_params.hasOwnProperty("success") ? (ajax_params.success) : function() {},
					error: ajax_params.hasOwnProperty("error") ? (ajax_params.error) : function() {}
				});
			}
		}
	});
})(jQuery || Zepto);