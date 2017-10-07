<?php
namespace app\index\service;
use app\common\helper\ArrayHelper;
use app\common\helper\ExcleHelper;
use app\index\model\CargoTmp;
use app\index\model\CustomerParams;
use app\index\model\DataDictionary;
use app\index\model\Hscode;
use \think\Db;

class CreateBillService {

	//b16 supervise_mode = trade_mode
	private $bill_head = ['B1' => 'serial_no', 'B2' => 'customs_address', 'B3' => 'export_port', 'B4' => 'seal_address',
		'B5' => 'ship_name', 'B6' => 'voyage_no', 'B7' => 'bill_no', 'B8' => 'box_no', 'B9' => 'box_type', 'B10' => 'closing', 'B11' => 'white_card_no', 'B12' => 'car_no', 'B13' => 'driver_tel',
		'B14' => 'total', 'B15' => 'weight', 'B16' => 'supervise_mode', 'B17' => 'customs_no',
		'B18' => 'amount_declared', 'B19' => 'head_remark', 'B20' => 'EdiID'];
	private $ddReader = ['B2', 'B3', 'B9', 'B16', 'B20'];
	//private $cargoAttribute = ['A'=>'name', 'B'=>'case_no', 'C'=>'price', 'D'=>'market_no'];
	private $cargoAttribute = ['A' => 'name', 'B' => 'case_no', 'C' => 'price', 'D' => 'rough_weight',
		'E' => 'net_weight', 'F' => 'number'];

	private $finalAttribute = ['D', 'E', 'F'];

	public function upload($path) {

		$objExcel = ExcleHelper::reader($path);
		$objWorksheet = $objExcel->getActiveSheet();
		$highestRow = $objWorksheet->getHighestRow(); // 取得总行数
		#$highestColumn = $objWorksheet->getHighestColumn(); // 取得总列数
		$bill_head = [];
		$cargo = [];
		foreach ($this->bill_head as $k => $v) {
			$tmpValue = $objWorksheet->getCell($k)->getCalculatedValue();
			$bill_head[$v] = $tmpValue;

			if (in_array($k, $this->ddReader)) {
				$dd = DataDictionary::where('name', '=', $v)->where('key', '=', $tmpValue)->find();
				if ($dd) {
					$bill_head[$v] = $dd['value'];
				}
				////b16 supervise_mode = trade_mode
				if ('B16' == $k) {
					$bill_head['supervise_mode'] = $dd['value'];
				}
			}

		}

		$j = 0;
		for ($row = 28; $row <= $highestRow; $row++) {
			foreach ($this->cargoAttribute as $k => $v) {
				$value = $objWorksheet->getCell($k . $row)->getValue() . "";

				if ($k == 'A' && !$value) {
					break;
				}

				$cargo[$j]['is_nicety'] = 0;
				$cargo[$j]['sort'] = $j;

				$cargo[$j][$v] = $value ? $value : '';

				if ($k == 'A') {

					$hsCode = $this->getHsCodeMessage($cargo[$j][$v]);
					$cargo[$j]['hsCode'] = $hsCode;
					$cargo[$j]['code'] = $hsCode['hs_code'];
					$involve_customs = $hsCode['involve_customs'];
					if ('是' == $involve_customs) {
						$involve_customs = 1;
					} else {
						$involve_customs = 2;
					}
					$cargo[$j]['involve_customs'] = $involve_customs;
				}

				if (in_array($k, $this->finalAttribute)) {
					if ($value) {
						$cargo[$j][$v] = $value;
						$cargo[$j]['is_nicety'] = 1;
					}
				}

				if ('F' == $k && $cargo[$j]['rough_weight'] && $cargo[$j]['net_weight'] && !$value) {
					$cargo[$j][$v] = bcdiv($cargo[$j]['net_weight'], $cargo[$j]['hsCode']['ratio'] ? $cargo[$j]['hsCode']['ratio'] : 1);
					$cargo[$j]['is_nicety'] = 1;
				}
			}
			$j++;
		}

		$result['bill_head'] = $bill_head;

		$result['cargo'] = $cargo;

		//$result['cargo'] = $this->cargo_sort($cargo, 'code');

		$result['cargo'] = $this->cargo_sort($this->cargo_sort($cargo, 'code'), 'involve_customs');

		//$cargo = count($result['cargo'] % 2) ? $result['cargo'] : array_slice($result['cargo'], 0, count($result['cargo']) - 1);

		$avg = $result['bill_head']['weight'] / $result['bill_head']['total'];

		$max = $avg >= 10 ? 4 : 0.4 * $avg;

		$randArr = [];

		foreach ($result['cargo'] as $key => $value) {
			$value['rough_weight'] = (int) ($value['case_no'] * ($avg + ($key % 2 ? -rand_float(0, $max, 1) : rand_float(0, $max, 1))));
			$randArr[] = $value;
		}

		$sum = array_reduce($randArr, function ($ret, $value) {
			return $ret + $value['rough_weight'];
		}, 0);
		$avg = bcsub($result['bill_head']['weight'], $sum) / $result['bill_head']['total'];

		$randArr2 = [];
		foreach ($randArr as $key => $value) {
			$value['rough_weight'] = ($key == count($randArr) - 1) ? bcsub($result['bill_head']['weight'], array_reduce($randArr2, function ($ret, $value) {
				return $ret + $value['rough_weight'];
			}, 0), 2) : $value['rough_weight'] + (int) ($value['case_no'] * $avg);
			$value['net_weight'] = $value['rough_weight'] - $value['case_no'];
			$randArr2[] = $value;
		}

		$result['cargo'] = $randArr2;

		return $result;
	}

