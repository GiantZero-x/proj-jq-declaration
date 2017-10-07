/**
 * Created by giantR on 2017/4/20.
 */
/* 报关设置js */
!function ($) {
  $(function () {
    /* 报关参数,报关行设置保存修改 */
    $('.save_btn').click(function () {
      var $this = $(this);
      var url = $($this.data('type')).find('form').attr('action');
      data = $($this.data('type')).find('form').serialize();
      $.post(url, data, function(data){
        console.log(data);
      });
      layer.msg("保存成功");
      setTimeout(function () {
        $this.button('reset')
      }, 500)
    });
  })
}(window.jQuery);

