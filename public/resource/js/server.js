// server.js v20260526 — Sammy.js SPA router removed; CI4 serves full admin pages.

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
	var adminDispatchBase = function () {
		var base = (typeof BASE_URL !== 'undefined' ? BASE_URL : '/');
		return base.replace(/\/?$/, '/') + 'admin';
	};

	var contentAlreadyRendered = function () {
		var $cw = $('.content-wrapper');
		if (!$cw.length) {
			return false;
		}
		return $cw.find('section.content .container-fluid').children().length > 0
			|| $cw.find('.card, table.dataTable, form, .profile-user-img').length > 0;
	};

	var loadURL = function (uri) {
		var fetchUrl = (uri.charAt(0) === '?') ? adminDispatchBase() + uri : (BASE_URL + uri);
		$('.content-wrapper').load(fetchUrl, function (response, status, xhr) {
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
				if (xhr.status === 401) {
					var login401 = (typeof BASE_URL !== 'undefined' ? BASE_URL : '/') + 'admin/login';
					try {
						var j401 = $.parseJSON(xhr.responseText || response || '{}');
						if (j401 && j401.redirect) {
							login401 = j401.redirect;
						}
					} catch (e401) {}
					window.location.href = login401;
					return;
				}
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
						if (json.code === 'session_expired' && json.redirect) {
							window.location.href = json.redirect;
							return;
						}
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

  // one-time shim: convert legacy "#/..." to clean "/admin/..." (and ?m=add → /add)
  (function hashToPushStateShim(){
    if (location.hash && location.hash.indexOf('#/') === 0) {
      var frag = location.hash.substring(2).replace(/^\/+/, '');
      var qIdx = frag.indexOf('?');
      var pathPart = qIdx >= 0 ? frag.substring(0, qIdx) : frag;
      var queryPart = qIdx >= 0 ? frag.substring(qIdx + 1) : '';
      var params = new URLSearchParams(queryPart);
      var m = params.get('m');
      if (m && m !== 'index') {
        pathPart = pathPart.replace(/\/+$/, '') + '/' + m;
        params.delete('m');
      }
      var remaining = params.toString();
      var target = '/admin/' + pathPart + (remaining ? '?' + remaining : '');
      history.replaceState(null, '', target);
    }
  })();

  $(function(){
    // Legacy Sammy.js SPA is disabled. CI4 serves full admin pages; boot-time app.run() and
    // link interception were calling loadURL() and replacing .content-wrapper with the
    // site root (Home::index → CodeIgniter welcome page).
    window.loadURL = loadURL;

    var path = window.location.pathname || '';
    if (path.indexOf('/admin') === 0) {
      setActiveFromPath(path);
    }

    window.addEventListener('popstate', function () {
      setActiveFromPath(location.pathname);
    });

    // Any admin AJAX returning 401 (session expired) → sign-in page
    $(document).ajaxComplete(function (_evt, xhr) {
      if (xhr.status !== 401) return;
      if (window.location.pathname.indexOf('admin/login') !== -1) return;
      var login = (typeof BASE_URL !== 'undefined' ? BASE_URL : '/') + 'admin/login';
      try {
        var j = $.parseJSON(xhr.responseText || '{}');
        if (j && j.redirect) login = j.redirect;
      } catch (ex) {}
      window.location.href = login;
    });
  });
})(jQuery);

