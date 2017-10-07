<?php
namespace app\index\service;

class Tcs01 extends DigitalChinaService
{
	// 初始化
    protected function _initialize()
    {

    	$this->_initData();

    	$this->ediID();

    	$this->setBody();//setbody
    	
    	$this->setContainer([])
    	  	 ->setDocuments([])
    	  	 ->setText([]);
    }


    protected function _initData()
    {
    	$this->data = [
			'Root' => [
				'报关单表头' => [
					"申报单位代码" => $this->bill->broker->customs_code,//"322698000X", 必填
					"申报单位名称" => $this->bill->broker->name,//太仓XXX报关行 必填
					"批准文号" => '',
					"提运单号" => $this->bill->allclear_no,
					"合同协议号" => '',
					"录入单位代码" => '092332634',//71321888X 必填
					"录入单位名称" => $this->bill->broker->name,//太仓XXX报关行 必填
					"申报地海关" => $this->bill->customs_address,//2327 必填
					"征免性质" => 101,
					"数据来源" => '',
					"报关转关关系标志" => $this->bill->ediID,//必填
					"装货港" => $this->bill->export_port,//,111,//必填
					"境内目的地" => 33189,//必填
					"报关标志" => 3,//必填
					"海关编号" => '',
					"报关单类型" => $this->bill->customs_case_type,//0,//必填
					"运费币制" => '',
					"运费标记" => '',					
					"运费率" => '',
					"毛重" => $this->bill->sum_cargos->rough_weight,//422,//必填
					"进出日期" => '',
					"进出口标志" => "E",//必填
					"进出口岸" => $this->bill->export_port,//2225,//必填
					"录入员姓名" => $this->bill->user->customs_name,//"中外运",//必填
					"征税比例" => '',
					"保险费币制" => '',
					"保险费标记" => '',
					"保险费率" => '',
					"许可证编号" => '',
					"备案号" => '',
					"净重" => $this->bill->sum_cargos->net_weight,//256,//必填
					"备注" => $this->bill->head_remark,
					"杂费币制" => '',
					"杂费标志" => '',
					"杂费率" => '',
					"货主单位代码" => $this->bill->business->customs_code,//"320524017X",//必填
					"货主单位名称" => $this->bill->business->name,//"博世XXX(苏州)有限公司",//必填
					"件数" => $this->bill->total_cases,//3,//必填										
					"申报人标识" => '',
					"结汇方式" => '',
					"纳税单位" => '',					
					"操作时间" => '',
					"预录入编号" => '',
					"风险评估参数" => '',
					"报关单统一编号" => '',
					"通关申请单号" => '',
					"经营单位编号" => $this->bill->deliver->customs_code,//"320524017X",//必填
					"贸易国别" => $this->bill->aim_country,//111,//必填
					"监管方式" => $this->bill->supervise_mode,//"0110",//必填
					"经营单位名称" => $this->bill->deliver->name,//必填
					"运输方式" => 2,//必填
					"运输工具名称" => '',
					"成交方式" => $this->bill->terms_mode,//3,//必填
					"申报备注" => '',
					"录入员IC卡号" => '',
					"包装种类" => $this->bill->pack,//5,//必填
					"担保验放标志" => '',
					"备案清单类型" => '',
					"申报单位统一编码" => '',
					"录入单位统一编码" => '',
					"货主单位统一编码" => '',
					"经营单位统一编码" => '',
					"承诺事项" => 101,//必填
					"外方贸易国别" => $this->bill->aim_country,//必填
				],
				"报关单表体" => [
					"表体" => []
				],
				"集装箱信息" => [
					"集装箱" => [
						'集装箱号' => $this->bill->box_no,
						"集装箱规格" => $this->bill->box_type,
						"集装箱自重" => ''
					]
				],
				// "随附单证" => [
				// 	"单证信息" => []
				// ],
				
				"自由文本信息" => [
					"监管仓号" => "",
					"货场代码" => "",
					"报关员联系方式" => "",
					"报关员号" => "",
					"关联报关单号" => "",
					"关联备案号" => "",
					"航次号" => ""
				],
				'转关单表头' => [
					'转关单统一编号' => '',
					'载货清单号' => '',
					'转关申报单号'=>  '',   		
					'境内运输方式' => $this->bill->inland_mode,//
					'境内运输工具编号' => $this->bill->white_card_no,
					'境内运输工具名称' => $this->bill->car_no,    		
					'境内运输工具航次' => '',	
					"承运单位名称"=> isset($this->bill->whitecard->name) ? $this->bill->whitecard->name : '',
					"承运单位组织机构代码"=> isset($this->bill->whitecard->identifier) ? $this->bill->whitecard->identifier : '',
					'转关类型' =>  '', 
					"是否启用电子关锁标志"=> "N",
					"预计运抵指运地时间"=> $this->bill->estimated_time,
					'备注' => '',
					'转关单类型' => '',
					'转关申报单位代码' => '',	
				],
				'转关单表体' => [
					'提单号' => $this->bill->bill_no,
					"实际进出境日期"=> $this->bill->border_time,
					'进出境运输工具编号'=> '',
					'进出境运输工具名称'=> $this->bill->ship_name,
					'进出境运输方式'=> 2,
					'进出境运输工具航次'=> $this->bill->voyage_no,
				],
				'提运单集装箱' => [
					"集装箱" => [
						[
							"集装箱号" => $this->bill->box_no,
							"集装箱序号" => 1,
							"集装箱规格" => $this->bill->box_type,
							"电子关锁号" => "",
							"境内运输工具名称" => $this->bill->white_card_no,
							"工具实际重量" => ""
						]
					]
				],
				'提运单集装箱商品装配' => [
					"商品信息"=> [
						[
							"集装箱号"=> $this->bill->box_no,
							"商品件数"=> $this->bill->total_cases,
							"商品重量"=> "",
							"商品序号"=> 1
						]
					]
				],
				'随附单据' => [
					'单据信息'=> []
				],
			]
		];

	}

