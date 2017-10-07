<?php
namespace app\index\model;

use think\Model;
use think\Db;

class Hscode extends Model
{
    public function getUnitObjAttr()
    {
        $std = new \StdClass();

        $find = HscodeUnit::where('hs_code', $this->getData('hs_code'))
            ->whereor('hs_code', 'LIKE', substr($this->getData('hs_code'), 0, 8) . '%')
            ->find();

        $std->unit1_code = $find ? $find->unit1_code : '';
        $std->unit2_code = $find ? $find->unit2_code : '';

        return $std;
    }

    public function elements()
    {
        return $this->hasMany('Element', 'hs_code', 'code');
    }
}