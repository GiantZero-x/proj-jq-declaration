<?php
namespace app\index\model;
use think\Model;

class User extends Model
{
	public $type = ['管理员', '运营人员', '注册用户','审核人员'];
	static public $staticType = ['管理员', '运营人员', '注册用户','审核人员'];
	// 验证规则
	protected $rule = [
		'type' => 'require',
		'username' => 'require',
		'telephone' => 'require',
	];

	protected function getTypeStrAttr($value, $data)
	{
		
		if ($data['type'] != '') {
			return $this->type[$data['type']];
		}
	}
}

