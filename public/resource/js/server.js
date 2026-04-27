//

if (typeof toastr != 'undefined') {
	// 提示组件配置
	toastr.options = {
		"closeButton" : true,
		"debug" : false,
		"newestOnTop" : false,
		"progressBar" : true,
		"positionClass" : "toast-top-center",
		"preventDuplicates" : false,
		"onclick" : null,
		"showDuration" : "300",
		"hideDuration" : "1000",
		"timeOut" : "5000",
		"extendedTimeOut" : "1000",
		"showEasing" : "swing",
		"hideEasing" : "linear",
		"showMethod" : "fadeIn",
		"hideMethod" : "fadeOut"
	}
}
// 删除确认框
function del_confirm(title, msg, link) {
	var args1 = arguments;
	bootbox.dialog({
		message : msg,
		title : title,
		buttons : {
			main : {
				label : "Confirm",
				className : "btn-default",
				callback : function () {

					$.get(link, {
						/*csrf_test_name:$.cookie(CSRF_COOKIE_NAME)*/
					}, function (data) {
						var json = $.parseJSON(data);
						if (json.success) {
							if (args1.length > 3) {
								
								toastr.options.onShown = function () {
									var table = $('#' + args1[3]).DataTable();
									table.draw();
								}
								location.href = window.location.hash + '&_t=' + Math.random();
							}else{
								// location.href = '';
								location.href = window.location.hash + '&after=del';
							}
							toastr.success(json.msg);
						} else {
							toastr.error(json.msg);
						}
					});
				}
			},
			success : {
				label : "Cancel",
				className : "btn-primary",
				callback : function () {
					// nothing to do
				}
			}
		}
	});
}

function changefieldvalue(rowid, tbname, tbfieldname) {
	$.post(BASE_URL + "?c=ajax&m=setfieldvalue", {
		tbname : tbname,
		tbfield : tbfieldname,
		tbfieldvalue : $("#" + tbfieldname + rowid).val(),
		id : rowid,
		csrf_test_name : $.cookie('csrf_cookie_name')
	},
		function (data) {});
}

function reset_dialog() {
	var dialog = top.dialog.get(window);
	// var dialog = top.dialog.get('dialog1');
	if (dialog) {
		dialog.width(768);
		dialog.height($('body').height() + 25);
		dialog.reset();
	}
}

function art_open(title, url, tableid) {
	var d = dialog({
			id : 'dialog2',
			title : title,
			url : url,
			width : 768,
			fixed : true,
			onremove : function () {
				var table = $('#' + tableid).DataTable();
				table.draw(false);
			}
		});
	d.showModal();
}


$(function () {

	$.validator.setDefaults({
		ignore : "",
		highlight : function (element) {
			$(element).closest('.form-group').removeClass('has-success').addClass('has-error');
			reset_dialog();
		},
		unhighlight : function (element) {
			$(element).closest('.form-group').removeClass('has-error');
			reset_dialog();
		}
	});

	$(document).on('click', '.cancel-form', function () {
		var dialog = top.dialog.get(window);
		if (typeof dialog != 'undefined') {
			dialog.remove();
		}
	});
});

