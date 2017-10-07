<?php
namespace app\common\helper;
use PHPExcel_IOFactory;
use PHPExcel;

class ExcleHelper {

	static function reader($path){
		$file = HOME_PATH.$path;
		$inputFileType = PHPExcel_IOFactory::identify($file);  
		$objReader = PHPExcel_IOFactory::createReader($inputFileType);  
		$objPHPExcel = $objReader->load($file);
		return $objPHPExcel;
	}
}