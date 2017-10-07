<?php
namespace app\index\controller;

use app\common\LoginController;
use app\index\model\BillHead;
use app\index\model\CustomerParams;
//use think\Paginator;
use think\Db;

use app\index\model\DataDictionary;
class Index extends LoginController
{
    public function index()
    {   
        
        $ser = $this->getService(__CLASS__);
        $where['id'] = ['>', '0'];

        $status = input('status');

        if ($status != '') {
            $where['status'] = ['=', $status];
            //$where['user_id'] = ['=', $this->user['id']];
        }

        $list = BillHead::where($where)->order('id DESC')->paginate();
        
        $this->assign('list', $list);
        $this->assign('overview', $ser->getOverview());
		return $this->fetch('index');
    }

	public function detail($id)
    {
        $billHead = BillHead::get($id, 'cargos,country,passport');

        $this->assign('billHead', $billHead);

        $this->assign('customerparams', CustomerParams::get(1));

        return $this->fetch();
    }
	public function check()
    {   
        $billHead = BillHead::get(input('id'), 'cargos');

        if (null == $billHead) {
            abort('404', '搞错了吧');
        }

        $this->assign('billHead', $billHead);

        return $this->fetch('check');
    }

    function checked()
    {
        $args = input();
        if (!isset($args['id'])) {
            abort('404', '搞错了吧');
        }
        $check_result = element('check_result', $args);
        switch ($check_result) {
            case '1':
                $args['status'] = 2;
                break;
           case '2':
                $args['status'] = 4;
                break;
        }
       
       
        $args['check_time'] = time();
        $billHead = new BillHead();
        $billHead->allowField(true)
                 ->save($args, ['id' => $args['id']]);
    }

}