$(function () {

	$(document).on('click', '.sidebar-menu a', function () {
		$(this).parent('li').addClass('active');
		$(this).parent('li').siblings('li').removeClass('active');
	});
}); ;
(function ($) {
	/*
	 *  javascript复杂对象转url参数字符串
	 */
	var parseParam = function (param, key) {
		var paramStr = "";
		if (param instanceof String || param instanceof Number || param instanceof Boolean) {
			paramStr += "&" + key + "=" + encodeURIComponent(param);
		} else {
			$.each(param, function (i) {
				var k = key == null ? i : key + (param instanceof Array ? "[" + i + "]" : "." + i);
				paramStr += '&' + parseParam(this, k);
			});
		}
		return paramStr.substr(1);
	};
	var loadURL = function (uri) {
		$('.content-wrapper').load(BASE_URL + uri, function (response, status, xhr) {
			var source = '<section class="content-header">\
								      <h1>\
								        Msg\
								        <small></small>\
								      </h1>\
								      <ol class="breadcrumb">\
								        <li><a href="#/"><i class="fa fa-dashboard"></i> Dashboard</a></li>\
								        <li class="active">Message</li>\
								      </ol>\
								    </section>\
								    <section class="content">\
										<div class="alert alert-<%= status %> alert-dismissible">\
								            <h4><i class="icon fa fa-<%= icon %>"></i> Notice</h4>\
								            <%= msg %>\
								        </div>\
								    </section>';

			if (status == 'error') {
				var msg = "Sorry but there was an error: ";
				msg = msg + xhr.status + " " + xhr.statusText;
				var render = template.compile(source);
				var html = render({
						icon : 'ban',
						status : 'danger',
						msg : msg
					});
				$('.content-wrapper').html(html);
			} else {
				if (response.indexOf('{"success":') !== -1) {
					var json = $.parseJSON(response);
					if (json.success) {}
					else {
						var msg = json.msg;
						var render = template.compile(source);
						var html = render({
								icon : 'info',
								status : 'info',
								msg : msg
							});
						$('.content-wrapper').html(html);
					}
				}
			}
		});
	};
	
(function($){
  // remember last AJAX request so we can soft-refresh after actions
  window.LAST_REQUEST_URI = null;
  var __originalLoadURL = loadURL;
  loadURL = function(uri){
    window.LAST_REQUEST_URI = uri;
    __originalLoadURL(uri);
  };

  // highlight active item in sidebar
  function setActiveFromPath(pathname) {
    try {
      var parts = pathname.replace(/^\/+|\/+$/g, '').split('/'); // trim slashes
      var ctrl  = (parts[1] || '').toLowerCase();                 // parts[0] === 'admin'
      $('.sidebar-menu li').removeClass('active');
      if (ctrl) {
        $('.sidebar-menu a[href="/admin/' + ctrl + '"]').closest('li').addClass('active');
      } else {
        $('.sidebar-menu a[href="/admin/"]').closest('li').addClass('active');
      }
    } catch(e){}
  }

  // one-time shim: convert legacy "#/..." to clean "/admin/..."
  (function hashToPushStateShim(){
    if (location.hash && location.hash.indexOf('#/') === 0) {
      var frag = location.hash.substring(2); // after "#/"
      var target = '/admin/' + frag.replace(/^\/+/, '');
      history.replaceState(null, '', target);
    }
  })();

  $(function(){
    var app = $.sammy(function(){
      this.use(Sammy.PushLocation); // HTML5 pushState (no "#")

      // /admin/ -> dashboard
      this.get('/admin/', function(){
        setActiveFromPath('/admin/');
        loadURL('?c=welcome&m=dashboard');
      });

      // /admin/:controller
      this.get('/admin/:controller', function(){
        setActiveFromPath(this.path);
        var c = this.params.controller;
        loadURL('?c=' + encodeURIComponent(c));
      });

      // /admin/:controller/:method
      this.get('/admin/:controller/:method', function(){
        setActiveFromPath(this.path);
        var p = this.params;
        loadURL('?c=' + encodeURIComponent(p.controller)
              + '&m=' + encodeURIComponent(p.method));
      });

      // /admin/:controller/:method/:param1
      this.get('/admin/:controller/:method/:param1', function(){
        setActiveFromPath(this.path);
        var p = this.params;
        loadURL('?c=' + encodeURIComponent(p.controller)
              + '&m=' + encodeURIComponent(p.method)
              + '&param1=' + encodeURIComponent(p.param1));
      });

      // /admin/:controller/:method/:param1/:param2
      this.get('/admin/:controller/:method/:param1/:param2', function(){
        setActiveFromPath(this.path);
        var p = this.params;
        loadURL('?c=' + encodeURIComponent(p.controller)
              + '&m=' + encodeURIComponent(p.method)
              + '&param1=' + encodeURIComponent(p.param1)
              + '&param2=' + encodeURIComponent(p.param2));
      });
    });

    // start router at /admin/
    app.run('/admin/');

    // expose for other scripts (e.g., del_confirm soft-refresh)
    window.__sammyApp = app;

    // intercept internal admin links to prevent full reloads
    $(document).on('click', 'a[href^="/admin/"]', function(e){
      if (e.metaKey || e.ctrlKey || this.target === '_blank') return;
      var href = this.getAttribute('href') || '';
      if (/^\/admin(\/.*)?$/.test(href)) {
        e.preventDefault();
        app.setLocation(href);
      }
    });

    // keep active menu on back/forward
    window.addEventListener('popstate', function(){
      setActiveFromPath(location.pathname);
    });
  });
})(jQuery);

