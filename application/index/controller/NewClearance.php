<?php
namespace app\index\controller;

use app\common\helper\ArrayHelper;
use app\common\LoginController;
use app\index\model\BillHead;
use app\index\model\Cargo;
use app\index\model\CorrelateCompany;
use app\index\model\Country;
use app\index\model\CustomerParams;
use app\index\model\CustomsBroker;
use app\index\model\DataDictionary;
use app\index\model\Hscode;
use app\index\model\Passport;
use app\index\service\CreateBillService;
use think\Db;
use think\Request;

class NewClearance extends LoginController {
	private $msg = '未上传商品';

	function index() {
		$headDDName = BillHead::getDD();
		$headDD = DataDictionary::getByName($headDDName);
		//Cache::store('memcache')->set('name','value');
		$this->assign('user_id', $this->user['id']);
		$this->assign('headDD', $headDD);
		$this->assign('country', Country::all());
		$this->assign('cusParams', CustomerParams::all());
		$this->assign('cusBroker', CustomsBroker::all());
		$this->assign('passport', Passport::all());
		$this->assign('deliverCompany', CorrelateCompany::all(['type' => 1]));
		$this->assign('businessCompany', CorrelateCompany::all(['type' => 2]));
		$this->assign('billHead', new BillHead);
		return $this->fetch('index');
	}

	function init() {

		$cusParams = CustomerParams::all();
		$cusBroker = CustomsBroker::all();
		$data['cusParams'] = $cusParams;
		$data['cusBroker'] = $cusBroker;
		return $data;
	}

	// function transport_save()
	// {
	// 	$billHead = new BillHead();
	// 	$data = input();
	// 	$id = element('id', $data);
	// 	//更新
	// 	if ($id) {
	// 		return $billHead->allowField(true)->save($data, ['id' => $id]);
	// 	}

	// 	$billHead->allowField(true)->save($data);
	// 	return $billHead->id;
	// }

	function upload() {
		$path = input('path');
		if (!$path) {
			return "上传失败";
		}
		$billSer = $this->getServer();
		return json($billSer->upload($path));
	}

	function cargo_sort() {
		$args = input();
		$data = element('data', $args);

		$total_cases = element('total_cases', $args);
		$total_weight = element('total_weight', $args);
		if (!$data) {
			return json($this->msg);
		}
		$billSer = $this->getServer();

		list($cargos, $involveArr) = array_reduce($data, function ($ret, $value) {
			if (isset($value['is_nicety'])) {
				if (!$value['is_nicety']) {
					$value['number'] = '千克' === $value['unit'] ? $value['net_weight'] : bcdiv($value['net_weight'], $value['ratio'] ? $value['ratio'] : 1);
				}
			}
			if ('是' === $value['involve_customs']) {
				$ret[1][] = $value;
			} else {
				$ret[0][] = $value;
			}
			return $ret;
		}, [[], []]);

		foreach ($cargos as $key => $value) {
			$cargos[$key]['sort'] = $key;
		}

		$cargos = $billSer->cargo_sort($cargos, 'hs_code');

		$cargos = ArrayHelper::multisort($cargos, 'sort');

		$cargos = array_merge($involveArr, $cargos);

		//$cargos = $billSer->cargo_sort($data, 'hs_code');

		/*$involveArr = [];
			        if (is_array($cargos)){
			            foreach ($cargos as $k => $v) {
			                #$v['standard'] = '';

			                $v['number'] = '千克' === $v['unit'] ? $v['net_weight'] : bcdiv($v['net_weight'], $v['ratio']);
			                //$involve_customs_num = $v['involve_customs'];
			                if ('是' == $involve_customs_num) {
			                    $involve_customs_num = 1;
			                } else {
			                    $involve_customs_num = 2;
			                }
			                $v['involve_customs_num'] = $involve_customs_num;
			                if ('是' === $v['involve_customs']) {
			                     $involveArr[$k] = $v;
			                     unset($cargos[$k]);
			                     continue;
			                }
			                $cargos[$k] = $v;
			            }
		*/

		//$cargos = $billSer->cargo_sort($cargos, 'involve_customs_num');
		$result['data'] = $cargos;
		$result['total_cases'] = $total_cases;
		$result['total_weight'] = $total_weight;
		return json($result);
	}

	#归类合并array $cargo
	#规则将HSCODE前2码相同的商品合并，HSCODE、品名、单位取件数最多的，
	#如果件数也相同，则将品名按中文拼音排序，取第一个
	function cargo_merge() {
		$args = input();

		$data = element('data', $args);

		$total_cases = element('total_cases', $args);
		$total_weight = element('total_weight', $args);

		if (!$data) {
			return json($this->msg);
		}

		$billSer = $this->getServer();
		$result['data'] = $billSer->cargo_merge($data);
		$result['total_cases'] = $total_cases;
		$result['total_weight'] = $total_weight;
		return json($result);
	}

	private function getServer() {
		return new CreateBillService();
	}

	//保存交通
	function save_transport() {
		$args = input();

		$id = element('id', $args);
		if ($id) {
			$billHead = new BillHead();
			$billHead->allowField(true)->save($args, ['id' => $id]);

			Db::table('cargo')->where('bill_id', '=', $id)->delete();
		} else {
			$args['create_time'] = time();
			//$args['serial_no']   = BillHead::setSerialNo();
			$billHead = new BillHead($args);
			$billHead->allowField(true)->save();

			$id = $billHead->id;
		}

		$billHead->contract_no = BillHead::setContractNo($billHead->serial_no);
		$billHead->save();
		BillHead::setAllclearNo($id);
		return $id;
	}

