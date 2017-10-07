!function ($) {

  $(function () {
    /* 选择当前元素的value内容*/
    $.fn.selectAllContent = function () {
      this.each(function (index, elem) {
        var strLength = this.value.length;
        if (elem.setSelectionRange) {
          elem.setSelectionRange(0, strLength);
        } else if (elem.createTextRange) {
          var range = elem.createTextRange();
          range.collapse(true);
          range.moveEnd('character', 0);
          range.moveStart('character', strLength);
          range.select();
        }
      });
      return this;
    };

    // 表单数据转JSON
    $.fn.serializeJson = function () {
      var serializeObj = {};
      var array = this.serializeArray();
      $(array).each(function () {
        if (serializeObj[this.name]) {
          if ($.isArray(serializeObj[this.name])) {
            serializeObj[this.name].push(this.value);
          } else {
            serializeObj[this.name] = [serializeObj[this.name], this.value];
          }
        } else {
          serializeObj[this.name] = this.value;
        }
      });
      return serializeObj;
    };

    /* 图片上传 */
    $(document).on('click', '#file_name', function () {
      var $this = $(this);
      var canUpload = true;
      try {
        new FormData()
      } catch (e) {
        canUpload = false;
      }
      if (!canUpload) {
        layer.alert('当前版本浏览器不支持导入功能,请升级您的浏览器或使用其他最新版本浏览器!', {title: '错误', icon: 2});
        return false;
      }
      $this.prop('readonly', true);
      /* 添加一个舞台 */
      $(document.body)
        .append('<div class="gc_upload_image"> ' +
          '<p class="text-sm text-center text-danger">仅支持上传小于2M的PDF格式文件</p>' +
          '<ul class="gc_file_list"></ul> ' +
          '<div class="gc_upload_btn"> ' +
          '<i class="gc_upload_icon">+</i> ' +
          '</div> ' +
          '<input type="file" class="gc_upload_input" data-input="#passport_img" data-name="#file_name"> ' +
          '</div>');
      var $box = $('.gc_upload_image');
      /* 首先根据input的值渲染图片*/
      randerPDF($('.gc_file_list'), $('#passport_img').val());
      $box
        .on('click', '.gc_upload_btn', function () {     //  上传按钮
          $(this).next().trigger('click');
        })
        .on('change', '.gc_upload_input', function () {  //  上传至服务器
          gc_upload_PDF(this);
        })
        .on('click', '.gc_delete', function () {         //  删除
          $(this).closest('li').remove();
          $("#file_name").val('');
          $($box.find('.gc_upload_input').data('input')).val('');
          $box.append(
            '<div class="gc_upload_btn"> ' +
            '<i class="gc_upload_icon">+</i> ' +
            '</div> ' +
            '<input type="file" class="gc_upload_input" data-input="#passport_img" data-name="#file_name"> ' +
            '</div>'
          )
        });
      /* 展示舞台 */
      layer.open({
        type: 1,
        shadeClose: true,
        content: $box,
        title: "上传护照",
        area: '196px',
        btn: ['确定'],
        yes: function (index) {
          layer.close(index)
        },
        end: function () { //  弹窗被销毁时触发的回调
          $('.gc_upload_image').remove();
        }
      });
    });

    /* 图片上传 */
    $(document).on('click', '#office_seal', function () {
      var $this = $(this);
      var canUpload = true;
      try {
        new FormData()
      } catch (e) {
        canUpload = false;
      }
      if (!canUpload) {
        layer.alert('当前版本浏览器不支持导入功能,请升级您的浏览器或使用其他最新版本浏览器!', {title: '错误', icon: 2});
        return false;
      }
      $this.prop('readonly', true);
      /* 添加一个舞台 */
      $(document.body)
        .append('<div class="gc_upload_image"> ' +
          '<p class="text-sm text-center text-danger">仅支持上传小于2M的PNG,JPG,JPEG格式文件</p>' +
          '<ul class="gc_file_list"></ul> ' +
          '<div class="gc_upload_btn"> ' +
          '<i class="gc_upload_icon">+</i> ' +
          '</div> ' +
          '<input type="file" class="gc_upload_input" data-input="#office_seal" data-name="#office_img"> ' +
          '</div>');
      var $box = $('.gc_upload_image');
      /* 首先根据input的值渲染图片*/
      randerImage($('.gc_file_list'), $('#office_img').val());
      $box
        .on('click', '.gc_upload_btn', function () {     //  上传按钮
          $(this).next().trigger('click');
        })
        .on('change', '.gc_upload_input', function () {  //  上传至服务器
          gc_upload_image(this);
        })
        .on('click', '.gc_delete', function () {         //  删除
          $(this).closest('li').remove();
          $("#office_seal").val('');
          $($box.find('.gc_upload_input').data('input')).val('');
          $box.append(
            '<div class="gc_upload_btn"> ' +
            '<i class="gc_upload_icon">+</i> ' +
            '</div> ' +
            '<input type="file" class="gc_upload_input" data-input="#office_img" data-name="#office_seal"> ' +
            '</div>'
          )
        });
      /* 展示舞台 */
      layer.open({
        type: 1,
        shadeClose: true,
        content: $box,
        title: "上传公章",
        area: '196px',
        btn: ['确定'],
        yes: function (index) {
          layer.close(index)
        },
        end: function () { //  弹窗被销毁时触发的回调
          $('.gc_upload_image').remove();
        }
      });
    });
    /* 图片上传 */
    $(document).on('click', '#customs_seal', function () {
      var $this = $(this);
      var canUpload = true;
      try {
        new FormData()
      } catch (e) {
        canUpload = false;
      }
      if (!canUpload) {
        layer.alert('当前版本浏览器不支持导入功能,请升级您的浏览器或使用其他最新版本浏览器!', {title: '错误', icon: 2});
        return false;
      }
      $this.prop('readonly', true);
      /* 添加一个舞台 */
      $(document.body)
        .append('<div class="gc_upload_image"> ' +
          '<p class="text-sm text-center text-danger">仅支持上传小于2M的PDF格式文件</p>' +
          '<ul class="gc_file_list"></ul> ' +
          '<div class="gc_upload_btn"> ' +
          '<i class="gc_upload_icon">+</i> ' +
          '</div> ' +
          '<input type="file" class="gc_upload_input" data-input="#customs_seal" data-name="#customs_img"> ' +
          '</div>');
      var $box = $('.gc_upload_image');
      /* 首先根据input的值渲染图片*/
      randerImage($('.gc_file_list'), $('#customs_img').val());
      $box
        .on('click', '.gc_upload_btn', function () {     //  上传按钮
          $(this).next().trigger('click');
        })
        .on('change', '.gc_upload_input', function () {  //  上传至服务器
          gc_upload_image(this);
        })
        .on('click', '.gc_delete', function () {         //  删除
          $(this).closest('li').remove();
          $("#customs_seal").val('');
          $($box.find('.gc_upload_input').data('input')).val('');
          $box.append(
            '<div class="gc_upload_btn"> ' +
            '<i class="gc_upload_icon">+</i> ' +
            '</div> ' +
            '<input type="file" class="gc_upload_input" data-input="#customs_img" data-name="#customs_seal"> ' +
            '</div>'
          )
        });
      /* 展示舞台 */
      layer.open({
        type: 1,
        shadeClose: true,
        content: $box,
        title: "上传护照",
        area: '196px',
        btn: ['确定'],
        yes: function (index) {
          layer.close(index)
        },
        end: function () { //  弹窗被销毁时触发的回调
          $('.gc_upload_image').remove();
        }
      });
    });
    // 护照列表图片查看
    $(document).on('click', '[aria-describedby$=_file_name]', function () {
      var $this = $(this),
        url = $this.next().text().trim();
      if (url === '') {
        return false;
      }
      downloadFile(url, $this.text().trim())
    });

    // 退出登陆
    $('.signOut').click(function () {
      layer.confirm('确定退出登录？', {
        btn: ['确定', '取消'] //按钮
      }, function () {
        location.href = '/public/index/login/loginOut'
      });
    });

    /* 数据维护自定义验证 */
    var validateKey = '',
      validateFlag = true,
      custom_func = {
        // 验证数据维护键值对
        validateExist: function (value) {
          var name = $('.nav-pills').find('li.active').data('name'),
            key = $('#key').val(),
            res = '',
            id = $('#id_g').val(),
            isNew = id === '_empty',
            origin = {
              key: $('.tab-content > .active .success td:nth-child(2)').text(),
              value: $('.tab-content > .active .success td:nth-child(3)').text()
            }
          if (!isNew && origin.key === key && origin.value === value) {
            return [true, '']
          }
          $.ajax({
            url: HTTP_HOST + '/public/index/data_manage/dataVerify',
            data: {
              name: name,
              key: key,
              value: value,
              id: id
            },
            async: false,
            success: function (data) {
              res = data
            }
          })
          if (res.key === 'SUCCESS' && res.value === 'SUCCESS') {
            layer.msg('提交成功')
            return [true, '']
          } else if (res.key !== 'SUCCESS') {
            return [false, res.key]
          } else if (res.value !== 'SUCCESS') {
            return [false, res.value]
          }
        },
        // 验证国家中文名称和代码不能重复
        validateCountryExist: function (value) {
          var res = '',
            co = $('#country_co').val(),
            id = $('#id_g').val(),
            isNew = id === '_empty',
            origin = {
              co: $('.tab-content > .active .success td:nth-child(2)').text(),
              na: $('.tab-content > .active .success td:nth-child(4)').text()
            }
          if (!isNew && origin.co === co && origin.na === value) {
            return [true, '']
          }
          $.ajax({
            url: HTTP_HOST + '/public/index/data_manage/countryVerify',
            data: {
              country_co: co,
              country_na: value,
              id: id
            },
            async: false,
            success: function (data) {
              res = data
            }
          })
          if (res.country_co === 'SUCCESS' && res.country_na === 'SUCCESS') {
            layer.msg('提交成功')
            return [true, '']
          } else if (res.country_co !== 'SUCCESS') {
            return [false, res.country_co]
          } else if (res.country_na !== 'SUCCESS') {
            return [false, res.country_na]
          }
        },
        // 验证港口中文名称和港口代码不能重复
        validatePortExist: function (value) {
          var res = '',
            port_c_cod = $('#port_c_cod').val(),
            id = $('#id_g').val(),
            isNew = id === '_empty',
            origin = {
              port_c_cod: $('.tab-content > .active .success td:nth-child(2)').text(),
              port_code: $('.tab-content > .active .success td:nth-child(5)').text()
            }
          if (!isNew && origin.port_c_cod === port_c_cod && origin.port_code === value) {
            return [true, '']
          }
          $.ajax({
            url: HTTP_HOST + '/public/index/data_manage/portLinVerify',
            data: {
              port_c_cod: port_c_cod,
              port_code: value,
              id: id
            },
            async: false,
            success: function (data) {
              res = data
            }
          })
          if (res.port_c_cod === 'SUCCESS' && res.port_code === 'SUCCESS') {
            layer.msg('提交成功')
            return [true, '']
          } else if (res.port_c_cod !== 'SUCCESS') {
            return [false, res.port_c_cod]
          } else if (res.port_code !== 'SUCCESS') {
            return [false, res.port_code]
          }
        },
        validateInteger: function (value, column) {
          if (!/\d/g.test(value))
            return [false, column + ": 请输入自然数"];
          else
            return [true, ""];
        },
        validateHsCode: function (value, column) {
          if (!/^\d{10}$/.test(value)) {
            return [false, column + ": 请输入10位数字"];
          } else {
            var res = '',
              id = $('#id_g').val(),
              isNew = id === '_empty',
              origin = $('.table-bordered .success td:nth-child(2)').text();
            // 修改,并且没有修改hscode则返回true
            if (!isNew && origin === value) {
              return [true, '']
            }
            // 否则异步查询
            $.ajax({
              url: HTTP_HOST + '/public/index/params_manage/hscodeVerify',
              data: {
                hsCode: value
              },
              dataType: 'json',
              async: false,
              success: function (data) {
                res = data
              }
            });
            if (res === 1) {
              return [true, '']
            } else {
              return [false, 'HSCODE: 系统海关库无此编码,请重新填写或与管理员联系']
            }
          }
        }
      }
    // 必填项设置样式
    var jqGstyle = '<style>';
    // jqGrid
    // 文档: http://www.cnblogs.com/younggun/archive/2012/08/27/2657922.html
    // DEMO: http://www.guriddo.net/demo/bootstrap/
    $.jgrid.defaults.styleUI = 'Bootstrap';
    $.jgrid.defaults.responsive = true;
    var jqgridList = [];
    $('[data-table=jqgrid]').each(function (i, v) {
      var $this = $(this),
        now = +new Date,
        thisId = 'jq_container' + now,      // 随机容器id
        tableId = 'jq_table' + now,         // 随机表格id
        paperId = 'jq_paper' + now,         // 随机控件id
        colModelArr = $this.data('colmodel').split(','),    // 提取字段
        colLengthArr = $this.data('collength') ? $this.data('collength').split(',') : [],    // 提取各列长度
        colEditRules = $this.data('editrules') ? $this.data('editrules').split(',') : [],    // 编辑规则
        colCustomFunc = $this.data('customfunc') ? $this.data('customfunc').split(',') : [],    // 编辑自定义规则验证
        colModel = [{
          name: 'id',
          index: 'id',
          key: true,
          width: 60,
          sortable: false,
          search: false,
          align: "center",
          hidden: true
        }];
      $this.prop('id', thisId);
      $this.children('table').prop('id', tableId);
      $this.children('div').prop('id', paperId);
      jqgridList.push(now);
      $.each(colModelArr, function (i, v) {
        var item = {
          name: v,
          index: v,
          sortable: false,
          align: "center",
          editable: true,
          searchoptions: {sopt: ['cn']},
          editrules: {}
        };
        if (colLengthArr[i] !== undefined && colLengthArr[i] !== '') {
          item['width'] = colLengthArr[i]
        }
        if (colEditRules[i] !== undefined && colEditRules[i] !== '') {
          var rules = colEditRules[i].split('|');
          $.each(rules, function (index, value) {
            switch (value) {
              case 'required':
                item['editrules']['required'] = true;
                jqGstyle += '[for=' + item.name + ']:after,';
                break
              case 'custom':
                item['editrules']['custom'] = true;
                item['editrules']['custom_func'] = custom_func[colCustomFunc[i]];
                break
              case 'number':
                item['editrules']['number'] = true;
            }
          })

        }
        /* 参数库管理额外设置商检否,涉税否编辑类型为select */
        if (v === 'involve_customs' || v === 'involve_tax') {
          item['edittype'] = "select";
          item['editoptions'] = {
            value: '否:否;是:是'
          }
        }
        // 参数库管理单位两字段有后台获取,类型改为select
        if (v === 'unit' || v === 'unit2') {
          item['edittype'] = "select";
          item['editoptions'] = {
            value: ':单位无需选择, 提交后将自动补全'
          }
        }
        if (v === 'office_img' || v === 'customs_img') {
          item['hidden'] = true;
        }
        colModel.push(item)
      });
      $("#jq_table" + now).jqGrid({         // 初始化jqGrid
        url: $this.data('url'),             // 数据 url
        editurl: $this.data('url'),         // 增,删,改url
        datatype: "json",                   // 返回数据格式
        rowNum: 12,                         // 每页显示条数
        rowList: [12, 20, 30],              // 可选显示条数
        pager: "#jq_paper" + now,           // 控件id
        jsonReader: {                       // 返回json数据处理
          root: 'data',                     // 包含实际数据的数组
          rows: 'per_page',                 // 每页显示多少条
          page: 'current_page',             // 当前页
          total: 'pages',                   // 总页数
          records: 'total',                 // 总数
          id: 'id'                          // 行id
        },
        // width: isAuto ? 'none' : 1000,
        height: 372,                        // 表格高度，可以是数字，像素值或者百分比
        autowidth: true,                    // 自动适应宽度(仅首次加载)
        shrinkToFit: true,                  // 使用百分比填充列
        viewrecords: true,                  // 查看总条数
        colNames: $this.data('colname').split(','), // 表头名
        colModel: colModel                  // 配置  单元格内容
      }).navGrid('#jq_paper' + now,
        {
          edit: true,                       // 是否可编辑
          add: true,                        // 是否可添加
          del: true,                        // 是否可删除
          search: true                      // 是否可查询
        },
        /* 动态改变弹窗高度 */
        { // 编辑配置
          height: 43 * colModelArr.length + 110,
          reloadAfterSubmit: true,
          closeAfterEdit: true
        },
        { // 添加配置
          height: 43 * colModelArr.length + 110,
          reloadAfterSubmit: true,
          closeAfterAdd: true
        },
        { // 删除配置
          reloadAfterSubmit: true
        },
        { // 搜索配置
          multipleSearch: true
        }
      );
    });
    // 必填项设置样式(包括护照设置全部字段)
    jqGstyle += '[for=nationality_str]:after,[for=passport_no]:after,[for=passport_img]:after{color:#f00;content:"*"}</style>';
    $(document.head).append(jqGstyle);


    // Add responsive to jqGrid
    $(window).bind('resize', function () {
      autoSetsetGridWidth()
    });
    $('[data-toggle="tab"]').click(function () {
      setTimeout(function () {
        autoSetsetGridWidth()
      }, 0)
    });

    function autoSetsetGridWidth() {
      $.each(jqgridList, function (i, v) {
        $('#jq_table' + v).setGridWidth($('#jq_container' + v).width());
      })
    }

    setTimeout(function () {
      autoSetsetGridWidth()
    }, 0)
    /* 护照设置 */
    if (location.pathname.split('/').slice(3)[0] === 'clearance') {
      var html = '';
      $.get('/public/index/data_manage/get_country?rows=10000', function (res) {
        res = res.data;
        $.each(res, function (i, v) {
          html += i === 0 ? v.country_co + ':' + v.country_na : ';' + v.country_co + ':' + v.country_na;
        });
      }).done(function () {
        var $this = $('#passportSetting').children(),
          now = +new Date,
          thisId = 'jq_container' + now,      // 随机容器id
          tableId = 'jq_table' + now,         // 随机表格id
          paperId = 'jq_paper' + now,         // 随机控件id
          colModelArr = $this.data('colmodel').split(','),    // 提取字段
          colModel = [{
            name: 'id',
            index: 'id',
            key: true,
            width: 60,
            sortable: false,
            search: false,
            align: "center",
            hidden: true
          }];
        $this.prop('id', thisId);
        $this.children('table').prop('id', tableId);
        $this.children('div').prop('id', paperId);
        jqgridList.push(now);
        $.each(colModelArr, function (i, v) {
          var itemObj = {
            name: v,
            index: v,
            sortable: false,
            align: "center",
            editable: true,
            searchoptions: {sopt: ['cn']},
            editrules: {
              required: true
            }
          }
          if (v === 'nationality_str') {
            itemObj['edittype'] = "select";
            itemObj['editoptions'] = {
              value: html
            }
          }
          if (v === 'passport_img') {
            itemObj['hidden'] = true;
          }
          colModel.push(itemObj)
        });
        $("#jq_table" + now).jqGrid({         // 初始化jqGrid
          url: $this.data('url'),             // 数据 url
          editurl: $this.data('url'),         // 增,删,改url
          datatype: "json",                   // 返回数据格式
          rowNum: 12,                          // 每页显示条数
          rowList: [12, 20, 30],               // 可选显示条数
          pager: "#jq_paper" + now,           // 控件id
          jsonReader: {                       // 返回json数据处理
            root: 'data',                     // 包含实际数据的数组
            rows: 'per_page',                 // 每页显示多少条
            page: 'current_page',             // 当前页
            total: 'pages',                   // 总页数
            records: 'total',                 // 总数
            id: 'id'                          // 行id
          },
          height: 390,                        // 表格高度，可以是数字，像素值或者百分比
          autowidth: true,                    // 自动适应宽度(仅首次加载)
          shrinkToFit: true,                  // 使用百分比填充列
          viewrecords: true,                  // 查看总条数
          colNames: $this.data('colname').split(','), // 表头名
          colModel: colModel                  // 配置  单元格内容
        }).navGrid('#jq_paper' + now,
          {
            edit: true,                       // 是否可编辑
            add: true,                        // 是否可添加
            del: true,                        // 是否可删除
            search: true                      // 是否可查询
          },
          /* 动态改变弹窗高度 */
          {
            height: 43 * (colModelArr.length - 1) + 110,
            reloadAfterSubmit: true,
            closeAfterEdit: true
          }, // 编辑配置
          {height: 43 * (colModelArr.length - 1) + 110, reloadAfterSubmit: true, closeAfterAdd: true}, // 添加配置
          {reloadAfterSubmit: true},              // 删除配置
          {}                                      // 搜索配置
        );
      });
    }
    // select2
    $('[data-select=select2]').each(function (i, v) {
      $(v).select2();
    });

    // slim-scroll
    $('.no-touch .slim-scroll').each(function () {
      var $self = $(this), $data = $self.data(), $slimResize;
      $self.slimScroll($data);
      $(window).resize(function (e) {
        clearTimeout($slimResize);
        $slimResize = setTimeout(function () {
          $self.slimScroll($data);
        }, 500);
      });

      $(document).on('updateNav', function () {
        $self.slimScroll($data);
      });
    });

    // 表格全选
    $(document).on('change', 'table thead [type="checkbox"]', function (e) {
      e && e.preventDefault();
      var $table = $(e.target).closest('table'), $checked = $(e.target).is(':checked');
      $('tbody [type="checkbox"]', $table).prop('checked', $checked);
    })
      .on('click', 'table tbody [type="checkbox"]', function (e) {
        var $table = $(e.target).closest('table');
        if (!$(e.target).is(':checked')) {
          $('thead [type="checkbox"]', $table).prop('checked', false);
        }
      })

  });
}(window.jQuery);