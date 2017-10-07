<?php
namespace app\index\model;
use think\Model;

class Cargo extends Model
{
	
	public function getAmountAttr($value, $data) {
		return bcmul($this->getData('np'), $this->number, 2);
	}
	
	public function getPerQtyAttr($value, $data) {
		return  $this->case_no ? (int) ((int) $this->number / (int) $this->case_no) : 0;
	}

	public function getNumberWeightRatioAttr($value, $data) {
		return  $this->number ? round($this->net_weight / (int) $this->number, 3) : 0;
	}

	public function getMarketNoAttr($value)
	{
		return $value ?: (CustomerParams::get(1)->def_merchant_code);
	}

	public function code()
	{
		return $this->hasOne('Hscode', 'hs_code', 'hs_code');
	}

	protected function getHsCodeArrAttr($value, $data)
	{
		return str_split($data['hs_code']);
	}

	protected function getPriceAttr($value)
	{
		return sprintf("%.2f", $value);
	}

	protected function getUnitApplyAttr()
	{
		return $this->unit;
	}

	protected function getUnit1Attr($value, $data)
	{
		return '035' === $this->code->unit_code ? $data['net_weight'] : $this->number;
	}
	protected function getUnit2Attr($value, $data)
	{
		if (!$this->code->unit2_code) {
			return '';
		}
		return '035' === $this->code->unit2_code ? $data['net_weight'] : $this->number;
	}

	public function __clone()
	{
		foreach ($this->data as $key => $value) {
			$this->data($key);
		}
	}
}