(function($) {
	function Dialog(c) {

		this.opt = $.extend({
			width : 400,
			title : "系统提示",
			content : "",
			isModal : false,
			destroyOnClose : false,
			listeners : {}
		}, c);

		this.init();

	}

	$.extend(Dialog.prototype, {
		init : function() {
			this.node = $('<div class="dialog"><h3 class="title">' + this.opt.title + '</h3><div class="content">' + this.opt.content + '</div></div>');
			$("body").append(this.node);
			this.node.css({
				'width' : this.opt.width,
				'left' : ($(window).width() - this.opt.width) / 2,
				//'top' : $("body").scrollTop() + Math.min(($(window).height() - this.node.height()) / 2 - 100, 300),
				'top' : Math.min(($(window).height() - this.node.height()) / 2 - 50, 300),
				'z-index' : 999
			});

			var self = this;
			var origLeft, origTop, startX, startY, moving = false;

			this.mask = null;
			if(this.opt.isModal) {
				this.mask = $('<div class="mask"></div>');
				this.mask.css('z-index', 998);
				this.node.before(this.mask);
			}

			$.isFunction(this.opt.listeners['init']) && this.opt.listeners['init'](this.node);
		},

		close : function() {
			if(this.opt.destroyOnClose) {
				this.destroy();
			} else {
				this.hide();
			}
		},

		show : function() {
			if(this.node) {
				this.node.show();
				this.mask && this.mask.show();
			} else {
				this.init();
			}
		},

		hide : function() {
			if(this.node) {
				this.node.hide();
				this.mask && this.mask.hide();
			}
		},

		destroy : function() {
			if(this.node) {
				this.node.remove();
				this.mask && this.mask.remove();
			}
			this.node = null;
			this.mask = null;
		}
	});

	function getStyle(type) {
		if(typeof(type) == "string") {
			return type;
		}

		if(typeof(type) == "undefined") {
			type = 1;
		}

		switch(type) {
			case 1:
				return "btn bg-color-red text-color-white clickable";
			case 2:
				return "btn bg-color-brown-light text-color-white clickable";
			case 3:
				return "btn bg-color-red-dark text-color-white clickable";
			case 4:
				return "btn bg-color-gray-dark text-color-white clickable";
			case 5:
				return "btn bg-color-blue text-color-white clickable";
			case 6:
				return "btn bg-color-green text-color-white clickable";
			default:
				return 1;
		}
	}

	$.extend($, {
		dialog : function(c) {
			var dialog = new Dialog(c);

			return {
				close : function() {
					dialog.close();
				},

				show : function() {
					dialog.show();
				},

				hide : function() {
					dialog.hide();
				}
			}
		},

		showMessage : function() {
			var title = null, message = null, buttons = null;
			if(arguments.length < 1) {
				return;
			}

			if(arguments.length == 1) {
				message = arguments[0];
			} else if(arguments.length == 2) {
				if(typeof(arguments[1]) == 'string') {
					title = arguments[0];
					message = arguments[1];
				} else {
					message = arguments[0];
					buttons = arguments[1];
				}
			} else {
				title = arguments[0];
				message = arguments[1];
				buttons = arguments[2];
			}

			var dialog = null;

			var content = '<div class="message"></div>';
			var config = {
				isModal : true,
				content : content,
				destroyOnClose : true
			};

			if(title) {
				config['title'] = title;
			}

			dialog = new Dialog(config);

			dialog.node.find(".message").html(message);

			if($.isFunction(buttons)) {
				var onclick = buttons;
				buttons = [{ text : '确认', style : 6, click : onclick }];
			} else if(!buttons || !$.isArray(buttons) || !buttons.length) {
				buttons = [{ text : '我知道了', style : 6 }];
			}

			var buttonPanel = $('<div class="buttons"></div>');
			dialog.node.find(".message").after(buttonPanel);
			for(var i = 0; i < buttons.length; i++) {
				var btn = buttons[i];
				var btnNode = $('<div class="' + getStyle(btn['style']) + '" idx="' + i + '">' + btn['text'] + '</button>');
				buttonPanel.append(btnNode);
				btnNode.click(function() {
					var idx = parseInt($(this).attr("idx"));
					if(!$.isFunction(buttons[idx]['click']) || buttons[idx]['click']() !== false) {
						dialog.close();
					}
				});
			}
			
			return {
				close : function() {
					dialog.close();
				}
			}
		}
		
	});
}) (jQuery);