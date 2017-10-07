<?php
namespace app\index\model;
use app\index\model\CustomsBroker;
use app\index\model\DataDictionary;
use app\index\model\PortLin;
use think\Db;
use think\Model;

class BillHead extends Model {
	protected $type = [
		'create_time' => 'timestamp:Y/m/d',
	];
	/*
		 *export_port:出口口岸
		 *inland_mode:境内运输方式
		 *pack:包装方式
		 *box_type ：柜型
		 *loading_mode:装柜方式
		 *ediID:报关方式
		 *customs_case_type : 报关单类型
		 *customs_address:申报地海关
		 *terms_mode:成交方式
		 *supervise_mode：监管方式
	*/
	static private $dataDictionary = ['export_port', 'inland_mode', 'pack', 'box_type', 'loading_mode', 'ediID', 'customs_case_type', 'customs_address', 'terms_mode', 'supervise_mode'];
	//状态：0为保存; 1为提交(待审核); 2为已审核; 3、已申报; 4、退回;
	public $statusName = ['草稿', '待审核', '已审核', '已申报', '退回'];

	//protected $readonly = ['contract_no','contract_no'];

	static function getDD() {
		return self::$dataDictionary;
	}

	protected function getExportPortStrAttr($export_port, $data) {
		return DataDictionary::getKeyByNameValue('export_port', $data['export_port']);
	}

	protected function getPackStrAttr($value, $data) {
		return DataDictionary::getKeyByNameValue('pack', $data['pack']);

	}

	protected function getUnloadingPortStrAttr($unloading_port, $data) {
		$data = PortLin::where('port_code', '=', $data['unloading_port'])->find();
		return $data['port_c_cod'];
	}

	protected function getStatusStrAttr($status, $data) {
		return $this->statusName[$data['status']];
	}
	//预计运抵指运地时间 创建时间三个月后
	protected function getEstimatedTimeAttr($value, $data) {
		return date('Ymd', $data['create_time']);
		//return date('Ymd', strtotime('+3 months', $data['create_time']));
	}
	//实际进出境日期 创建时间三个月后
	protected function getBorderTimeAttr($value, $data) {
		return date('Ymd', $data['create_time']);
		//return date('Ymd', strtotime('+3 months', $data['create_time']));
	}

	/*protected function getExportPortAttr($export_port, $data)
		{
			return $export_port;
	*/

	protected function getCreateTimeChsAttr($value, $data) {
		return date('Y年m月d日', $data['create_time']);
	}

	protected function getHeadRemarkAttr($value, $data) {
		/*1. 出口口岸是舟山的：ZW(如果装柜方式是拼箱的则是ZW+装柜方式）
			    出口口岸非舟山的：LZ (如果装柜方式是拼箱的则是LZ+装柜方式）
			2. 如果是有归类合并的则为JH，否则没有值
		*/
		$remarks = [
			('2942' === $data['export_port'] ? 'ZW' : 'LZ') . ('1' === $data['loading_mode'] ? '' : $data['loading_mode']),
			'2' === $data['classify_type'] ? 'JH' : '',
			$this->passport->getData('name') . $this->passport->passport_no,
		];
		return implode(' ', $remarks);
	}

	/*protected function getAimCountryStrAttr()
		{
			return DataDictionary::getKeyByNameValue('export_port', $data['export_port']);
	*/

	// 验证规则
	protected $rule = [
		'type' => 'require',
		'username' => 'require',
		'telephone' => 'require',
	];

	//设置合同号
	static function setContractNo($serial_no) {
		return str_pad(substr($serial_no, 5), 5, '0', STR_PAD_LEFT);
	}

	//设置编号
	static function setSerialNo() {
		$top3code = 185;
		$sql = "SELECT
					LPAD((RIGHT (MAX(serial_no), 3) + 1), 3, 0) AS day_water
				FROM
					bill_head
				WHERE
					serial_no LIKE CONCAT(
						{$top3code},
						DATE_FORMAT(NOW(), '%m%d'),
						'%'
					)";
		$data = Db::query($sql);
		$dayWater = element('day_water', $data[0]);
		$dayWater = $dayWater ? $dayWater : '001';
		return $top3code . date("md", time()) . $dayWater;
	}

	protected function getSuperviseModeStrAttr($value, $data) {
		return DataDictionary::getKeyByNameValue('supervise_mode', $data['supervise_mode']);
	}

	protected function getMarketsAttr($value, $data) {
		$a4 = 12;
		$cargos = array_reduce($this->beforeCargos, function ($ret, $cargo) {
			$ret[$cargo->market_no][] = $cargo;
			return $ret;
		}, []);
		return array_reduce($cargos, function ($ret, $cargo) use ($a4) {
			return array_merge($ret, array_map(function ($value) use ($a4) {
				if (($count = count($value)) === $a4) {
					return $value;
				}
				return $value + array_fill($count, $a4 - $count, clone reset($value)); //clone 返回data空对象
			}, array_chunk($cargo, $a4)));
		}, []);
	}

	protected function getSimpleListAttr($value, $data) {
		$a4 = 28;

		//$cargos = $this->cargos;
		return array_map(function ($value) use ($a4) {
			//dump($value);
			if (($count = count($value)) === $a4) {
				return $value;
			}
			return $value + array_fill($count, $a4 - $count, clone reset($value)); //clone 返回data空对象
		}, array_chunk($this->beforeCargos, $a4));
	}

