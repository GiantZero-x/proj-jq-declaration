/**
 * Created by giantR on 2017/4/21.
 */

/* 新建报关单js */
!function ($) {
  $(function () {

    var step2_item_key = 0, // 商品行号
      step3_item_key = 0, //归类行号
      CLEARANCE_ID,  // 当前报关单id
      WIZARD = $('#myWizard'),  // wizard容器
      classify_type = '', // 归类类型1,不合并,2,合并
      SERIAL_NO,  // 报关单编号
      sortIndex = -1; // 排序标志

    // 草稿入口
    if (billHead.id) {
      $('#bill_head_id').val(CLEARANCE_ID = billHead.id);
      $('.total_cases').val(billHead.total_cases);
      $('.total_weight').val(billHead.to1tal_weight);
      $('.total_money').val(billHead.total_money);
      classify_type = billHead.classify_type;
      SERIAL_NO = billHead.serial_no; // 编号
      $('#aim_country').val(billHead.aim_country).trigger('change');
      $.each($('.form-control'), function (k, v) {
        var name = $(this).attr('name');
        if (billHead[name]) {
          $(this).val(billHead[name]);
        }
      });

      $('#unloading_port').val(billHead.unloading_port);
      $.getJSON(get_cargo_url + billHead.id, function (data) {
        add_step3_item(data.cargo_dis);
        add_step2_item(data.cargo_or);
        // 计算各毛重和净重
        calcEachWeight()
      });
    }

    /* 批量删除 */
    $('.del_batch').click(function () {
      var parent = $(this).closest('.step-pane'),
        step = parent.data('step'),
        deleteList = parent.find('input.select_item:checked').closest('tr');
      if (deleteList.length < 1) {
        layer.msg('未选中任何记录!');
        return false;
      }
      if (deleteList.length == parent.find('input.select_item').length) {
        layer.msg('至少保留一条项目!');
        return false;
      }
      var valueList = [];
      deleteList.each(function (i, n) {
        var val = $(n).val();
        val && valueList.push(val);
      });
      if (valueList.length != 0) {
        $.ajax({
          type: "post",
          url: "",  //删除路径
          data: {
            valueList: JSON.stringify(valueList),  // value数组
            step: step   //删除类型
          },
          success: function () {
            deleteList.remove();
            calcTotal(step)
          }
        });
      } else {
        deleteList.remove();
        calcTotal(step)
      }
    });

    /* 新增 */
    $('.add_item').click(function () {
      var type = $(this).closest('.step-pane').data('step');
      type == '2' ? add_step2_item() : add_step3_item();
    });

    /* 步骤点击事件 */
    WIZARD
      .on('actionclicked.fu.wizard', function (e, data) {
        /* 跳转前执行 */
        var validated = true,
          $target = $('div[data-step="' + data.step + '"]');
        $('[data-required="true"]', $target).each(function () {
          validated = $(this).parsley('validate')
          if (!validated) {
            $(this).focus()
          }
          return validated;
        });
        if (data.direction == 'next' && !validated) {
          /* 验证不通过 */
          layer.msg('未完成,请查看提示!');
          return e.preventDefault();
        } else {
          /* 当前为第二步的时候,处理数据 */
          if (data.step == 2 && data.direction === "next") {
            if (!classify_type) {
              var flag = false;
              layer.confirm('请选择归类是否合并？',
                {
                  btn: ['不合并', '合并'], //按钮
                  end: function () {
                    if (!flag) {
                      classify(classify_type = 1);
                    }
                  }
                }, function (index) {
                  layer.close(index);
                  classify(classify_type = 1);
                  flag = true;
                }, function () {
                  classify(classify_type = 2);
                  flag = true;
                });
            }
          }
          /* 验证通过,保存草稿 */
          //console.log($form.serialize());
        }
      })
      .on('stepclicked.fu.wizard', function (e, data) {
        /* 跨步骤跳转后执行 */
        console.log('step ' + data.step + ' clicked');
        if (data.step === 1) {
          // return e.preventDefault();
        }
      })
      .on('changed.fu.wizard', function (e, data) {
        /* 跳转后执行 */
        var btn = $('.upload_excel');
        data.step !== 1 ? btn.hide() : btn.show();
      })
      .on('finished.fu.wizard', function (e, data) {
        /* 最后一步 changed后执行*/
        layer.confirm('确定提交么?', {
          btn: ['确定', '取消'] //按钮
        }, function () {
          var $serial_no = $('#serial_no');
          // 相等表示已保存过草稿,并且没有修改编号
          if ($serial_no.val() === SERIAL_NO) {
            layer.msg('提交成功');
            save();
            location.href = '/public'
          } else {
            $.post('/public/index/new_clearance/serialNo', {
              serial_no: $serial_no.val()
            }).done(function (res) {
              // 检查编号是否重复,重复则返回第一步并获取焦点
              if (res === 1) {
                layer.msg('提交成功');
                save();
                location.href = '/public'
              }
              else {
                layer.msg('报关单编号重复,请重新填写!');
                WIZARD.wizard('selectedItem', {step: 1});
                $serial_no.focus().selectAllContent()
              }
            });
          }
        });
      });

    // 保存按钮点击事件
    $('#save').click(function () {
      var $serial_no = $('#serial_no'),
        headBillStatus = $('#head_bill_status');
      // 相等表示已保存过草稿,并且没有修改编号
      if ($serial_no.val() === SERIAL_NO) {
        doSave()
      } else {
        $.post('/public/index/new_clearance/serialNo', {
          serial_no: $serial_no.val()
        }).done(function (res) {
          // 检查编号是否重复,重复则返回第一步并获取焦点
          if (res === 1) {
            SERIAL_NO = $serial_no.val();
            doSave()
          }
          else {
            layer.msg('报关单编号重复,请重新填写!');
            WIZARD.wizard('selectedItem', {step: 1});
            $serial_no.focus().selectAllContent()
          }
        });
      }

      function doSave() {
        headBillStatus.val(0);
        save();
        headBillStatus.val(1);
        layer.msg('保存成功');
      }
    });

    //保存/提交
    function save() {
      var url = "/public/index/new_clearance/";
      var save_transport = 'save_transport';
      var cargo = 'save_cargo';
      var custom = 'save_custom';
      var id = '';
      $.ajaxSetup({
        async: false
      });
      $('div[data-step]').each(function (i, v) {
        if (i == 0) {
          $.post(url + save_transport, $(v).find('form').serialize(), function (data) {
            console.log(data);
            id = data;
            $('#bill_head_id').val(id);
          });
        }
        if (i == 1) {
          $.post(url + cargo + '?bill_id=' + id, $(v).find('form').serialize(), function (data) {
            console.log(data)

          });
        }
        if (i == 2) {
          $.post(url + cargo + '?type=2&bill_id=' + id, $(v).find('form').serialize(), function (data) {
            console.log(data)
          });
        }
        if (i == 3) {
          $.post(url + custom + '?bill_id=' + id, $(v).find('form').serialize(), function (data) {
            //console.log(data)
          });
        }

      })
    }

    /* 归类合并按钮 */
    $('.classify').click(function () {
      classify_type = $(this).data('type');
      classify(classify_type);
      $('#myWizard').wizard('next');
    });

    /* 绑定1,4的enter提交 */
    $('div[data-step=1] .form-control,div[data-step=4] .form-control').on('keydown', function (e) {
      if (e.keyCode == 13) {
        var index = Number($(this).attr('tabindex')) + 1;
        if (index === 19 || index === 29) {
          return WIZARD.wizard('next');
        }
        moveNext(index)

        function moveNext(index) {
          var next = $('[tabindex=' + index + ']');
          if (next.is(':disabled')) {
            index++;
            moveNext(index)
          } else {
            next.focus()
          }
        }
      }
    });

    /* 绑定2,3输入品名查询 */
    WIZARD.on('keyup', '.searchGoodsInput', function (e) {
      e.stopPropagation();
      if (e.which === 13) {
        var $this = $(this);
        getGoodsByEnter($this.val().trim(), $this)
      }
    });

    /* 输入件数,毛重计算净重 */
    $('div[data-step="2"],div[data-step="3"]').each(function (i, box) {
      $(box).on('input', '.item_pgs,.item_gross,.item_net', function () {
        /* 判断总件数,总毛重是否有值 */
        var $customer_total_pgs = $(box).find('.customer_total_pgs');
        $customer_total_pgs.val(parseInt($customer_total_pgs.val().replace(/[^0-9]/g, '')));
        if (isNaN($customer_total_pgs.val())) {
          layer.msg('请先输入总件数!');
          $customer_total_pgs.val('').focus();
          return false;
        }
        var $customer_total_gross = $(box).find('.customer_total_gross');
        $customer_total_gross.val(parseFloat($customer_total_gross.val().replace(/[^0-9.]/g, '')));
        if (isNaN($customer_total_gross.val())) {
          layer.msg('请先输入总毛重!');
          $customer_total_gross.val('').focus();
          return false;
        }
        var $this = $(this);
        var $item_net = $this.closest('tr').find('.item_net'),
          $is_nicety = $this.closest('tr').find('[name*=nicety]').val();
        if ($this.hasClass('item_pgs') && /[^0-9]/g.test($this.val())) {
          layer.msg('请输入正整数');
          $this.val($this.val().replace(/[^0-9]/g, ''));
        } else if (!$this.hasClass('item_pgs') && /[^0-9.]/g.test($this.val())) {
          layer.msg('请正确输入数字');
          $this.val($this.val().replace(/[^0-9.]/g, ''));
        }
        /* 输入件数计算毛重和净重 */
        if ($this.hasClass('item_pgs') && !Number($is_nicety)) {
          var $item_gross = $this.closest('tr').find('.item_gross');
          // $item_gross.val(forDight($this.val() / $customer_total_pgs.val() * $customer_total_gross.val(), 1));
          $item_net.val($item_gross.val() - $this.val());
        } else if ($this.hasClass('item_gross') && !Number($is_nicety)) {
          // 当毛重修改好，净重跟着联动，净重＝毛重－件数
          var $item_pgs = $this.closest('tr').find('.item_pgs');
          $item_net.val(Acc.sub($this.val(), $item_pgs.val()))
        }
        calcItemRatio($this);
        calcTotal($(box).data('step'));
      })
        .on('click', 'input[type=text]:not([readonly])', function () {
          $(this).selectAllContent()
        })
        /* 新建报关单时，在归类商品步骤在单价栏回车可以直接到下一条记录的单价栏
         *   上下键能使光标在各个单元格中移动
         * */
        .on('keyup', 'input[type=text]:not([readonly]):not(.searchGoodsInput)', function (e) {
          if (e.which === 13 || e.which === 40 || e.which === 38) {
            // 13,40下 38上
            var matArr = this.name.match(/data\[([\d]+)\]\[(.*)\]/); //1: index, 2: name
            var nextInput = $(box).find('input[name="data[' + (Number(matArr[1]) + (e.which === 38 ? -1 : 1)) + '][' + matArr[2] + ']"]');
            nextInput && nextInput.focus().selectAllContent()
          }
        })
    })

    /** 涉通关单,涉税为是时添加文字红色样式
     * hscode,品名粘贴查询
     * input输入时将value属性值同时更新
     * */
    $('div[data-step="2"]').on('change', 'select', function () {
      var $this = $(this),
        $hs_code = $this.closest('tr').find('[name*="hs_code"]'),
        $involve_customs = $this.closest('tr').find('[name*="involve_customs"]'),
        $involve_tax = $this.closest('tr').find('[name*="involve_tax"]');
      $involve_customs.val() === '是' || $involve_tax.val() === '是' ? $hs_code.addClass('text-danger') : $hs_code.removeClass('text-danger');
      $this.find('[value="' + $this.val() + '"]').attr('selected', true).siblings().removeAttr('selected');
      sortIndex = -1;
    })
      .on('paste', '.searchGoodsInput', function () {
        var $this = $(this);
        setTimeout(function () {
          getGoodsByEnter($this.val().trim(), $this)
        }, 0)
      })
      .on('input', 'input[type=text]', function () {
        $(this).attr('value', $(this).val()); // 排序时必须显式将value属性值改掉
        sortIndex = -1;
      });

    /* 输入数量,单价计算总价 */
    $('div[data-step="3"]').on('input', '.item_price,.item_number', function () {
      var $this = $(this),
        $thisTr = $this.closest('tr'),
        $item_totalPrice = $thisTr.find('.item_totalPrice');
      // 单价
      if ($this.hasClass('item_price')) {
        if (/[^.0-9]/g.test($this.val())) {
          layer.msg('请输入价格并至多保留两位小数')
        }
        $this.val($this.val().replace(/[^.0-9]/g, ''));
        var $item_number = $(this).closest('tr').find('.item_number');
        if ($item_number.val()) {
          $item_totalPrice.val(forDight(Acc.mul($this.val(), $item_number.val()), 4))
          // 合并后的单价更改之后，计算第二步新单价，如果单位是ＫＧ，则为净重，否则数量＝净重/数重比
          // 涉通关单为是的没有合并,或者归类不合并的直接改单价
          // 匹配合并前的条目   hscode和品名匹配即可
          /*var currCode = $thisTr.find('[name*=hs_code]').val().trim(),
           currName = $thisTr.find('[name*=name]').val().trim(),
           $step2Trs = Array.from($('[data-step=2] tbody tr')),
           isMerge = $('[data-step=3] tbody tr').length !== $step2Trs.length,
           isInvolveCustoms = $thisTr.find('[name*=involve_customs]').val() === '是', // 当前是否为涉通关单
           // 获取第二步对应tr
           step2Tr = $step2Trs.filter(function (tr) {
           return $(tr).find('[name*=hs_code]').val().trim() === currCode && $(tr).find('[name*=name]').val().trim() === currName
           }
           )[0];
           step2Tr = $(step2Tr);

           // 如果是合并并且涉通关单为'否'则计算,否则直接改对应单价
           if (isMerge && !isInvolveCustoms) {
           // 归类合并
           // 先取code前两位
           var per2 = currCode.slice(0, 2);
           // 获取等于code前两位并且涉通关单为否的条目
           var $matchTrs = $step2Trs.filter(function (tr) {
           return $(tr).find('[name*=hs_code]').val().trim().slice(0, 2) === per2 && $(tr).find('[name*=involve_customs]').val() === '否'
           }
           );
           var originTotalPrice = 0;
           var len = $matchTrs.length;
           for (var i = 0; i < len; i++) {
           var $tr = $($matchTrs[i]),
           netVal = $tr.find('[name$="net_weight]"]').val(),
           num = $tr.find('[name$="unit]"]').val() === '千克' ? netVal : Math.floor(netVal / $tr.find('[name$="ratio]"]').val());
           originTotalPrice = Acc.add(originTotalPrice, forDight(Acc.mul($this.val(), num), 4), 4)
           }
           // 差值
           var diffTotalPrice = forDight(Acc.sub($item_totalPrice.val(), originTotalPrice), 4),
           originNum = step2Tr.find('[name$="unit]"]').val() === '千克' ? step2Tr.find('[name$="net_weight]"]').val() : Math.floor(step2Tr.find('[name$="net_weight]"]').val() / step2Tr.find('[name$="ratio]"]').val());
           for (var j = 0; j < len; j++) {
           var $thisTr = $($matchTrs[j]),
           $np = $thisTr.find('[name$="np]"]');
           $np.attr('value', $this.val());
           }
           step2Tr.find('[name*=np]').attr('value', Acc.add($this.val(), Acc.div(diffTotalPrice, originNum), 4));
           } else {
           // 归类不合并
           step2Tr.find('[name*=np]').attr('value', $this.val());
           }*/
        }
      }
      else if ($this.hasClass('item_number')) {
        if (/[^0-9]/g.test($this.val())) {
          layer.msg('请输入正整数')
        }
        $this.val($this.val().replace(/[^0-9]/g, ''));

        calcItemRatio($this);

        // 计算总价
        var item_price = $(this).closest('tr').find('.item_price');
        if (item_price.val()) {
          $item_totalPrice.val(forDight(Acc.mul($this.val(), item_price.val()), 4))
        }
      }
      calcTotal(3);
    })
      .on('blur', '.item_price', function () {
        var $this = $(this);
        if (!/^\d+(\.\d{1,2})?$/.test($this.val())) {
          layer.msg('单价至多可保留两位小数');
          $this.focus();
        }
      });

    /* 输入总件数,总毛重进行对比 */
    $('.customer_total_pgs,.customer_total_gross').on('input', function () {
      var $this = $(this);
      if ($this.hasClass('customer_total_pgs') && /[^0-9]/g.test($this.val())) {
        layer.msg('请输入正整数');
        $this.val($this.val().replace(/[^0-9]/g, ''));
      } else if ($this.hasClass('customer_total_gross') && /[^0-9.]/g.test($this.val())) {
        layer.msg('请正确输入数字');
        $this.val($this.val().replace(/[^0-9.]/g, ''));
      }
      compareTotal();
      calcEachWeight()
    });

    /* 上传excel*/
    $('.upload_excel').click(function () {
      var flag = true
      try {
        new FormData()
      } catch (e) {
        layer.alert('当前版本浏览器不支持导入功能,请升级您的浏览器或使用其他最新版本浏览器!', {title: '错误', icon: 2});
        flag = false;
      }
      flag && $(this).next().trigger('click')
    });
    $('.upload_excel_input').change(function () {
      upload_excel(this)
    });

    /* 代理选择hs_code */
    $('.chooseGoodsBox')
      .on('click', '.goodsList tr', function () {
        var $this = $(this);
        $this.addClass('active').siblings().removeClass('active');
      })
      .on('dblclick', '.goodsList tr', function () {
        var $this = $(this);
        $this.addClass('active').siblings().removeClass('active');
        settleGoods(null, null, true)
      })

    /* 必填项加上必填星号 */
    $('[data-required]').each(function (i, v) {
      $(this).closest('.form-group').find('label').addClass('necessary');
    });

    /* 境内运输方式是铁路运输则白卡号不必填 */
    $('[name="inland_mode"]').change(function () {
      var flag = $(this).val() === '3'; // 铁路运输
      $('[name="white_card_no"],[name="car_no"]').each(function (i, v) {
        var $this = $(v),
          $label = $(this).closest('.form-group').find('label');
        $this.attr('data-required', !flag);
        !flag ? $label.addClass('necessary') : $label.removeClass('necessary');
        $this.parsley().reset();
        $this.parsley().destroy()
      });
    }).change();

    /* hsCode排序*/
    $('.sort_hs_code').click(function () {
      var $box = $('div[data-step="2"]'),
        sortFlag = $(this).hasClass('active'), // 为true => 降序; false => 升序
        headThs = $box.find('thead tr th'),
        bodyTrs = $box.find('tbody tr'),
        trHtml_top = [],
        trHtml = [],
        currIndex = headThs.index($(this).closest('th'));

      bodyTrs.each(function (i, v) {
        var currTd = $(v).find('td').eq(currIndex),
          isIC = $(v).find('[name*="involve_customs"]').val() === '是',
          temp = {
            key: currTd.find('input').val().trim(),
            html: $(v).html()
          };
        // 如涉通关单为是,则插入trHtml_top中
        isIC ? trHtml_top.push(temp) : trHtml.push(temp);
        $(v).html('')
      });
      // 如果排过了就直接翻转,否则排序
      if (currIndex == sortIndex) {
        trHtml.reverse();
      } else {
        customerSort(trHtml);
      }
      var newArr = trHtml_top.concat(trHtml),
        len_new = newArr.length;
      for (var i = 0; i < len_new; i++) {
        //将排序完的 值 插入到 表格中
        bodyTrs[i].innerHTML = newArr[i].html;
      }
      sortIndex = currIndex;

      /**
       * 自定排序
       * @param arr  { Array }   排序数组
       */
      function customerSort(arr) {
        var len = arr.length;
        if (!len) {
          return false;
        }
        for (var i = 0; i < len; i++) {
          for (var j = i + 1; j < len; j++) {
            var v1 = arr[i].key === '' ? 0 : arr[i].key,
              v2 = arr[j].key === '' ? 0 : arr[j].key;
            // 降序 true > 升序 false <
            if ((sortFlag && parseInt(v1) > parseInt(v2)) ||
              (!sortFlag && parseInt(v1) < parseInt(v2))) {
              var temp = arr[j];
              arr[j] = arr[i];
              arr[i] = temp;
            }
          }
        }
      }
    });

    /* 没有id认为是新建,各添加一行 */
    if (CLEARANCE_ID === undefined) {
      add_step2_item();
    }

    /** 上传excel
     * @param {Element} el 上传input
     * */
    function upload_excel(el) {
      var tip = "文件格式不符,请上传Excel文件！", // 设定提示信息
        uploadUrl = HTTP_HOST + '/public/index/Upload'; // 上传地址

      if (el.value == '') {
        return false;
      }
      if (!/\.xls$|\.xlsx$/i.test(el.value)) {
        layer.msg(tip);
        el.value = null;
      } else {
        var form = new FormData();
        form.append("fileList", el.files[0]);// 文件对象
        // XMLHttpRequest 对象
        var xhr = new XMLHttpRequest();
        xhr.open("post", uploadUrl, true);
        xhr.onload = function () {
          var path = xhr.responseText;
          path = eval(path);
          $.get("/public/index/new_clearance/upload.html?path=" + path, function (data) {
            if (data.bill_head && data.cargo) {
              layer.msg('导入成功!');
              var bill_head = data.bill_head,
                form1 = $('[data-form="1"]')[0];
                // form4 = $('[data-form="4"]')[0];
              /* 添加1,4步表单 */
              for (var i in bill_head) {
                form1.hasOwnProperty(i) && (form1[i].value = bill_head[i]);
                // form4.hasOwnProperty(i) && (form4[i].value = bill_head[i])
              }
              /* 编号 */
              $('.number').html($('#serial_no').val());
              /* 总件数,总毛重 */
              $('.total_cases').val(data.bill_head.total);
              $('.total_weight').val(data.bill_head.weight);
              /* 添加商品数据 */
              add_step2_item(data.cargo)
              // 计算各毛重和净重
              calcEachWeight()
            } else {
              layer.msg(data)
            }

          });;
        };
        xhr.send(form);
      }

    }

    /** 添加商品数据
     * @param {Array} dataArr 数据数组
     * */
    function add_step2_item(dataArr) {
      var html = '',
        box = $('div[data-step="2"]').find('table tbody');
      if (dataArr) {
        box.html('');
      } else {
        dataArr = [{
          case_no: '',
          id: '',
          price: '',
          market_no: '',
          name: '',
          standard: '',
          np: '',
          hsCode: {
            hs_code: '',
            name_search: '',
            unit: '',
            unit2: '',
            involve_customs: '否',
            involve_tax: '否',
            ratio: '',
            standard: ''
          },
          code: {
            unit2: ''
          },
          is_nicety: 0, // 是否不需要计算
          net_weight: '', // 净重
          number: '',  // 数量
          rough_weight: '' // 毛重
        }]
      }
      $.each(dataArr, function (i, v) {
        html += '<tr data-key="' + step2_item_key + '"> ' +
          '<td  width="2%"><input type="checkbox" name="data[' + step2_item_key + '][id]" value="' + v.id + '" class="select_item"></td> ' +
          '<td width="9%"><input class="searchGoodsInput  ' + (function () {
            if (v.hsCode.involve_tax === '是' || v.hsCode.involve_customs === '是') {
              return 'text-danger'
            } else {
              return ''
            }
          })() + '" data-trigger="change" data-type="number" data-required="true" data-rangelength="[10, 10]" type="text" name="data[' + step2_item_key + '][hs_code]" value="' + v.hsCode.hs_code + '"></td> ' +
          '<td  width="13%"><input class="searchGoodsInput" data-trigger="change" class="product_name" data-required="true" type="text" name="data[' + step2_item_key + '][name]" value="' + v.name + '"></td> ' +
          '<td width="8%"><input type="text" name="data[' + step2_item_key + '][name_search]" value="' + v.hsCode.name_search + '" readonly></td> ' +
          '<td width="7%"><input data-trigger="change" data-required="true" type="text" name="data[' + step2_item_key + '][case_no]" class="item_pgs" value="' + v.case_no + '"></td> ' +
          '<td  width="7%"><input data-trigger="change" data-required="true" type="text" name="data[' + step2_item_key + '][rough_weight]" class="item_gross" value="' + (v.rough_weight || '') + '"></td> ' +
          '<td width="7%"><input data-trigger="change" data-required="true" type="text" name="data[' + step2_item_key + '][net_weight]" class="item_net" value="' + (v.net_weight || '') + '"></td> ' +
          '<td width="7%"><input type="text" name="data[' + step2_item_key + '][unit]" value="' + v.hsCode.unit + '"></td> ' +
          '<input type="hidden" name="data[' + step2_item_key + '][unit_apply]" value="' + v.hsCode.unit + '"> ' +
          '<td width="7%"><input type="text" name="data[' + step2_item_key + '][unit2]" value="' + (v.code.unit2 || '') + '"></td> ' +
          '<td width="8%"><select name="data[' + step2_item_key + '][involve_customs]"><option value="否">否</option><option value="是" ' + (function () {
            if (v.hsCode.involve_customs === '是') return 'selected'
          })() + '>是</option></select></td> ' +
          '<td  width="6%"><select name="data[' + step2_item_key + '][involve_tax]"><option value="否">否</option><option value="是" ' + (function () {
            if (v.hsCode.involve_tax === '是') return 'selected'
          })() + '>是</option></select></td> ' +
          '<td width="8%"><input type="text" name="data[' + step2_item_key + '][market_no]" value="' + (v.market_no !== undefined ? v.market_no : v.hsCode.market_no) + '"></td> ' +
          '<td><input type="text" name="data[' + step2_item_key + '][ratio]" value="' + v.hsCode.ratio + '"></td> ' +
          '<input type="hidden" name="data[' + step2_item_key + '][standard]" value="' + (v.hsCode.standard || v.standard) + '">' +
          '<input type="hidden" name="data[' + step2_item_key + '][price]" value="' + (v.hsCode.price || v.price) + '">' +
          '<input type="hidden" name="data[' + step2_item_key + '][np]" value="' + v.np + '">' +
          '<input type="hidden" name="data[' + step2_item_key + '][is_nicety]" value="' + v.is_nicety + '">' +
          '<input type="hidden" name="data[' + step2_item_key + '][number]" value="' + (v.number || '') + '">' +
          '</tr>';
        step2_item_key++;
      });
      box.append(html);
      calcTotal(2);
    }

    /** 添加归类数据
     * @param {Array} dataArr 数据数组
     * */
    function add_step3_item(dataArr) {
      var html = '',
        box = $('div[data-step="3"]').find('table tbody');
      if (dataArr) {
        box.html('');
      } else {
        dataArr = [{
          case_no: '',
          hs_code: '',
          name: '',
          net_weight: '',
          price: '',
          ratio: '1.0000',
          rough_weight: '',
          unit: '',
          unit_apply: '',
          standard: '',
          number: '',
          totalPrice: ''
        }];
        // layer.msg('未上传商品');
      }
      $.each(dataArr, function (i, v) {
        var price = v.price ? v.price : '';
        var totalPrice = price * v.number ? price * v.number : '';
        html += '<tr data-key="' + step3_item_key + '"> ' +
          '<td width="2%"><input type="checkbox" name="data[' + step3_item_key + '][id]" value="' + v.id + '" class="select_item"></td> ' +
          '<td width="10%"><input class="searchGoodsInput" data-trigger="change" data-type="number" data-rangelength="[10, 10]" data-required="true" type="text" name="data[' + step3_item_key + '][hs_code]" value="' + v.hs_code + '"></td> ' +
          '<td width="13%"><input class="searchGoodsInput" data-trigger="change" data-required="true" type="text" name="data[' + step3_item_key + '][name]" value="' + v.name + '"></td> ' +
          '<td width="15%"><input data-trigger="change" data-required="true" type="text" name="data[' + step3_item_key + '][standard]" value="' + v.standard + '"></td> ' +
          '<td width="8%"><input data-trigger="change" data-required="true" type="text" name="data[' + step3_item_key + '][case_no]" class="item_pgs" value="' + v.case_no + '"></td> ' +
          '<td width="8%"><input data-trigger="change" data-required="true" type="text" name="data[' + step3_item_key + '][rough_weight]" class="item_gross" value="' + v.rough_weight + '"></td> ' +
          '<td width="8%"><input data-trigger="change" data-required="true" type="text" name="data[' + step3_item_key + '][net_weight]" class="item_net" value="' + v.net_weight + '"></td> ' +
          '<td width="8%"><input type="text" name="data[' + step3_item_key + '][number]" value="' + v.number + '" class="item_number"></td> ' +
          '<td width="8%"><input type="text" name="data[' + step3_item_key + '][unit_apply]" value="' + v.unit_apply + '"></td> ' +
          '<input type="hidden" name="data[' + step3_item_key + '][unit]" value="' + v.unit + '"> ' +
          '<td width="8%"><input data-trigger="change" data-required="true" type="text" name="data[' + step3_item_key + '][price]" value="' + price + '" class="item_price"></td> ' +
          '<td width="8%"><input type="text" name="data[' + step3_item_key + '][totalPrice]" value="' + totalPrice + '" class="item_totalPrice" readonly></td> ' +
          '<td><input type="text" class="item_ratio" name="data[' + step3_item_key + '][ratio]" value="' + v.ratio + '"></td> ' +
          '<input type="hidden" name="data[' + step3_item_key + '][market_no]" value="' + v.market_no + '"> ' +
          '<input type="hidden" name="data[' + step3_item_key + '][involve_customs]" value="' + v.involve_customs + '"> ' +
          '<input type="hidden" name="data[' + step3_item_key + '][is_nicety]" value="' + v.is_nicety + '"> ' +
          '</tr>';
        step3_item_key++;
      });
      box.append(html);
      // 根据数重比和净重计算数量 数量 = 净重/数重比
      // 根据数量和单价计算总价
      box.find('tr').each(function (i, v) {
        var $this = $(this),
          $item_price = $this.find('.item_price'),
          $item_totalPrice = $this.find('.item_totalPrice'),
          $item_number = $this.find('.item_number');
        /*$item_net = $this.find('.item_net'),
         $item_ratio = $this.find('.item_ratio'),
         $unit_apply = $this.find('[name*=unit_apply]');*/
        /*if ($unit_apply.val() === '千克') {
         $item_number.val($item_net.val())
         } else {
         $item_number.val(forDight(Acc.div($item_net.val(), $item_ratio.val()), 0) ? forDight(Acc.div($item_net.val(), $item_ratio.val()), 0) : '0');
         }*/
        if ($item_number.val() && $item_price.val()) {
          $item_totalPrice.val(forDight(Acc.mul($item_number.val(), $item_price.val()), 4))
        }
      });
      calcTotal(3);
    }

    /** 根据品名获取数据
     * @param {String} value 品名
     * @param {Element} self 当前输入的input jq对象
     * */
    var layerIndex = '',
      currentInput = null;

    function getGoodsByEnter(value, self) {
      currentInput = self;
      if (value == '') {
        return false
      }
      var type = /name/.test(self.attr('name')) ? 'name' : 'hs_code'
      var inputList = self.closest('tr').find('input,select')
      $.getJSON("/public/index/new_clearance/getHscode?type=" + type + '&value=' + value, function (res) {
        if (res.length === 0) {
          $(inputList[3]).val('空').attr('value', '空');
          layer.msg('未匹配到任何数据');
        } else if (res.length === 1) {
          settleGoods(inputList, res[0]);
          layer.msg('完全匹配');
        } else {
          var $goodsList = $('.goodsList'),
            html = '';
          $.each(res, function (i, v) {
            html += '<tr ' +
              'data-hs_code="' + v.hs_code + '" ' +
              'data-unit="' + v.unit + '" ' +
              'data-name="' + v.name + '" ' +
              'data-unit2="' + v.unit2 + '" ' +
              'data-involve_customs="' + v.involve_customs + '" ' +
              'data-involve_tax="' + v.involve_tax + '" ' +
              'data-market_no="' + v.market_no + '" ' +
              'data-ratio="' + v.ratio + '" ' +
              'data-price="' + v.price + '" ' +
              'data-standard="' + v.standard + '">' +
              '<td>' + v.name + '</td>' +
              '<td>' + v.hs_code + '</td>' +
              '</tr>'
          })
          $goodsList.html(html);
          layerIndex = layer.open({
            type: 1,
            title: false,
            shade: false,
            content: $('.chooseGoodsBox'),
            btn: ['确定', '取消'],
            yes: function () {
              settleGoods(inputList, null, true);
            }
          });
        }
      });
    }

    /**
     * 根据选中行设定数据
     * @param inputList {Array}   匹配行 Input和Select
     * @param res       {Object}  数据
     * @param flag      {Boolean} 是否需要关闭弹窗
     */
    function settleGoods(inputList, res, flag) {
      inputList = inputList || currentInput.closest('tr').find('input,select');
      res = res || $('.goodsList').find('.active').data();
      if (res) {
        var currStep = currentInput.closest('div[data-step]').data('step');

        $(inputList[1]).val(res.hs_code).attr('value', res.hs_code);
        $(inputList[2]).val(inputList[2].value || res.name).attr('value', inputList[2].value || res.name);// 不覆盖原有值
        $(inputList[8]).val(res.unit).attr('value', res.unit);
        if (currStep == 2) {
          $(inputList[3]).val('匹配').attr('value', '匹配');
          $(inputList[7]).val(res.unit).attr('value', res.unit);
          $(inputList[9]).val(res.unit2).attr('value', res.unit2);
          $(inputList[10]).val(res.involve_customs).attr('value', res.involve_customs);
          $(inputList[11]).val(res.involve_tax).attr('value', res.involve_tax);
          $(inputList[12]).val(res.market_no).attr('value', res.market_no);
          $(inputList[13]).val(res.ratio).attr('value', res.ratio);
          $(inputList[14]).val(res.standard).attr('value', res.standard);
          $(inputList[15]).val(res.price).attr('value', res.price);
        } else if (currStep == 3) {
          $(inputList[3]).val(res.standard).attr('value', res.standard);
          $(inputList[9]).val(res.unit).attr('value', res.unit);
          $(inputList[10]).val(res.price).attr('value', res.price);
          $(inputList[12]).val(res.ratio).attr('value', res.ratio);
          $(inputList[13]).val(res.market_no).attr('value', res.market_no);
        }
      }

      if (flag) {
        currentInput.parsley().reset();
        layer.close(layerIndex);
      }
      $('div[data-step="2"] select').change()
    }

    /** 根据数量,净重计算数重比
     * @param $this { Element } 输入对象
     */
    function calcItemRatio($this) {
      // 计算数重比   数量 = 净重 / 数重比
      var $item_net = $this.closest('tr').find('.item_net');
      var $item_number = $this.closest('tr').find('.item_number');
      var $item_ratio = $this.closest('tr').find('.item_ratio');
      if ($item_number.val() && $item_net.val()) {
        $item_ratio.val(forDight(Acc.div($item_net.val(), $item_number.val()), 4))
      }
    }

    /** 计算汇总件数,毛重,净重,总额
     * @param {Number} step 类型,2或3
     * */
    function calcTotal(step) {
      var total = {
          pgs: 0,
          gross: 0,
          net: 0,
          totalPrice: 0
        },
        $Box = $('div[data-step="' + step + '"]');

      for (var key in total) {
        if (total.hasOwnProperty(key)) {
          mapValue(key);
        }
      }
      compareTotal();

      function mapValue(key) {
        $Box.find('.item_' + key).map(function (i, v) {
          return v.value
        }).each(function (i, v) {
          total[key] = Acc.add(total[key], v, key === 'totalPrice' ? 4 : key === 'pgs' ? 0 : 1);
        });
        $Box.find('.total_' + key).val(Number(total[key]) ? total[key] : 0);
      }
    }

    /** 比较总件数汇总件数,总毛重,汇总毛重
     * */
    function compareTotal() {
      for (var i = 2; i < 4; i++) {
        var $box = $('div[data-step="' + i + '"]'),
          $customer_total_pgs = $box.find('.customer_total_pgs'),
          $total_pgs = $box.find('.total_pgs'),
          $customer_total_gross = $box.find('.customer_total_gross'),
          $total_gross = $box.find('.total_gross');
        if (Number($customer_total_pgs.val()) != Number($total_pgs.val())) {
          $total_pgs.addClass('text-danger');
        } else {
          $total_pgs.removeClass('text-danger');
        }
        if (Number($customer_total_gross.val()) != Number($total_gross.val())) {
          $total_gross.addClass('text-danger');
        } else {
          $total_gross.removeClass('text-danger');
        }
      }
    }

    /** 归类合并或不合并
     * @param {Number} type 合并表示,1:不合并,2合并
     * */
    function classify(type) {
      $('#classify_type').val(type)
      var data = $('div[data-step="2"]').find('form').serialize();
      var url = "/public/index/new_clearance/cargo_sort";
      if (type === 2) {
        url = "/public/index/new_clearance/cargo_merge";
      }

      $.post(url, data, function (res) {
        console.log(url + 11111111);
        console.log(res);
        add_step3_item(res.data);
      });
    }

    /** 遍历计算二, 三步毛重净重数量总价
     * */
    function calcEachWeight() {
      $('div[data-step="2"],div[data-step="3"]').each(function (index, box) {
        var customer_total_pgs = $(box).find('.customer_total_pgs').val(),
          customer_total_gross = $(box).find('.customer_total_gross').val();
        $(box).find('.item_pgs').each(function () {
          var $this = $(this),
            $parent = $this.closest('tr'),

            $item_gross = $parent.find('.item_gross'),
            $item_net = $parent.find('.item_net'),
            $is_nicety = $parent.find('[name*=nicety]').val();
          if (Number($is_nicety)) {
            return true;
          }
          // 导入模板中如果有毛重、净重和数量字段内容的品名不计算
          // var $item_gross_value = forDight($this.val() / customer_total_pgs * customer_total_gross, 0) || '';
          // $item_gross.val($item_gross_value).attr('value', $item_gross_value);
          var $item_net_value = $item_gross.val() - $this.val();
          $item_net.val($item_net_value).attr('value', $item_net_value);
        })
      });
      calcTotal(2)
      calcTotal(3)
    }
  })
}(window.jQuery);
