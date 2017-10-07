<?php
namespace app\api\controller;

use \think\Request;
use app\api\model\DigitalChina;
use app\index\model\BillHead;

class DigitalChinaController
{
    protected $beforeActionList = [
        'post' => ['only'=>'tcs02,sfd02'],
    ];
    public function post(Request $request)
    {
        $post = $request->post();

        if (!count($post)) {
            die([
                'success' => false,
                'error_msg' => 'post数据为空'
            ]); 
        }
    }


    public function tcs02(Request $request)
    {
        $params = $request->param();

        $digitalChina = new DigitalChina();

        $digitalChina->return = json_encode($params, JSON_UNESCAPED_UNICODE);

        $digitalChina->save();
/*
        if ('1' === $request->param('IsSuccess')) {
            
            $billHead = BillHead::get(['serial_no' => $request->param('notify_id')]);

            $billHead->status = 3;

            $billHead->save();
        }*/
        
        return [
            'success' => true,
            'error_msg' => '接收成功'
        ];
    }

    public function sfd02(Request $request)
    {
        $digitalChina = new DigitalChina();

        $digitalChina->return = json_encode($request->param(), JSON_UNESCAPED_UNICODE);

        $digitalChina->save();

        return [
            'success' => true,
            'error_msg' => '接收成功'
        ];
    }
}













































