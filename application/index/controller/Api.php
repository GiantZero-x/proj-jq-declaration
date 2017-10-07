<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/8/28
 * Time: 10:55
 */

namespace app\index\controller;

use app\index\model\Element;
use app\index\model\Hscode;
use think\Controller;
use think\Request;

class Api extends Controller
{
    public function hscode(Request $request, $code)
    {
        return json(
            Hscode::with('elements')->whereLike(
                'hs_code|name', "%{$code}%"
            )->field('hs_code AS HSCODE, name AS PRODNAME, standard AS PRODSP, rebate_rate AS TAXRADIO')->paginate($request->param('rows'))
        );
    }

    public function element($code)
    {
        $codes = array_map(function ($len) use ($code) {
            return substr($code, 0, $len);
        }, range(1, strlen($code)));

        return json(Element::where('code', Element::whereIn('code', $codes)->MAX('code'))->order('sort')->column('element'));
    }
}