	//保存商品
	function save_cargo() {
		#return json(input());
		$args = input();
		$type = element('type', $args);
		$bill_id = element('bill_id', $args);
		if (!$bill_id) {
			return false;
		}
		$cargos = element('data', $args);
		$cargo = new Cargo;
		if (is_array($cargos)) {
			foreach ($cargos as $k => $v) {
				$v['bill_id'] = $bill_id;
				if ($type == 2) {
					$v['type'] = 2;
					$v['np'] = $v['price'];
				}
				$cargo->data($v)->isUpdate(false)->allowField(true)->save();
			}
		}
		if ($type = 2) {
			$this->operate($cargo->bill_id);
		}
	}

	protected function operate($bill_id) {
		//不合并
		$count1 = Cargo::where('bill_id', $bill_id)->where('type', 1)->count();
		$count2 = Cargo::where('bill_id', $bill_id)->where('type', 2)->count();

		$count = ($count1 === $count2);

		Cargo::where('bill_id', $bill_id)->where('type', 2)->chunk(100, function ($cargos) use ($count) {

			foreach ($cargos as $cargo) {
				//未合并 或者 involve_customs为是
				if ($count || $cargo->involve_customs == '是') {
					Cargo::where('bill_id', $cargo->bill_id)
						->where('name', "=", $cargo->name)
						->where('hs_code =' . $cargo->hs_code)
						->where('type', 1)
						->update([
							'np' => $cargo->price,
						]);
					continue;
				}

				Cargo::where('bill_id', $cargo->bill_id)
					->where('LEFT(`hs_code`, 2) =' . substr($cargo->hs_code, 0, 2))
					->where('type', 1)
					->update([
						'np' => $cargo->price,
					]);
				//合并前的数据总额
				$sum = Cargo::where('type', '1')
					->where('bill_id', $cargo->bill_id)
					->where('LEFT(`hs_code`, 2) =' . substr($cargo->hs_code, 0, 2))
					->sum('number * np');

				$difference = bcsub(bcmul($cargo->number, $cargo->getData('price'), 100), $sum, 100);

				//echo $sum, PHP_EOL, $cargo->number, PHP_EOL, $cargo->getData('price'), PHP_EOL, bcmul($cargo->number, $cargo->getData('price'), 100), PHP_EOL;
				//echo $difference != "0";
				if ($difference != "0") {
					$same = Cargo::where('type', '1')
						->where('bill_id', $cargo->bill_id)
						->where('LEFT(`hs_code`, 2) =' . substr($cargo->hs_code, 0, 2))
						->find();

					$same->np = $same->np + bcdiv($difference, $same->number, 100);

					$same->save();
				}
			}
		});
	}

	//获取商品
	function get_cargo() {
		$id = input('id');
		if (!$id) {
			return '';
		}
		//TYPE:1是原始数据。2为处理后的数据
		$cargo_or = Cargo::where('bill_id', '=', $id)->where('type', '=', 1)->select();
		$cargo_dis = Cargo::where('bill_id', '=', $id)->where('type', '=', 2)->select();
		$cargo_or = $this->get_cargo_format($cargo_or);
		$data['cargo_or'] = $cargo_or;
		$data['cargo_dis'] = $cargo_dis;
		return json($data);
	}

	//商品数据格式化
	function get_cargo_format($data) {
		if (!is_array($data)) {
			return $data;
		}

		foreach ($data as $k => $v) {
			$hsCode['hs_code'] = $v['hs_code'];
			$hsCode['involve_customs'] = $v['involve_customs'];
			$hsCode['involve_tax'] = $v['involve_tax'];
			$hsCode['name_search'] = $v['name_search'];
			$hsCode['ratio'] = $v['ratio'];
			$hsCode['unit'] = $v['unit'];
			$hsCode['unit2'] = $v['unit2'];
			$data[$k]['hsCode'] = $hsCode;
		}
		return $data;
	}

	//存报关信息
	function save_custom() {
		$args = input();
		$bill_id = element('bill_id', $args);
		if (!$bill_id) {
			return false;
		}
		$billHead = new BillHead();

		$billHead->allowField(true)->save($args, ['id' => $bill_id]);

		$billHead = BillHead::get($bill_id);
		if (1 == $billHead->status) {
			$cargos = Cargo::where('bill_id', '=', $bill_id)->where('type', '=', 2)->select();
			$billSer = $this->getServer();
			$billSer->createHscode($cargos);
		}

		BillHead::setAllclearNo($bill_id);
		return;
	}

	function delete_custom() {
		$id = element('id', input());
		if ($id) {
			Db::table('bill_head')->delete($id);
			Db::table('cargo')->where('bill_id', $id)->delete();
		}
	}

	function get_port_lin() {
		$data = Db::table('port_lin')->
			field('port_c_cod,port_count,port_code')->
			where('port_count', '=', element('port_count', input()))->select();
		return json($data);

	}

	function get_init_passport() {
		$port_count = input('port_count');

		if (!$port_count) {
			return json('');
		}
		$passports = Passport::where('nationality', '=', $port_count)->select();
		if (!count($passports)) {
			$passports = Passport::all();
		}

		$key = array_rand($passports);
		$id = $passports[$key]->id;
		return json($id);
	}

	//查询
	function get_by_id() {
		$id = input('id');
		if (!$id) {
			$this->error('系统参数错误，请稍后再试！');
		}
		$this->index();
		$billHead = BillHead::get($id);
		$this->assign('billHead', $billHead);
		return $this->fetch('index');
	}

	//type : hs_code name
	function getHscode($type, $value) {
		if ('hs_code' == $type) {
			return json(Hscode::where($type, 'like', '%' . $value . '%')->select());
		}
		return json(Hscode::where($type, '=', $value)->select());
	}

	public function serialNo(Request $request) {
		$serialNo = $request->param('serial_no');

		return (int) (null === BillHead::get(['serial_no' => $serialNo]));
	}
}
