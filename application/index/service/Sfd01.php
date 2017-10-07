<?php
namespace app\index\service;

class Sfd01 extends DigitalChinaService implements \Iterator
{
	// 初始化
    protected function _initialize()
    {
    	$this->_initData();
    }

    protected function _initData()
    {
    	$this->data = [
			'Root' => [
				'FileName' => '1',
				'EDOCData' => '2',
			]
		];
	}

	protected $files = [];

	protected function setFileName()
	{

		if (isset($this->files[$this->index])) {
			$this->data['Root']['FileName'] = $this->files[$this->index];
			return $this;
		}

		list($usec) = explode(' ', microtime());

		$usec = str_pad(intval(1000 * $usec), 2, '0', STR_PAD_LEFT) . $this->index;

		$fileName = [
			self::OWNER_ID,
			(new \DateTime)->format('YmdHis') . $usec,
			$this->bill->serial_no
		];
				
		$this->data['Root']['FileName'] = $this->files[$this->index] = implode('$', $fileName) . '.pdf';
		
		return $this;
		//企业编码$年月日时分秒毫秒$单据号
	}

	public function getFileName()
	{
		return $this->data['Root']['FileName'];
	}

	public function setEDOCData($EDOCData)
	{
		$this->data['Root']['EDOCData'] = $EDOCData;

		return $this;
	}

	protected $pdfs = [
		'caigouqingdan', //0000005
		'weituoshu', //0000008
		'jianhuaguilei', //0000007
		//'shenbaoqingdan',
	];

	protected $pdf;

	protected $index = 0;

	public function rewind()
	{
		$this->index = 0;
	}

	public function current()
	{
		$this->pdf = $this->pdfs[$this->index];
		//return $pdf;
		return $this->setFileName();
	}

	public function key()
	{
		return $this->pdfs[$this->index];
	}

	public function next()
	{
		return $this->index++;
	}

	public function valid()
    {
        return $this->index < count($this->pdfs);
    }
}

