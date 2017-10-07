<?php
namespace app\index\model;
use think\Model;
use think\Db;

class Passport extends Model
{
	protected function getNationalityStrAttr($value, $data)
	{	
		$nationality = $data['nationality'];
		if ($nationality) {
			$country = Db::table('country')->where('country_co', '=', $nationality)->find();
			$nationality = $country['country_na'];
		}
		return $nationality;
	}
}