	#模拟数据
	function getHsCodeMessage($name) {
		$result = [];

		$def_merchant_code = '';

		$customerParams = CustomerParams::find();
		if ($customerParams) {
			$def_merchant_code = $customerParams['def_merchant_code'];
		}

		$result['name_search'] = $def_merchant_code;
		$result['hs_code'] = '';
		$result['unit'] = '';
		$result['price'] = '';
		$result['unit2'] = '';
		$result['involve_customs'] = '否';
		$result['involve_tax'] = '';
		$result['market_no'] = $def_merchant_code;
		$result['ratio'] = '';

		$hscode = Hscode::where('name', $name)->find();
		if (isset($hscode->id)) {
			$result['hs_code'] = $hscode->hs_code;
			$result['name_search'] = '匹配';
			$result['standard'] = $hscode->standard;
			$result['unit'] = $hscode->unit;
			$result['involve_customs'] = $hscode->involve_customs;
			$result['involve_tax'] = $hscode->involve_tax;
			$result['ratio'] = $hscode->ratio;
			$result['unit2'] = $hscode->unit2;
			$result['price'] = $hscode->price;
			$result['market_no'] = $hscode->market_no ? $hscode->market_no : $def_merchant_code;
		}

		return $result;
	}

	#归类不合并
	function cargo_sort(array $cargo, $element) {
		return ArrayHelper::multisort($cargo, $element);
	}

	#归类合并array $cargo
	#规则将HSCODE前2码相同的商品合并，HSCODE、品名、单位取件数最多的，
	#如果件数也相同，则将品名按中文拼音排序，取第一个
	function cargo_merge($data) {
		$time_sign = md5(time() . mt_rand(0, 1000));

		CargoTmp::destroy(function ($query) {
			$query->where('id', '>', 1);
		});

		$cargoTmp = new CargoTmp();

		if (is_array($data)) {
			foreach ($data as $key => $value) {
				$value['time_sign'] = $time_sign;
				$value['top2'] = substr($value['hs_code'], 0, 2);
				$cargoTmp->data($value)->allowField(true)->isUpdate(false)->save();
			}
		}
		$table = 'cargo_tmp';
		$sql = "SELECT
					`name`,
					AVG(price) AS price,
					market_no,
					hs_code,
					SUM(`number`) AS number,
					unit,
					unit as unit_apply,
					involve_tax,
					involve_customs,
					name_search,
					ratio,
					top2,
					standard,
					SUM(case_no) AS case_no,
					SUM(rough_weight) AS rough_weight,
					SUM(net_weight) AS net_weight
				FROM
					{$table}
				WHERE
					time_sign = ?
					AND involve_customs <> '是'
				GROUP BY
					top2";
		$sql2 = "SELECT
					`name`,
					AVG(price) AS price,
					market_no,
					`number`,
					hs_code,
					unit,
					unit as unit_apply,
					involve_tax,
					involve_customs,
					name_search,
					ratio,
					top2,
					standard,
					SUM(case_no) AS case_no,
					SUM(rough_weight) AS rough_weight,
					SUM(net_weight) AS net_weight
				FROM
					{$table}
				WHERE
					time_sign = ?
					AND involve_customs = '是'
				GROUP BY
					id";
		$problemData = Db::query($sql, [$time_sign]);

		$is_involve_customs_data = Db::query($sql2, [$time_sign]);
		$a = Db::getLastSql();
		$problemData = $this->cargo_merge_format($problemData);
		//$is_involve_customs_data;

		foreach ($problemData as $key => $value) {
			$is_involve_customs_data[] = $value;
		}

		CargoTmp::destroy(['time_sign' => $time_sign]);
		return $is_involve_customs_data;
	}

