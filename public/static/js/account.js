/**
 * Created by giantR on 2017/4/20.
 */
/* 账号管理js */
!function ($) {
  $(function () {
    /* 用户新增修改 */
    $('#editUser').on('show.bs.modal', function (event) {

      var button = $(event.relatedTarget),
        id = button.closest('tr').data('id'),
        type = button.data('type'),
        modal = $(this);
      modal.find('.modal-title').text(type + '用户');
      modal.find('#username').prop('disabled', '');
      if (type === '修改') {
        var ds = button.closest('tr').find('td');
        modal.find('#username').prop('value', ds.eq(0).text());
        modal.find('#username').prop('disabled', 'disabled');
        modal.find('#name').prop('value', ds.eq(1).text());
        modal.find('#telephone').prop('value', ds.eq(2).text());
        modal.find('#type option').each(function (i, v) {
          var $this = $(v);
          if ($this.text() === ds.eq(3).text()) {
            $this.prop('selected', true);
          }
        })
        modal.find('#id').prop('value', id);
      } else {
        modal.find('input,select').val('');
      }
    });

    /* 删除 */
    $('.del_user').click(function () {
      var url = $(this).data('url');
      var self = $(this).closest('tr');
      var id = self.data('id');
      layer.confirm('是否确定删除？', {
        btn: ['确定', '取消'] //按钮
      }, function () {
        $.post(url, {id: id}, function (data) {
          {
            if (data) {
              layer.msg('删除成功');
              self.hide();
            }
          }
        })
      });
    });

    /* 提交 */
    $('.submit_btn').click(function () {
      var form = $('#editUserForm'),
        userName = form.find('#username'),
        userId = form.find('#id'),
        $this = $(this);
      if (userName.val().trim() === '') {
        layer.msg('用户名不能为空!');
        userName.focus();
        return false;
      }
      // 新建验证
      if (userId.val() === '') {
        $.post(HTTP_HOST + '/public/index/account/usernameVerify', {username: userName.val()}, function (res) {
          if (res === 'SUCCESS') {
            layer.msg('新建成功')
            form.submit();
          } else {
            layer.msg(res);
            setTimeout(function () {
              $this.button('reset');
            }, 1000)
            userName.focus()
          }
        })
      } else {
        form.submit();
      }
    });
  })
}(window.jQuery);

