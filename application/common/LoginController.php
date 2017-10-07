<?php
namespace app\common;
use think\Controller;
use app\index\model\User;

class LoginController extends Controller
{
	protected $user;
	private function setUser($user) {
		$data = [];
		$data['id'] = $user['id']; 
		$data['username'] = element('username', $user);
		$data['telephone']= element('telephone', $user);
		$data['nickname'] = element('nickname', $user); 
		$data['type'] = element('type', $user); 
		$data['typeStr'] = User::$staticType[element('type', $user) ? element('type', $user) : 2]; 
		$this->assign('userInfo', $data);
		$this->user = $data;
	}
		
	public function _initialize()
    {	
    	
		$request = \think\Request::instance(); 
		define('__MODULE__', $request->module()); 
		define('__CONTROLLER__', $request->controller()); 
		define('__ACTION__', $request->action());

        $this->isVisitor();
    }

	private function isVisitor() {
		$userStr = cookie('user');

    	if (!$userStr){
 			$this->error('未登陆', 'Login/index', 0);
    	}

    	$user = json_decode($userStr, true);
		$this->setUser($user);
	}

	protected function getService($strclazz)
	{	
		$clazz = str_replace('controller', 'service', $strclazz."Service");
        $reflect = new \ReflectionClass($clazz);
        $instance = $reflect->newInstance();
        $instance->user = $this->user;

        return $instance;
	}
}