	protected function ediID()
	{
		if ($this->bill->ediID == 0) {
			unset($this->data['Root']['转关单表头']);
			unset($this->data['Root']['转关单表体']);
			unset($this->data['Root']['提运单集装箱']);
			unset($this->data['Root']['提运单集装箱商品装配']);
			$this->data['Root']['报关单表头']['报关标志'] = 1;
		}
	}
	public function setHeader(array $arr)
	{
		$this->data['Root']['报关单表头'] = array_merge($this->data['Root']['报关单表头'], $arr);
		return $this;
	}

	public function setBody()
	{
		foreach ($this->bill->cargos as $index => $cargo) {
			$this->data['Root']['报关单表体']['表体'][] = [
				"归类标志" => "",
				"商品编号" => $cargo->hs_code,//必填
				"备案序号" => '',
				"申报单价" => $cargo->price,//必填
				"申报总价" => $cargo->amount,//必填
				"征减免税方式" => 1,//必填
				"货号" => "",
				"版本号" => "",
				"申报计量单位与法定单位比例因子" => "",
				"第一法定数量" => $cargo->unit1,//必填
				"第一计量单位" => $cargo->code->unit_obj->unit1_code,//必填
				"申报计量单位" => $cargo->code->unit_obj->unit1_code,//必填
				"商品规格型号" => $cargo->code->standard,//必填
				"商品名称" => $cargo->code->name,//必填
				"商品序号" => $index + 1,//必填
				"申报数量" => $cargo->number,//必填
				"原产地" => $this->bill->aim_country,//必填 因为接口原因此处跟最终目的国的值对调
				"第二计量单位" => $cargo->code->unit_obj->unit2_code,
				"第二法定数量" => $cargo->unit2,
				"成交币制" => $this->bill->getData('currency'),//必填
				"用途生产厂家" => "01",
				"工缴费" => "",
				"最终目的国" => 142,//必填 因为接口原因此处跟原产地的值对调
			];
		}
		return $this;
	}

	public function setContainer(array $arr)
	{
		$this->data['Root']['集装箱信息']['集装箱'] = $arr + $this->data['Root']['集装箱信息']['集装箱'];
		return $this;
	}

	public function setDocuments(array $arr)
	{
		//单证信息缺少
		// $arr = [
		// 	"单证代码" => "U",//必填
		// 	"单证编号" => "201612080001 =>1"//必填
		// ];
		// $this->data['Root']['随附单证']['单证信息'] = $arr + $this->data['Root']['随附单证']['单证信息'];
		return $this;
	}

	public function setDocumentInformation($arr)
	{
		$type = [
			'caigouqingdan' => '00000005',
			'weituoshu' => '00000008',
			'jianhuaguilei' => '00000007',
		];
		foreach ($arr as $key => $value) {
			$this->data['Root']['随附单据']['单据信息'][] = [
				"随附单据类别"=> $type[$key],
				"随附单据格式类型"=> "US",
				"操作说明"=> "",
				"随附单据文件企业名"=> $value->getFileName()
			];
		}
	}

	public function setText(array $arr)
	{
		$this->data['Root']['自由文本信息'] = $arr + $this->data['Root']['自由文本信息'];
		return $this;
	}

}

