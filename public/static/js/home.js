/**
 * Created by giantR on 2017/4/20.
 */
/* 账号管理js */
!function ($) {
  $(function () {
    /* 确认修改密码 */
    $('.submit_update').click(function () {
      var url = $(this).data('url');
      var outurl = $(this).data('outurl');
      var modal = $(this).closest('.modal'),
        data = modal.find('form').serialize();
      $.post(url, data, function (res) {
        layer.msg(res);
        if (res == '修改成功') {
          setTimeout(function () {
            modal.modal('hide');
          }, 2000);
          //window.location.href = outurl;
        }
      });

    });
    /* 更新资料 */
    $('.save_btn').click(function () {
      var $this = $(this),
        url = $this.data('url'),
        data = $this.closest('.panel').find('form').serialize();

      // 验证
      if (!/^(13|15|18)\d{9}$/.test($('[name="telephone"]').val())) {
        $this.button('reset');
        layer.msg('请正确输入手机号码');
        return false;
      }
      if (!/[\w!#$%&'*+/=?^_`{|}~-]+(?:\.[\w!#$%&'*+/=?^_`{|}~-]+)*@(?:[\w](?:[\w-]*[\w])?\.)+[\w](?:[\w-]*[\w])?/.test($('[name="email"]').val())) {
        $this.button('reset');
        layer.msg('请正确输入邮箱');
        return false
      }
      $.post(url, data, function (res) {
        layer.msg('更新成功');
        window.location.reload();
      });
    })
  })
}(window.jQuery);

