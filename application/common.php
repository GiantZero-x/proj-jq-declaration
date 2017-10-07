<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
//
if (!function_exists('rand_float')) {

	function rand_float($min = 0, $max = 10, $decimal = 2) {
		$num = $min + mt_rand() / mt_getrandmax() * ($max - $min);

		return sprintf("%.{$decimal}f", $num);

	}
}
