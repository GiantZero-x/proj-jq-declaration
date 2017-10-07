<?php
namespace app\index\controller;

use app\common\LoginController;
use app\index\model\User;

class Home extends LoginController
{
    public function index()
    {
    	$user = User::get($this->user['id'])->toArray();
    	$this->assign('user', $user);
        return $this->fetch('index');
    }

    public function rest_password()
    {
    	$oldPwd = input('oldPwd');
    	$newPwd = input('newPwd');
    	$confirmPwd = input('confirmPwd');
    	if ($newPwd !== $confirmPwd || $newPwd == "") {
    		return json('两次输入的密码不一样');
    	}

    	$user = User::get($this->user['id']);
    	if ($user->password != md5($oldPwd)) {
    		return json('原密码错误');
    	}

    	$user->password = md5($newPwd);
    	$user->save();
    	return "修改成功";
    }

    function update()
    {
    	$user = new User;
		$user->allowField(true)->save(input(), ['id'=>$this->user['id']]);
    }
}

