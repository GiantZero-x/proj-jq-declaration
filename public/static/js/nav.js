/**
 * Created by giantR on 2017/4/20.
 */
/* 主页js */
!function ($) {
  $(function () {
    // 左侧导航切换
    var path = location.pathname.split('/'),
      sys = '', // 一级
      mod = ''; // 二级
    try {
      path = path.slice(3);
      sys = path[0];
      mod = path[1].replace('.html','');
    } catch (e) {
      sys = mod = 'index';
    }
    
    var breadcrumbList = [];
    switch (sys) {
      case 'index':
        breadcrumbList.push({
          name: '报关单管理',
          icon: 'fa-home'
        });
        switch (mod) {
          case 'detail':
            breadcrumbList.push({
              name: '报关单详情'
            });
            break;
          case 'check':
            breadcrumbList.push({
              name: '报关单审核'
            });
        }
        break;
      
      case 'new_clearance':
        breadcrumbList.push({
          name: '新建报关单',
          icon: 'fa-edit'
        });
        break;
      
      case 'clearance':
        breadcrumbList.push({
          name: '报关设置',
          icon: 'fa-cog'
        });
        break;
      
      case 'home':
        breadcrumbList.push({
          name: '个人中心',
          icon: 'fa-user'
        });
        break;
      
      case 'data_manage':
        breadcrumbList.push({
          name: '数据维护',
          icon: 'fa-tasks'
        });
        break;

      case 'params_manage':
        breadcrumbList.push({
          name: '参数库管理',
          icon: 'fa-list'
        });
        break;

      case 'account':
        breadcrumbList.push({
          name: '账号管理',
          icon: 'fa-users'
        })
      
    }
    $('.nav-primary > .nav > li > a').each(function (i, v) {
      var a_sys = v.href.split('/').slice(3)[2];
      if (a_sys == sys) {
        $(v).addClass('active').parent().addClass('active');
      }
    });
    var html = '';
    $.each(breadcrumbList, function (i, v) {
      if (i === (breadcrumbList.length - 1)) {
        html += '<li class="active"><i class="fa ' + v.icon + '"></i> ' + v.name + '</li>'
      } else {
        html += '<li>' +
          '<a href="/public/index/' + sys + '">' +
          '<i class="fa ' + v.icon + '"></i> ' + v.name +
          '</a>' +
          '</li>'
      }
    });
    $('.breadcrumb').html(html);
  })
}(window.jQuery);

