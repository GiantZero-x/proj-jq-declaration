<?php
namespace app\index\service;

use think\Request;
use app\index\model\BillHead;

abstract class DigitalChinaService
{
	const KEY = 'BF4960C941774ADFB301E052C505A424';
	const OWNER_ID = '331898008K';

	protected $bodyParameter;

	protected $data = [];

	protected $bill;

	public static $id;

	public function __construct(BillHead $bill)
	{
		$this->bill = $bill;
		
		$this->_initialize();
	}

	// 初始化
    abstract protected function _initialize();

    public static function invoke(Request $request)
	{
		static::$id = $request->param('id');

		return new static(BillHead::get(self::$id));
	}

	public function bodyParameter($refresh = false)
	{
		if (null === $this->bodyParameter || $refresh) {

			$data = $this->data();
			$this->bodyParameter = [
				'notify_type' => $this->className(),
				'notify_id'=> $this->bill->serial_no, //TODO::和报文对应唯一单据号，若有回执报文则用此字段与请求报文关联
				'notify_time' => date('Y-m-d H:i:s'),
				'owner_id'=> self::OWNER_ID,
				'data'=> $data,//报文
				'sign' => $this->sign($data),
			];
		}

		return $this->bodyParameter;
	}

	protected function className()
	{
		return strtoupper(array_pop((explode('\\', get_called_class()))));
	}
	//md5(data原始值 + Key)
	protected function sign($data)
	{
		return  strtoupper(md5($data . self::KEY));
	}

	protected function data()
	{
		return json_encode($this->data, JSON_UNESCAPED_UNICODE);
	}

	public function getBillHead()
	{
		return $this->bill;
	}
}

