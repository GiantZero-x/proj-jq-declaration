/**
 * Created by giantR on 2017/4/20.
 */
/* 上传图片函数封装 */
function gc_upload_image(el) {
  var target = $($(el).data('input')),
      targetName = $($(el).data('name')),
    show = $('.gc_file_list'),
    tip = "图片格式不支持，请选择png/gif/bmp/jpeg/jpg格式的图片！", // 设定提示信息
    filters = {
      "jpeg": "/9j/4",
      "gif": "R0lGOD",
      "png": "iVBORw",
      "bmp": "Qk02cC"
    },
    uploadUrl = HTTP_HOST + '/public/index/Upload'; // 上传地址

  if (window.FileReader) { // html5方案
    for (var i = 0, f, fr; i < el.files.length; i++) {
      fr = new FileReader();
      f = el.files[i];
      fr.onload = function (e) {
        if (!validateImg(e.target.result)) {
          layer.msg(tip);
          el.value = null;
        }
      };
      fr.readAsDataURL(f);
      el.value !== null && uploading(f);
    }
  } else { // 降级处理
    if (!/\.jpg$|\.png$|\.gif$|\.bmp$/i.test(el.value)) {
      layer.msg(tip);
      el.value = null;
    }
  }
  // 上传
  function uploading(f) {
      
    var form = new FormData();
    form.append("fileList", f);// 文件对象
    // XMLHttpRequest 对象
    var xhr = new XMLHttpRequest();
    xhr.open("post", uploadUrl, true);
    xhr.onload = function () {
      var imgList = target.val()
       ,res = xhr.responseText
               
                //.replace(/"|\\/g, '')
      ;
  res = eval(res);
  targetName.val(f.name);
//      imgList += imgList == '' ? res : '|' + res;
      imgList = res;
      target.val(imgList);
      randerImage(show, res);
    };
    xhr.send(form);
  }

  function validateImg(data) {
    var pos = data.indexOf(",") + 1;
    for (var e in filters) {
      if (data.indexOf(filters[e]) === pos) {
        return e;
      }
    }
    return null;
  }
}



/**
 * 根据图片地址字符串渲染li
 * @param {Element}   target    显示容器的jquery对象
 * @param {String}    imgList   拼接图片地址字符串
 * @param {String}    glue      图片地址字符串分隔符 默认 '|'
 *
 * */
function randerImage(target, imgList, glue) {
  glue = glue || '|';
  if (typeof imgList !== 'string') {
    return layer.alert('地址只能是字符串!');
  }
  if (!imgList) {
    return false;
  }
  imgList = imgList.split(glue);

  for (var i = 0, html = ''; i < imgList.length; i++) {
    html += '<li class="gc_item"> ' +
      '<img src="' + imgList[i] + '"> ' +
      '<span class="gc_item_action"> ' +
      '<span class="gc_preview">预览</span> ' +
      '<span class="gc_delete">删除</span> ' +
      '</span> ' +
      '</li>'
  }
  target.append(html);
  $('.gc_upload_btn').remove();
}

/* 上传PDF函数封装 */
function gc_upload_PDF(el) {
  var target = $($(el).data('input')),
    targetName = $($(el).data('name')),
    show = $('.gc_file_list'),
    tip = "文件格式不支持，请选择PDF格式的文件！", // 设定提示信息
    uploadUrl = HTTP_HOST + '/public/index/Upload'; // 上传地址

  if (!/\.pdf$/i.test(el.value)) {
    layer.msg(tip);
    el.value = null;
  } else if (el.files[0].size > 1024 * 1024 * 2) {
    layer.msg('文件大小超限,请选择小于2M的文件!');
    el.value = null;
  } else {
    uploading(el.files[0]);
  }

  // 上传
  function uploading(f) {
    var form = new FormData();
    form.append("fileList", f);// 文件对象
    var xhr = new XMLHttpRequest();
    xhr.open("post", uploadUrl, true);
    xhr.onload = function () {
      var path = xhr.responseText;
      path = eval(path);
      target.val(path);
      targetName.val(f.name);
      layer.msg('护照上传成功', {time: 1000});
      randerPDF(show, path);
    };
    xhr.send(form);
  }
}

