<?php
namespace app\index\model;
use think\Model;

class CustomerParams extends Model
{
	static private $dataDictionary = ['pack', 'inland_mode', 'terms_mode', 'supervise_mode'];

	static function getDD() {
		return self::$dataDictionary;
	}
}





