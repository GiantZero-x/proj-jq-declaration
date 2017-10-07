<?php
namespace app\index\controller;

use app\common\LoginController;
use app\index\model\User;
use think\Paginator;
use think\Db;

class Account extends LoginController
{
    function index()
    {	
        $user = new User();
        $users = User::where('status', 1)->paginate();
        $this->assign('type', $user->type);
    	$this->assign('users', $users);
		return $this->fetch('index');
    }

    function delete()
    {
    	$user = User::get(input('id'));
    	$user->status = 0;
    	return $user->save();
    }

    function save()
    {
       
    	$id = input('id');
		$data = input();
		$password = element('password', $data);
        $username = element('username', $data);
		if ($password) {
			$data['password'] = md5($password);
		}

		if ($id) {
    		$this->update($id, $data);
    		$this->redirect('Account/index');
    	}

    	$user = new User($data);
		$user->allowField(true)->save();
        $this->redirect('Account/index');
    }

    function usernameVerify($username)
    {   
        if (!$username) {
            return json('用户名重复');
        }
        $user = User::where('username', '=', $username)->find();
        if ($user) {
            return json('用户名重复');
        }
        return json('SUCCESS');
    }

    private function update($id, $data)
    {
    	$user = new User();
		$user->allowField(['type', 'password', 'telephone', 'name'])->save($data, ['id' => $id]);
    }
}