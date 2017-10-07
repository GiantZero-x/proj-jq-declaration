<?php
namespace app\index\controller;
use app\common\LoginController;
use app\index\model\DataDictionary;
use app\index\model\Country;
use app\index\model\PortLin;
use app\index\model\WhiteCards;
use think\Db;
use app\common\helper\CommonHelper;

class DataManage extends LoginController
{
    public function index()
    {	
    	$this->assign("dataType", DataDictionary::$dataType);
        return $this->fetch('index');
    }

    function get_data()
    {	
        $args = input();
        $oper = element("oper", $args);
        $id   = element("id", $args);
        $name = element('name', $args);
        $args['name'] = $name;
        $dataDictionary = new DataDictionary();
        switch ($oper) {
            case 'add':
                $dataDictionary->data($args)->allowField(true)->save();
                break;
            case 'del':
                if ($id){
                    DataDictionary::destroy($id);
                }
                break;
            case 'edit':
                if ($id){
                    $dataDictionary->allowField(true)->save($args, ['id'=>$id]);
                }
                break;
        }

        // if (element("_search", $args) == 'true') {
        //     $gridSearch = CommonHelper::$gridSearch;
        //     $searchOper = element('searchOper', $args);
        //     $searchString = element('searchString', $args);
        //     $searchField = element('searchField', $args);
        //     $data = Db::name('data_dictionary')->
        //                 order('id DESC')->
        //                 where('name', $name)->
        //                 where($searchField, element($searchOper, $gridSearch), '%'.$searchString.'%')->
        //                 paginate(input('rows'))->toArray();
        // } else {
        //     $data = Db::name('data_dictionary')->
        //                 order('id DESC')->
        //                 where('name', $name)->
        //                 paginate(input('rows'))->toArray();
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
                     $data = Db::name('data_dictionary')->
                        order('id DESC')->
                        where($where)->
                        where('name', $name)->
                        paginate(input('rows'))->toArray();
                     break;
                 
                case 'OR':
                     $data = Db::name('data_dictionary')->
                                order('id DESC')->
                                whereOr($where)->
                                where('name', $name)->
                                paginate(input('rows'))->toArray();
                     break;
             } 

            
        } else {
            $data = Db::name('data_dictionary')->
                order('id DESC')->
                where('name', $name)->
                paginate(input('rows'))->toArray();
        }
        

		
        $data = CommonHelper::getPages($data);
        return json($data);
    }

    function get_country()
    {   
        $args = input();
        $oper = element("oper", $args);
        $id   = element("id", $args);
        $country = new Country;

        switch ($oper) {
            case 'add':
                $country->data($args)->allowField(true)->save();
                break;
            case 'del':
                if ($id){
                    Country::destroy($id);
                }
                break;
            case 'edit':
                if ($id){
                    $country->allowField(true)->save($args, ['id'=>$id]);
                }
                break;
        }

        // if (element("_search", $args) == 'true') {
        //     $gridSearch = CommonHelper::$gridSearch;
        //     $searchOper = element('searchOper', $args);
        //     $searchString = element('searchString', $args);
        //     $searchField = element('searchField', $args);
        //     $data = DB::name('country')->
        //             where($searchField, element($searchOper, $gridSearch), '%'.$searchString.'%')->
        //             order('country_na')->paginate(input('rows'))->toArray();
        // } else {
        //     $data = DB::name('country')->order('country_na')->paginate(input('rows'))->toArray();
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
                     $data = Db::name('country')->
                        order('country_na')->
                        where($where)->
                        paginate(input('rows'))->toArray();
                     break;
                 
                case 'OR':
                     $data = Db::name('country')->
                                order('country_na')->
                                whereOr($where)->
                                paginate(input('rows'))->toArray();
                     break;
             } 

            
        } else {
            $data = DB::name('country')->order('country_na')->paginate(input('rows'))->toArray();
        }
        

    	
        $data = CommonHelper::getPages($data);
        return json($data);
    }


    function get_portLin()
    {   
        $args = input();
        $oper = element("oper", $args);
        $id   = element("id", $args);
        $portLin = new PortLin;

        switch ($oper) {
            case 'add':
                $portLin->data($args)->allowField(true)->save();
                break;
            case 'del':
                if ($id){
                    portLin::destroy($id);
                }
                break;
            case 'edit':
                if ($id){
                    $portLin->allowField(true)->save($args, ['id'=>$id]);
                }
                break;
        }
        // if (element("_search", $args) == 'true') {
        //     $gridSearch = CommonHelper::$gridSearch;
        //     $searchOper = element('searchOper', $args);
        //     $searchString = element('searchString', $args);
        //     $searchField = element('searchField', $args);
        //     $data = DB::name('port_lin')->
        //             where($searchField, element($searchOper, $gridSearch), '%'.$searchString.'%')->
        //             order('port_c_cod')->paginate(input('rows'))->toArray();
        // }else {
        //     $data = DB::name('port_lin')->order('port_c_cod')->paginate(input('rows'))->toArray();
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
                     $data = Db::name('port_lin')->
                        order('port_c_cod')->
                        where($where)->
                        paginate(input('rows'))->toArray();
                     break;
                 
                case 'OR':
                     $data = Db::name('port_lin')->
                                order('port_c_cod')->
                                whereOr($where)->
                                paginate(input('rows'))->toArray();
                     break;
             } 

            
        } else {
            $data = DB::name('port_lin')->order('port_c_cod')->paginate(input('rows'))->toArray();
        }
        

        $data = CommonHelper::getPages($data);
        return json($data);
    }

    function dataVerify($name, $key, $value, $id = '')
    {   

        if ($id) {
            $key = DataDictionary::where('name', $name)->where('key', $key)->where('id', '<>', $id)->find();
            $value = DataDictionary::where('name', $name)->where('value', $value)->where('id', '<>', $id)->find();
        } else {
            $key = DataDictionary::where('name', $name)->where('key', $key)->find();
            $value = DataDictionary::where('name', $name)->where('value', $value)->find();
        }

        $result['key']   = $key ? '显示值重复' : 'SUCCESS';
        $result['value'] = $value ? '报关值重复' : 'SUCCESS';
        return json($result);
    }

    function countryVerify($country_co, $country_na, $id = '')
    {   
        if ($id) {
            $country_co = Country::where('country_co', $country_co)->where('id', '<>', $id)->find();
            $country_na = Country::where('country_na', $country_na)->where('id', '<>', $id)->find();
        } else {
            $country_co = Country::where('country_co', $country_co)->find();
            $country_na = Country::where('country_na', $country_na)->find();
        }

        $result['country_co'] = $country_co ? '国家代码重复' : 'SUCCESS';
        $result['country_na'] = $country_na ? '国家名称重复' : 'SUCCESS';
        return json($result);
        
    }

    function portLinVerify($port_code, $port_c_cod, $id = '')
    {   
       if ($id) {
            $port_code = PortLin::where('port_code', $port_code)->where('id', '<>', $id)->find();
            $port_c_cod = PortLin::where('port_c_cod', $port_c_cod)->where('id', '<>', $id)->find();
       } else {
            $port_code = PortLin::where('port_code', $port_code)->find();
            $port_c_cod = PortLin::where('port_c_cod', $port_c_cod)->find();
       }

        $result['port_code'] = $port_code ? '港口代码重复' : 'SUCCESS';
        $result['port_c_cod'] = $port_c_cod ? '港口名称重复' : 'SUCCESS';

        return json($result);
    }

    public function get_white_cards()
    {
        $args = input();
        $oper = element("oper", $args);
        $id   = element("id", $args);
        $whiteCards = new WhiteCards;

        switch ($oper) {
            case 'add':
                $whiteCards->data($args)->allowField(true)->save();
                break;
            case 'del':
                if ($id){
                    whiteCards::destroy($id);
                }
                break;
            case 'edit':
                if ($id){
                    $whiteCards->allowField(true)->save($args, ['id'=>$id]);
                }
                break;
        }

        // if (element("_search", $args) == 'true') {
        //     $gridSearch = CommonHelper::$gridSearch;
        //     $searchOper = element('searchOper', $args);
        //     $searchString = element('searchString', $args);
        //     $searchField = element('searchField', $args);
        //     $data = DB::name('white_cards')->
        //             where($searchField, element($searchOper, $gridSearch), '%'.$searchString.'%')->
        //             order('id')->paginate(input('rows'))->toArray();
        // } else {
        //     $data = DB::name('white_cards')->order('id')->paginate(input('rows'))->toArray();
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
                     $data = Db::name('white_cards')->
                        order('id DESC')->
                        where($where)->
                        paginate(input('rows'))->toArray();
                     break;
                 
                case 'OR':
                     $data = Db::name('white_cards')->
                                order('id DESC')->
                                whereOr($where)->
                                paginate(input('rows'))->toArray();
                     break;
             } 

            
        } else {
            $data = DB::name('white_cards')->order('id')->paginate(input('rows'))->toArray();
        }
        
        
        $data = CommonHelper::getPages($data);
        
        return json($data);
    }
}