	protected function getSimpleCategoryAttr($value, $data) {
		$a4 = 18;

		$cargos = collection($this->beforeCargos)->append(['amount'])->toArray();

		//dump($cargos);
		list($involve, $order) = array_reduce($cargos, function ($ret, $value) {
			if ($value['involve_customs'] === '是') {
				$ret[0][] = $value;
			} else {
				$ret[1][] = $value;
			}
			return $ret;
		}, [[], []]);

		$amounts = array_map(function ($value) {
			return $value['amount'];
		}, $order);

		array_multisort($amounts, SORT_DESC, $order);

		$cargos = array_merge($involve, $order);

		return array_map(function ($value) use ($a4) {
			//dump($value);
			if (($count = count($value)) === $a4) {
				return $value;
			}
			return $value + array_fill($count, $a4 - $count, []); //clone 返回data空对象
		}, array_chunk($cargos, $a4));
	}

	protected function getDeclarationAttr($value, $data) {
		$a4 = 20;

		return array_map(function ($value) use ($a4) {
			//dump($value);
			if (($count = count($value)) === $a4) {
				return $value;
			}
			return $value + array_fill($count, $a4 - $count, clone reset($value)); //clone 返回data空对象
		}, array_chunk($this->cargos, $a4));
	}

	protected function getPackingListAttr($value, $data) {
		$a4 = 20;

		return array_map(function ($value) use ($a4) {
			//dump($value);
			if (($count = count($value)) === $a4) {
				return $value;
			}
			return $value + array_fill($count, $a4 - $count, clone reset($value)); //clone 返回data空对象
		}, array_chunk($this->cargos, $a4));
	}

	protected function getInvoiceAttr($value, $data) {
		$a4 = 20;

		return array_map(function ($value) use ($a4) {
			//dump($value);
			if (($count = count($value)) === $a4) {
				return $value;
			}
			return $value + array_fill($count, $a4 - $count, clone reset($value)); //clone 返回data空对象
		}, array_chunk($this->cargos, $a4));
	}

	protected function getContractAttr($value, $data) {
		$a4 = 13;

		return array_map(function ($value) use ($a4) {
			//dump($value);
			if (($count = count($value)) === $a4) {
				return $value;
			}
			return $value + array_fill($count, $a4 - $count, clone reset($value)); //clone 返回data空对象
		}, array_chunk($this->cargos, $a4));
	}

	protected function getCurrencyAttr($value) {
		$currencys = [502 => 'USD'];
		return isset($currencys[$value]) ? $currencys[$value] : 'USD';
	}
	protected function getCurrencySymbolAttr($value) {
		$currencys = [502 => '$'];
		return isset($currencys[$value]) ? $currencys[$value] : '$';
	}

	protected function getCurrencyChsAttr($value) {
		$currencys = [502 => '美元'];
		return isset($currencys[$value]) ? $currencys[$value] : '美元';
	}

	public function getSumCargosAttr() {
		return array_reduce($this->cargos, function ($stdClass, $model) {
			$stdClass->case_no += (int) $model->case_no;
			$stdClass->net_weight += (float) $model->net_weight;
			$stdClass->rough_weight += (float) $model->rough_weight;
			$stdClass->total += (float) $model->amount;
			$stdClass->bulk += (float) $model->bulk;
			return $stdClass;
		}, new \StdClass());
	}

    /*放行号的取号规则是
    1－4位：报关海关，5－6：01出口，7－12：年月日，13－15：报关行代码，最后3位是编号，
    年月日之前是从编号中取，现在改为取导入单据当天的日期。*/

    //20170819 以下规则作废,更改为上面的 hm
	//185 0510 002
	//                     2921             01       0503                    08K           004
	//放行号:生成（1－4位：报关海关，5－6：01出口，7－12：年月日，13－15：报关行代码，最后3位是编号）


    static function setAllclearNo($id) {
		$billHead = self::get($id);
		if (!$billHead) {
			return;
		}

		$allclear_no = '';
		$customs_three_code = '';
		$customs_address = $billHead['customs_address'];
		$serial_no = $billHead['serial_no'];
		$export = '01';
		$customs_broker_id = $billHead['customs_broker_id'];
		if ($customs_address && $serial_no && $customs_broker_id) {
			$customsBroker = CustomsBroker::get($customs_broker_id);
			if ($customsBroker) {
				$customs_three_code = $customsBroker->customs_three_code;
			}
			//$ymd = substr($billHead['create_time'], 2, 2) . substr($serial_no, 3, 4);
			$ymd = date('ymd');
            $flow_no = substr($serial_no, 7);
			$allclear_no = $customs_address . $export . $ymd . $customs_three_code . $flow_no;
		}

		$billHead->allclear_no = $allclear_no;
		$billHead->save();
	}

	public function user() {
		return $this->belongsTo('User', 'user_id');
	}

	public function deliver() {
		return $this->belongsTo('CorrelateCompany', 'correlate_company_id_deliver');
	}

	public function business() {
		return $this->belongsTo('CorrelateCompany', 'correlate_company_id_business');
	}

	public function country() {
		return $this->belongsTo('Country', 'aim_country', 'country_co');
	}

	public function cargos() {
		return $this->hasMany('Cargo', 'bill_id')->where('type', 2)->order('hs_code')->with('code');
	}

	public function beforeCargos() {
		return $this->hasMany('Cargo', 'bill_id')->where('type', 1)->order('hs_code')->with('code');
	}

	public function broker() {
		return $this->belongsTo('CustomsBroker', 'customs_broker_id');
	}

	public function passport() {
		return $this->belongsTo('Passport', 'passport_id');
	}

	public function whitecard() {
		return $this->hasOne('WhiteCards', 'white_card', 'white_card_no');
	}
}