/**
 * 根据PDF文件地址渲染li
 * @param {Element}   target    显示容器的jquery对象
 * @param {String}    src       地址字符串
 *
 * */
function randerPDF(target, src) {
  if (!src) {
    return false;
  }
  target.append('<li class="gc_item"> ' +
    '<img src="/public/static/images/pdf.jpg" data-src="' + src + '"> ' +
    '<span class="gc_item_action"> ' +
    '<span class="gc_delete">删除</span> ' +
    '</span> ' +
    '</li>');
  $('.gc_upload_btn').remove();
}

/** 点击查看大图
 * @param {String} src 图片路径
 * */
function previewImage(src) {
  layer.open({
    type: 1,
    title: false,
    closeBtn: 2,
    area: '80%',
    skin: 'layui-layer-nobg', //没有背景色
    content: '<div class="text-center">' +
    '<img style="max-width:100%" src="' + src + '">' +
    '</div>'
  });
}

//js 计算
var Acc = {
  /** 加法
   * @param {Number} arg1 加数1
   * @param {Number} arg2 加数2
   * @param {Number} places 保留位数,默认2
   * */
  add: function (arg1, arg2, places) {
    var r1, r2, m;
    places = parseInt(places);
    if (places < 0 || places > 20 || isNaN(places)) {
      places = 2
    }
    try {
      r1 = arg1.toString().split(".")[1].length
    } catch (e) {
      r1 = 0
    }
    try {
      r2 = arg2.toString().split(".")[1].length
    } catch (e) {
      r2 = 0
    }
    m = Math.pow(10, Math.max(r1, r2));
    return ((arg1 * m + arg2 * m) / m).toFixed(places);
  },

  /* 减法 */
  sub: function (arg1, arg2) {
    var r1, r2, m;
    try {
      r1 = arg1.toString().split(".")[1].length
    } catch (e) {
      r1 = 0
    }
    try {
      r2 = arg2.toString().split(".")[1].length
    } catch (e) {
      r2 = 0
    }
    m = Math.pow(10, Math.max(r1, r2));
    return ((arg1 * m - arg2 * m) / m);
  },

  /* 乘法 */
  mul: function (arg1, arg2) {
    var m = 0, s1 = arg1.toString(), s2 = arg2.toString();
    try {
      m += s1.split(".")[1].length
    } catch (e) {
    }
    try {
      m += s2.split(".")[1].length
    } catch (e) {
    }
    return Number(s1.replace(".", "")) * Number(s2.replace(".", "")) / Math.pow(10, m)
  },

  /* 除法 */
  div: function (arg1, arg2) {
    var t1 = 0, t2 = 0, r1, r2;
    try {
      t1 = arg1.toString().split(".")[1].length
    } catch (e) {
    }
    try {
      t2 = arg2.toString().split(".")[1].length
    } catch (e) {
    }
    with (Math) {
      r1 = Number(arg1.toString().replace(".", ""));
      r2 = Number(arg2.toString().replace(".", ""));
      return (r1 / r2) * pow(10, t2 - t1);
    }
  }

};

/** 四舍五入
 * @param {Number} val 需要进行四舍五入运算的数字
 * @param {Number} places 保留位数
 * */
function forDight(val, places) {
  return Math.round(val * Math.pow(10, places)) / Math.pow(10, places);
}

/** 下载文件 文件名为最后一级地址名
 * @param {String} url  下载地址
 * @param {String} name 文件名
 * */
function downloadFile(url, name) {
  var a = document.createElement('a'),
    list = url.split('/');
  a.href = url;
  console.log(name || list[list.length - 1])
  a.download = name || list[list.length - 1];
  $(document.body).append(a);
  a.click();
  a.parentNode.removeChild(a);
}

