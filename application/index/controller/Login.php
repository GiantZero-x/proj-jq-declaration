<?php
namespace app\index\controller;
use think\Controller;
use app\index\model\User;
use think\Cookie;
class Login extends Controller
{	

	public function index() {
		$this->view->engine->layout(false);
		return $this->fetch('index');
	}

    public function login()
    {
		$username = input('username');
		$password = input('password');
		$user = User::get([
			'username' => $username,
			'password' => md5($password)
		]);
		if (!$user){
			return json(['code'=> 401, 'msg'=>'用户或密码错误']);
		}
		unset($user['password']);
		// 设置
		cookie('user', $user, 113600);
		
		return json(['code'=> 200, 'msg'=>'success']);
    }

    public function loginOut() 
    {
    	$userStr = cookie('user');

    	if (!$userStr){
 			$this->error('未登陆', 'Login/index');
    	}
    	Cookie::delete('user');
    	$this->success('退出成功', 'Login/index');
    }

}
