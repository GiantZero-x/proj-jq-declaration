<?php
namespace app\index\model;
use think\Model;

class DataDictionary extends Model
{
	static public $dataType = [['en_name' =>'export_port', 'cn_name' => '出口口岸'],
								['en_name' =>'inland_mode', 'cn_name' => '境内运输方式'],
								['en_name' =>'pack', 'cn_name' => '包装方式'],
								['en_name' =>'box_type', 'cn_name' => '柜型'],
								['en_name' =>'loading_mode', 'cn_name' => '装柜方式'],
								['en_name' =>'ediID', 'cn_name' => '报关方式'],
								['en_name' =>'customs_case_type', 'cn_name' => '报关单类型'],
								['en_name' =>'customs_address', 'cn_name' => '申报地海关'],
								['en_name' =>'terms_mode', 'cn_name' => '成交方式'],
								['en_name' =>'supervise_mode', 'cn_name' => '监管方式']];	
	static function getByName(array $names) {
		$result = [];
		foreach ($names as $key => $value) {
			$list = self::all(['name' => $value]);
			if ($list){
				$result[$value] = collection($list)->toArray();
			}
		}
		return $result;
	}


	static function getKeyByNameValue($name, $value)
	{
		$obj = self::field('key')->where(['name'=>$name])->where(['value'=>$value])->find();
		if (isset($obj->key))
			return $obj->key;
	}
}





							
