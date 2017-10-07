<?php
namespace app\index\controller;
use think\Controller;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\LabelAlignment;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\SvgWriter;
use app\index\model\BillHead;

require HOME_PATH.'/vendor/autoload.php';


class Test extends Controller
{
	function index()
	{
		$id = request()->param('id');
        $this->view->engine->layout(false);
        $this->assign('billHead', BillHead::get($id, 'cargos'));
        return $this->fetch('index/weituoshu');
		// $A = time();
		// echo $A;
		// $qrcode = new QrCode();
		// $a = $qrcode->setText('A B C')
		// 		->setSize(300)
		// 		->setPadding(10)
		// 		->setErrorCorrection('high')
		// 		->setForegroundColor(['r'=>0, 'g'=>0, 'b'=>0, 'a'=>0])
		// 		->setBackgroundColor(['r'=>255, 'g'=>255, 'b'=>255, 'a'=>0])
		// 		->setLabel('My label')
		// 		->setLabelFontSize(16)
		// 		->save(ROOT_PATH . 'public' . DS .'uploads' . $A);
		// dump($a);
		vendor("phpqrcode.phpqrcode");
		$data = 'http://www.baidu.com';
		// 纠错级别：L、M、Q、H
		$level = 'L';
		// 点的大小：1到10,用于手机端4就可以了
		$size = 4;
		// 下面注释了把二维码图片保存到本地的代码,如果要保存图片,用$fileName替换第二个参数false
		$path = ROOT_PATH . 'public' . DS .'uploads';
		// 生成的文件名
		//$fileName = $path.$size.'.png';
		$a = \QRcode::png($data, $path, $level, $size);
		dump($a);

	}
} 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