	function cargo_merge_format($problemData) {
		$result = [];
		if (is_array($problemData)) {
			foreach ($problemData as $k => $v) {
				$sql = "SELECT *,unit as unit_apply FROM cargo_tmp WHERE top2  = ? ORDER BY case_no DESC ,`name` LIMIT 1";
				$trueData = Db::query($sql, [$v['top2']]);
				if ($trueData) {
					$v['name'] = $trueData[0]['name'];

					list($amount, $qty, $weight) = array_values(
						Db::table('cargo_tmp')->where('top2', $v['top2'])
							->where('involve_customs', '否')
							->field('IF(unit = "千克",SUM(price * net_weight), SUM(price * ratio * net_weight))AS amount,SUM(ratio * net_weight) AS qty, SUM(net_weight) AS weight')
							->find()
					);

					$v['price'] = $v['np'] = bcdiv($amount, '千克' === $trueData[0]['unit'] ? $weight : $qty, 2);

					//echo PHP_EOL,$amount, PHP_EOL,'千克' === $trueData[0]['unit'] ? $weight : $qty;
					//echo  PHP_EOL,$v['price'];

					$v['market_no'] = $trueData[0]['market_no'];
					$v['hs_code'] = $trueData[0]['hs_code'];
					$v['unit'] = $trueData[0]['unit'];
					$v['involve_customs'] = $trueData[0]['involve_customs'];
					$v['name_search'] = $trueData[0]['name_search'];
					$v['ratio'] = $trueData[0]['ratio'];
					$v['standard'] = $trueData[0]['standard'];
					//$v['number'] = '';
					$v['unit_apply'] = $trueData[0]['unit_apply'];
					$result[$k] = $v;
				}
			}
		}
		$rand = 0;

		/*$result = array_map(function ($value) use (&$rand) {
			$rand += rand_float();
			$value['price'] = $value['np'] = $rand;
			return $value;
		}, $result);*/
		return $result;
	}

	function createHscode($cargos) {
		if (!$cargos) {
			return false;
		}

		foreach ($cargos as $key => $cargo) {
			$count = Db::table('hscode')->field('COUNT(*)  as total')
				->where('hs_code', $cargo['hs_code'])
				->where('name', $cargo['name'])->find();

			Db::table('hscode')->where('hs_code', $cargo['hs_code'])
				->where('name', $cargo['name'])
				->setField('ratio', $cargo['ratio']);

			if (!$count['total']) {
				$objHscode = new Hscode();
				unset($cargo['id']);
				$objHscode->hs_code = $cargo->hs_code;
				$objHscode->name = $cargo->name;
				$objHscode->unit = $cargo->unit;
				$objHscode->standard = $cargo->standard;
				$objHscode->price = $cargo->price;
				$objHscode->involve_customs = $cargo->involve_customs ? $cargo->involve_customs : '否';
				$objHscode->involve_tax = $cargo->involve_tax ? $cargo->involve_tax : '否';
				$objHscode->market_no = $cargo->market_no;
				$objHscode->ratio = $cargo->ratio;
				$objHscode->remark = '新建报关单系统自动添加';
				$objHscode->isUpdate(false)->save();
				// $objHscode->allowField(true)->save();
				// return Hscode::getLastSql();
				// //$objHscode->data($cargo)->allowField(true)->isUpdate(false)->save();
			}
		}
	}

}