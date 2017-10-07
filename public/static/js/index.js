/**
 * Created by giantR on 2017/4/20.
 */
/* 主页js */
!function ($) {
  $(function () {
    /* 主页点击 */
    $(document).on('click', '.action_detail,.action_declare,.action_delete,.action_copy,.action_edit,.action_check', function () {
      var $this = $(this),
        id = $this.closest('tr').find('input[type=checkbox]').val();
      if ($this.hasClass('action_detail')) {          // 详情
        // layer.msg('详情 ' + id);
        // setTimeout(function () {
          location.href = '/public/index/index/detail/id/' + id;
        // },1000)
      } else if ($this.hasClass('action_declare')) {  // 申报
        layer.msg('申报 ' + id)
      } else if ($this.hasClass('action_delete')) {   // 删除
        //layer.msg('删除 ' + id)
        var url = $this.data('url');
        $(this).closest('tr').remove();
        $.get(url + '?id='+id, function(res){

        });
        layer.msg('删除成功');
        location.reload()
      } else if ($this.hasClass('action_copy')) {     // 复制
        layer.msg('复制 ' + id)
      } else if ($this.hasClass('action_edit')) {     // 编辑
        var url = $(this).data('url') + "?id=" + id;
        location.href = url;
      } else if ($this.hasClass('action_check')) {    // 审核
        layer.msg('审核 ' + id);
        setTimeout(function () {
          location.href = '/public/index/index/check'
        },1000)
      }
    });
  })
}(window.jQuery);

