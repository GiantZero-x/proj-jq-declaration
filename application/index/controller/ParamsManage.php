<?php
namespace app\index\controller;

use app\common\LoginController;
use app\index\model\Hscode;
use app\index\model\HscodeUnit;
use think\Db;
use app\common\helper\CommonHelper;

class ParamsManage extends LoginController
{
    public function index()
    {
        return $this->fetch('index');
    }


    function get_data()
    {
        $args = input();
        $oper = element("oper", $args);
        $id = element("id", $args);
        $name = element('name', $args);
        $args['name'] = $name;
        $hscode = new Hscode();
        switch ($oper) {
            case 'add':
                $hscode->data($args)->allowField(true)->save();
                break;
            case 'del':
                if ($id) {
                    Hscode::destroy($id);
                }
                break;
            case 'edit':
                if ($id) {
                    $hscode->allowField(true)->save($args, ['id' => $id]);
                }
                break;
        }

        if (element("_search", $args) == 'true') {
            $gridSearch = CommonHelper::$gridSearch;
            $filters = json_decode(element('filters', $args));
            $where;
            if (isset($filters->rules))
                foreach ($filters->rules as $key => $value) {
                    $where[$value->field] = ['like', "%" . $value->data . "%"];
                }
            switch ($filters->groupOp) {
                case 'AND':
                    $data = Db::name('hscode')->
                    order('id DESC')->
                    where($where)->
                    paginate(input('rows'))->toArray();
                    break;

                case 'OR':
                    $data = Db::name('hscode')->
                    order('id DESC')->
                    whereOr($where)->
                    paginate(input('rows'))->toArray();
                    break;
            }


        } else {
            $data = Db::name('hscode')->
            order('id DESC')->
            paginate(input('rows'))->toArray();
        }

        $data = CommonHelper::getPages($data);
        return json($data);
    }

    public function hscodeVerify($hsCode)
    {
        return (int) (HscodeUnit::get(['hs_code'=>$hsCode]) !== null);
    }
}





