<?php
namespace app\index\controller;

use think\Request;
use think\Controller;
use app\index\service\Tcs01;
use app\index\service\Sfd01;

class DigitalChinaCallback extends Controller
{
    public function tcs02(Request $request)
    {
    	dump($request->param());
        return json([
        	'success' => true,
        	'error_msg' => '失败原因',
        ]);
    }

    /*{
"success”:true/false,    	// bool类型
"error_msg":"失败原因" 	// String类型
}*/

    public function sfd02(Request $request)
    {
    	dump($request->param());

        return json([
        	'success' => true,
        	'error_msg' => '失败原因',
        ]);
    }
}






