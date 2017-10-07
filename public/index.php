<?php
// 设置此页面的过期时间(用格林威治时间表示)，只要是已经过去的日期即可。
header(" Expires: Mon, 26 Jul 1970 05:00:00 GMT ");
// 设置此页面的最后更新日期(用格林威治时间表示)为当天，可以强制浏览器获取最新资料
header(" Last-Modified:" . gmdate(" D, d M Y H:i:s ") . "GMT ");
// 告诉客户端浏览器不使用缓存，HTTP 1.1 协议
header(" Cache-Control: no-cache, must-revalidate ");
// 告诉客户端浏览器不使用缓存，兼容HTTP 1.0 协议
header(" Pragma: no-cache ");

// [ 应用入口文件 ]

// 定义应用目录
define('APP_PATH', __DIR__ . '/../application/');
define('HOME_PATH', __DIR__ . '/..');
define('__STATIC__', '/public/static');
define('PAGESIZE', 10);
define('HTTP_HOST', 'http://' . $_SERVER['HTTP_HOST']);
// 加载框架引导文件
require __DIR__ . '/../thinkphp/start.php';
