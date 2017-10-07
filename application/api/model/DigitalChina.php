<?php
namespace app\api\model;

use think\Model;

class DigitalChina extends Model
{
	protected $autoWriteTimestamp = true;

	protected $insert = ['type'];

	protected function setTypeAttr()
	{
		return request()->action();
	}
}
