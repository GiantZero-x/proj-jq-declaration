<?php
namespace app\common\helper;

class CommonHelper {

	static public $gridSearch = ['cn'=>'like'];
	static function getPages(array $page)
	{
		$page['pages'] = ceil($page['total'] / $page['per_page']);
		return $page;
	}
}
