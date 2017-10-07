<?php
namespace app\index\controller;

use app\common\LoginController;
use app\index\model\CustomerParams;
use app\index\model\Operator;
use app\index\model\CustomsBroker;
use app\index\model\CorrelateCompany;
use app\index\model\DataDictionary;
use app\index\model\Passport;
use app\index\model\Country;
use think\Db;
use think\Paginator;
use app\common\helper\CommonHelper;

class Clearance extends LoginController
{
	function index()
    { 
        $this->assign('tab', input('tab'));
    	$cusParams = Customerparams::get(1)->toArray();
    	$ddNames   = Customerparams::getDD();
    	$dataDic   = DataDictionary::getByName($ddNames);

    	$operator       = Operator::get(1)->toArray();
    	$customsBroker  = CustomsBroker::get(1)->toArray();
    	$deliverCompany = CorrelateCompany::get(1)->toArray();

    	$this->assign('dataDic', $dataDic);
    	$this->assign('cusParams', $cusParams);
		$this->assign('operator', $operator);
    	$this->assign('customsBroker', $customsBroker);
    	$this->assign('deliverCompany', $deliverCompany);
        return $this->fetch('index');
    }

    function get_passport()
    {   
        $args = input();
        $oper = element("oper", $args);
        $id   = element("id", $args);
        $objPassport = new Passport();
        if (isset($args['nationality_str'])) {
            $args['nationality'] = $args['nationality_str'];
            $country = Country::where('country_co', '=', $args['nationality_str'])->find();
            $args['nationality_str'] = $country['country_na'];
        }
        switch ($oper) {
            case 'add':
                $objPassport->data($args)->allowField(true)->save();
                break;
            case 'del':
                if ($id){
                    Passport::destroy($id);
                }
                break;
            case 'edit':
                if ($id){
                    $objPassport->allowField(true)->save($args, ['id'=>$id]);
                }
                break;
        }

        if (element("_search", $args) == 'true') {
            $gridSearch = CommonHelper::$gridSearch;
            $searchOper = element('searchOper', $args);
            $searchString = element('searchString', $args);
            $searchField = element('searchField', $args);
            $data = Db::name('passport')->
                        order('id DESC')->
                        where($searchField, element($searchOper, $gridSearch), '%'.$searchString.'%')->
                        paginate(input('rows'))->toArray();
        } else {
            $data = Db::name('passport')->
                        order('id DESC')->
                        paginate(input('rows'))->toArray();
        }

        $data = CommonHelper::getPages($data);
        return json($data);
    }

    private function passport_help($id)
    {
        // $objPassport = Passport::get($id);

        // $nationality = $objPassport->nationality;
        // if ($nationality) {
        //     $country = Country::where('country_co', '=', $nationality)->find();
        //     $nationality_str = $country['country_co'];
        //     $nationality->nationality_str = $nationality_str;
        //     $nationality->save();
        // }
        
    }

    function get_Company()
    {
        $args = input();
        $oper = element("oper", $args);
        $id = element("id", $args);
        $type = element("type", $args);
        $objCorrelateCompany = new CorrelateCompany();
        switch ($oper) {
            case 'add':
                $objCorrelateCompany->data($args)->allowField(true)->save();
                break;
            case 'del':
                if ($id){
                    CorrelateCompany::destroy($id);
                }
                break;
            case 'edit':
                if ($id){
                    $objCorrelateCompany->allowField(true)->save($args, ['id'=>$id]);
                }
                break;
        }

        // if (element("_search", $args) == 'true') {
        //     $gridSearch = CommonHelper::$gridSearch;
        //     $searchOper = element('searchOper', $args);
        //     $searchString = element('searchString', $args);
        //     $searchField = element('searchField', $args);
        //     $data = Db::name('correlateCompany')->
        //                 order('id DESC')->
        //                 where($searchField, element($searchOper, $gridSearch), '%'.$searchString.'%')->
        //                 where('type', $type)->
        //                 paginate(input('rows'))->toArray();
        // } else {
        //    $data = Db::name('correlateCompany')->where('type', $type)
        //             ->paginate(input('rows'))->toArray();
        // }
        if (element("_search", $args) == 'true') {
            $gridSearch = CommonHelper::$gridSearch;
            $filters    = json_decode(element('filters', $args));
            $where;
            if (isset($filters->rules))
                foreach ($filters->rules as $key => $value) {
                    $where[$value->field] = ['like', "%".$value->data."%"];
                }
            switch ($filters->groupOp) {
                 case 'AND':
                     $data = Db::name('correlateCompany')->
                        order('id DESC')->
                        where($where)->
                        where('type', $type)->
                        paginate(input('rows'))->toArray();
                     break;
                 
                case 'OR':
                     $data = Db::name('correlateCompany')->
                                order('id DESC')->
                                whereOr($where)->
                                where('type', $type)->
                                paginate(input('rows'))->toArray();
                     break;
             } 

            
        } else {
            $data = Db::name('correlateCompany')->where('type', $type)
                    ->paginate(input('rows'))->toArray();
        }
        
        $data = CommonHelper::getPages($data);
        return json($data);
    }

    function save_customer_params()
    {
        $args = input();
        $id = element('id', $args);
        $customerParams = new CustomerParams();
        if ($id){
            $customerParams->allowField(true)->save($args, ['id'=>$id]);
        } else {
            $customerParams->data($args)->allowField(true)->save();
        }
    }

    function save_customs_broker()
    {   
        $args = input();
        dump($args);
        $id = element('id', $args);
        $customsBroker = new CustomsBroker();
        if ($id){
            $customsBroker->allowField(true)->save($args, ['id'=>$id]);
        } else {
            $customsBroker->data($args)->allowField(true)->save();
        }
    }
}

// correlate_company_id_deliver
// correlate_company_id_business
