<?php
namespace app\index\controller;

use think\Config;
use think\Request;
use think\Image;
use app\index\model\BillHead;
use app\index\model\CustomerParams;
use app\common\LoginController;

use League\Flysystem\Adapter\Local;

class Pdf extends LoginController
{
    protected $ext = 'pdf';
    protected $uploadsPath;
    protected $pdf;
    protected $types;
    protected $id;
    protected $list = [
        'baoguandan',
        'weituoshu',
        'shenbaoqingdan',
        'caigouqingdan',
        'lianwangqingdan',
        'fapiao',
        'zhuangxiangdan',
        'hetong',
        'ganghang',
        'jianhuaqingdan',
        'jianhuaguilei',
    ];

    public function _initialize()
    {
        $this->uploadspath = Config::get('UPLOAD_PATH');

        $this->type = $this->request->param('type');
        
        $this->view->engine->layout(false);
        
    }

    public function show($id)
    {
        if (!in_array($this->type, $this->list)) {
            return 0;
        }
        $this->id = $id;

        $this->assign('billHead', BillHead::get($id, 'cargos'));
        $this->assign('customerparams', CustomerParams::get(1));
        return json($this->createPdf($this->type));       

    }

    protected function createPdf($type)
    {
        //TODO::图片
        $pdfName = $type . '_' . $this->id . '.' . $this->ext;

        if ('shenbaoqingdan' === $type) {
            $this()->orientation('Landscape');
        }
        
        $this()->loadHTML($this->fetch('index/' . $type))
               ->pageSize('A4')
               ->encoding('UTF-8')
               ->disableSmartShrinking()
               ->save($pdfName, new \League\Flysystem\Adapter\Local('uploads'), true);
        
        return  HTTP_HOST . $this->uploadspath . DS .$pdfName;
    }

    protected function __invoke()
    {
        if (null === $this->pdf) {
            $this->pdf = new \CanGelis\PDF\PDF('/usr/local/wkhtmltox/bin/wkhtmltopdf');
        }
        return $this->pdf;
    }

    public function excel($id)
    {
        $billHead = BillHead::get($id, 'cargos');

        header("Content-type:application/vnd.ms-excel");  //设置内容类型
        header("Content-Disposition:attachment;filename=" . $billHead->serial_no . ".xls");  //文件下载

        $headers = [
            '商户'=>'market_no' , 
            '商品名称'=>'name' ,
            '商品HS编码'=>'hs_code'  ,
            '货号/唛头'=>'',
            '规格型号'=>'',
            '生产厂商'=>'',
            '品牌'=>'',
            '交易数量'=>'number',
            '单位'=>'unit',
            '包装数量'=>'case_no',
            '包装单位'=>'',
            '币种'=>'',
            '货值'=>'',
            '重量(KGS)'=>'net_weight'
        ];

        foreach ($headers as $header=>$field) {
            echo  iconv('utf-8', 'gbk', $header) . "\t";
        }
        echo "\n";

        foreach ($billHead->cargos as $cargo) {
            
            foreach ($headers as $field) {
                if (!$field) {
                    echo "\t";
                    continue;
                }
                echo  iconv('utf-8', 'gbk', $cargo->getData($field)) . "\t";
            }
            echo "\n";
        }
        die;
    }
    /**
     * 功能：导出操作(注：暂时只做导出Excel文件,注间PhpExcel函数用法，一定要在之前加上'\')
     * @return xls文件
     * 2014-12-18@youge
     */
    public function export_excel($id) {
        $billHead = BillHead::get($id, 'cargos');
        $inputFileName = '../public/excel_temp/tempt.xls';

        /** Load $inputFileName to a PHPExcel Object * */
        $this->tpl = \PHPExcel_IOFactory::load($inputFileName);
        if(!isset($this->objPHPExcel)) $this->objPHPExcel = clone $this->tpl;
        //报关单
        $this->declaration($billHead,$this->objPHPExcel,0);
        $this->invoice($billHead,$this->objPHPExcel,1);
        $this->packing_list($billHead,$this->objPHPExcel,2);
        $this->contract($billHead,$this->objPHPExcel,3);
        $this->ganghang($billHead,$this->objPHPExcel,4);
        $this->weituoshu($billHead,$this->objPHPExcel,5);
        $this->simple_list($billHead,$this->objPHPExcel,6);
        $this->simple_category($billHead,$this->objPHPExcel,7);        
        $this->cargos($billHead,$this->objPHPExcel,8);
        $this->markets($billHead,$this->objPHPExcel,9);
        // Redirect output to a client’s web browser (Excel5)
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $billHead->serial_no . '.xls"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');
        // If you're serving to IE over SSL, then the following may be needed
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');            // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');   // always modified
        header('Cache-Control: cache, must-revalidate');             // HTTP/1.1
        header('Pragma: public');                                    // HTTP/1.0
        $objWriter = \PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }

    /**
     * 报关单
     */
    protected function declaration($billHead, $objPHPExcel,$sheet_num) {
        $cargoss = $billHead->declaration;
        $baoguandan = $objPHPExcel->getSheet($sheet_num);
        $baoguandan->setCellValue('C4', $billHead->export_port_str);
        $baoguandan->setCellValue('E4', $billHead->serial_no);
        $baoguandan->setCellValue('I4', $billHead->white_card_no);
        $baoguandan->setCellValue('I5', $billHead->car_no);
        $baoguandan->setCellValue('B6', $billHead->customer);
        $baoguandan->setCellValue('J6', $billHead->closing);
        $baoguandan->setCellValue('D7', $billHead->country->country_na);
        $baoguandan->setCellValue('G7', $billHead->unloading_port_str);
        $baoguandan->setCellValue('J7', $billHead->create_time);
        $baoguandan->setCellValue('D8', $billHead->bill_no);
        $baoguandan->setCellValue('J8', $billHead->head_remark);
        $baoguandan->setCellValue('D9', $billHead->ship_name);
        $baoguandan->setCellValue('G9', $billHead->voyage_no);
        $baoguandan->setCellValue('J9', $billHead->box_type);
        $baoguandan->setCellValue('D10', $billHead->box_no);
        $baoguandan->setCellValue('H10', $billHead->allclear_no);
        $baoguandan->setCellValue('D11', $billHead->deliver->name);
        $baoguandan->setCellValue('G11', $billHead->deliver->customs_code);
        $baoguandan->setCellValue('J11', $billHead->sum_cargos->bulk);
        $baoguandan->setCellValue('C12', $billHead->sum_cargos->case_no);
        $baoguandan->setCellValue('F12', $billHead->sum_cargos->rough_weight);
        $baoguandan->setCellValue('I12', $billHead->sum_cargos->net_weight);
        $a = 0;        
        $startrow = 0;
        $HighestRow  = $baoguandan->getHighestRow();
        $HighestColumn  = $baoguandan->getHighestColumn();
        foreach ($cargoss as $k =>$cargos) {
            for ($y = 1; $y <= $HighestRow; $y++) {//行数是以第1行开始
                for ($x = 'A'; $x <= $HighestColumn; $x++) {//列数是以A列开始
                    $txt = trim($baoguandan->getCell($x . $y)->getValue());
                    if ($txt && $txt{0} == '#')
                        $txt = isset($ar[$i]) ? $ar[$i++] : '';
                    $h = $y + $startrow;
                    $baoguandan->getCell("$x$h")->setValue($txt);
                    $Width = $baoguandan->getColumnDimension($x)->getWidth();
                    $Height = $baoguandan->getRowDimension($y)->getRowHeight();
                    $baoguandan->getColumnDimension("$x")->setWidth($Width);
                    $baoguandan->getRowDimension("$h")->setRowHeight($Height);
                    $baoguandan->duplicateStyle($baoguandan->getStyle("$x$y"), "$x$h");
                }
            }
            foreach ($baoguandan->getMergeCells() as $merge) {
                $merge = preg_replace_callback('/\d+/', function ($matches)use($startrow) {
                    return $matches[0] + $startrow;
                }, $merge);
                $baoguandan->mergeCells($merge);
            }
            $startrow += $HighestRow + 1; //多加3行便于裁剪
        $sum_amount=0;
        $sum_net_weight=0;
            foreach ($cargos as $i=>$cargo) {
                $row=$k*35+$i+14; //第X行开始插入
                if(empty($cargo['name'])){
                    $baoguandan->setCellValue('A' . ($row), ++$a);
                    $baoguandan->setCellValue('B' . ($row), "");
                    $baoguandan->setCellValue('C' . ($row), "");
                    $baoguandan->setCellValue('E' . ($row), "");
                    $baoguandan->setCellValue('F' . ($row), "");
                    $baoguandan->setCellValue('G' . ($row), "");
                    $baoguandan->setCellValue('H' . ($row), "");
                    $baoguandan->setCellValue('I' . ($row), "");
                    $baoguandan->setCellValue('J' . ($row), "");
                    $baoguandan->setCellValue('K' . ($row), "");
                } else {
                    $baoguandan->setCellValue('A' . ($row), ++$a);
                    $baoguandan->setCellValue('B' . ($row), $cargo['hs_code']);
                    $baoguandan->setCellValue('C' . ($row), $cargo['name']);
                    $baoguandan->setCellValue('E' . ($row), $cargo['case_no']);
                    $baoguandan->setCellValue('F' . ($row), $cargo['net_weight']);
                    $baoguandan->setCellValue('G' . ($row), $cargo['unit1']);
                    $baoguandan->setCellValue('H' . ($row), $cargo['unit']);
                    $baoguandan->setCellValue('I' . ($row), $billHead->currency_symbol . $cargo['price']);
                    $baoguandan->setCellValue('J' . ($row), $billHead->currency_symbol . $cargo['amount']);
                    $baoguandan->setCellValue('K' . ($row), $cargo['number_weight_ratio']);
                $sum_amount = $sum_amount + $cargo['amount'];
                $sum_net_weight = $sum_net_weight + $cargo['net_weight'];
            }
        }
            $baoguandan->setCellValue('G'.($k*35+34), $sum_net_weight);
            $baoguandan->setCellValue('J'.($k*35+34), $billHead->currency_symbol . $sum_amount);
        }
        unset($sum_amount);
        unset($sum_net_weight);
        return $objPHPExcel;
    }

    /**
     * 发票
     */
    protected function invoice($billHead, $objPHPExcel,$sheet_num) {
        $cargos = $billHead->invoice;
        $fapiao = $objPHPExcel->getSheet($sheet_num);
        $fapiao->setCellValue('A1', $billHead->deliver->name);
        $fapiao->setCellValue('I7', $billHead->contract_no);
        $fapiao->setCellValue('I9', $billHead->create_time);
        $fapiao->setCellValue('D12', $billHead->export_port_str);
        $fapiao->setCellValue('G12', $billHead->country->country_na);
        $fapiao->setCellValue('I12', $billHead->unloading_port_str);
        
        $a = 0;        
        $startrow = 0;
        $HighestRow  = $fapiao->getHighestRow();
        $HighestColumn  = $fapiao->getHighestColumn();
        foreach ($cargos as $k =>$cargos) {
            for ($y = 1; $y <= $HighestRow; $y++) {//行数是以第1行开始
                for ($x = 'A'; $x <= $HighestColumn; $x++) {//列数是以A列开始
                    $txt = trim($fapiao->getCell($x . $y)->getValue());
                    if ($txt && $txt{0} == '#')
                        $txt = isset($ar[$i]) ? $ar[$i++] : '';
                    $h = $y + $startrow;
                    $fapiao->getCell("$x$h")->setValue($txt);
                    $Width = $fapiao->getColumnDimension($x)->getWidth();
                    $Height = $fapiao->getRowDimension($y)->getRowHeight();
                    $fapiao->getColumnDimension("$x")->setWidth($Width);
                    $fapiao->getRowDimension("$h")->setRowHeight($Height);
                    $fapiao->duplicateStyle($fapiao->getStyle("$x$y"), "$x$h");
                }
            }
            foreach ($fapiao->getMergeCells() as $merge) {
                $merge = preg_replace_callback('/\d+/', function ($matches)use($startrow) {
                    return $matches[0] + $startrow;
                }, $merge);
                $fapiao->mergeCells($merge);
            }
            $startrow += $HighestRow + 1; //多加3行便于裁剪
        $sum_amount=0;
            foreach ($cargos as $i=>$cargo) {
                $row = $k * 38 + $i + 17; //第X行开始插入
                if(empty($cargo['name'])){
                    $fapiao->setCellValue('A' . $row, '');
                    $fapiao->setCellValue('B' . $row, "");
                    $fapiao->setCellValue('C' . $row, "");
                    $fapiao->setCellValue('F' . $row, "");
                    $fapiao->setCellValue('G' . $row, "");
                    $fapiao->setCellValue('I' . $row, "");
                    $fapiao->setCellValue('J' . $row, "");
                } else {                   
                $fapiao->setCellValue('A' . $row, $i == 0 ? 'N/M': '');
                    $fapiao->setCellValue('B' . $row, ++$a);
                $fapiao->setCellValue('C' . $row, $cargo['name']);
                $fapiao->setCellValue('F' . $row, $cargo['unit1']);
                $fapiao->setCellValue('G' . $row, $cargo['unit']);
                $fapiao->setCellValue('I' . $row, $billHead->currency_symbol . $cargo['price']);
                $fapiao->setCellValue('J' . $row, $billHead->currency_symbol . $cargo['amount']);
                $sum_amount = $sum_amount + $cargo['amount'];
            }
        }
            $fapiao->setCellValue('J'.($k*38+37), $sum_amount);
        }
        unset($sum_amount);
        return $objPHPExcel;
    }

    /**
     * 装箱单
     * @param type $billHead
     * @param type $objPHPExcel
     * @return type
     */
    protected function packing_list($billHead, $objPHPExcel,$sheet_num) {
        $cargos = $billHead->packing_list;
        $zhuangxiangdan = $objPHPExcel->getSheet($sheet_num);
        $zhuangxiangdan->setCellValue('A1', $billHead->deliver->name);
        $zhuangxiangdan->setCellValue('J4', $billHead->serial_no);
        $zhuangxiangdan->setCellValue('J7', $billHead->contract_no);
        $zhuangxiangdan->setCellValue('D9', $billHead->box_no);
        $zhuangxiangdan->setCellValue('I9', $billHead->create_time);
        $sum_case_no = 0;
        $sum_rough_weight = 0;
        $sum_net_weight = 0;
        $a = 0;        
        $startrow = 0;
        $HighestRow  = $zhuangxiangdan->getHighestRow();
        $HighestColumn  = $zhuangxiangdan->getHighestColumn();
        if($billHead->box_type == "L"){
            $cbm=68;
        }elseif($billHead->box_type == "S"){
            $cbm=28;
        }else{
            $cbm="";
        }
        foreach ($cargos as $k =>$cargos) {
            for ($y = 1; $y <= $HighestRow; $y++) {//行数是以第1行开始
                for ($x = 'A'; $x <= $HighestColumn; $x++) {//列数是以A列开始
                    $txt = trim($zhuangxiangdan->getCell($x . $y)->getValue());
                    if ($txt && $txt{0} == '#')
                        $txt = isset($ar[$i]) ? $ar[$i++] : '';
                    $h = $y + $startrow;
                    $zhuangxiangdan->getCell("$x$h")->setValue($txt);
                    $Width = $zhuangxiangdan->getColumnDimension($x)->getWidth();
                    $Height = $zhuangxiangdan->getRowDimension($y)->getRowHeight();
                    $zhuangxiangdan->getColumnDimension("$x")->setWidth($Width);
                    $zhuangxiangdan->getRowDimension("$h")->setRowHeight($Height);
                    $zhuangxiangdan->duplicateStyle($zhuangxiangdan->getStyle("$x$y"), "$x$h");
                }
            }
            foreach ($zhuangxiangdan->getMergeCells() as $merge) {
                $merge = preg_replace_callback('/\d+/', function ($matches)use($startrow) {
                    return $matches[0] + $startrow;
                }, $merge);
                $zhuangxiangdan->mergeCells($merge);
            }
            $startrow += $HighestRow + 1; //多加3行便于裁剪
            foreach ($cargos as $i => $cargo) {
                $row = $k * 43 + $i + 13; //第X行开始插入
                if (empty($cargo['name'])) {
                    $zhuangxiangdan->setCellValue('A' . $row, '');
                    $zhuangxiangdan->setCellValue('B' . $row, '');
                    $zhuangxiangdan->setCellValue('C' . $row, '');
                    $zhuangxiangdan->setCellValue('E' . $row, '');
                    $zhuangxiangdan->setCellValue('F' . $row, '');
                    $zhuangxiangdan->setCellValue('G' . $row, '');
                    $zhuangxiangdan->setCellValue('H' . $row, '');
                    $zhuangxiangdan->setCellValue('I' . $row, '');
                    $zhuangxiangdan->setCellValue('J' . $row, '');
                } else {
                    $zhuangxiangdan->setCellValue('A' . $row, $i == 0 ? 'N/M' : "");
                    $zhuangxiangdan->setCellValue('B' . $row, ++$a);
                    $zhuangxiangdan->setCellValue('C' . $row, $cargo['name']);
                    $zhuangxiangdan->setCellValue('E' . $row, $cargo['case_no']);
                    $zhuangxiangdan->setCellValue('F' . $row, "CTNS");
                    $zhuangxiangdan->setCellValue('G' . $row, $cargo['rough_weight']);
                    $zhuangxiangdan->setCellValue('H' . $row, "KGS");
                    $zhuangxiangdan->setCellValue('I' . $row, $cargo['net_weight']);
                    $zhuangxiangdan->setCellValue('I' . $row, "KGS");
                    $sum_case_no = $sum_case_no + $cargo['case_no'];
                    $sum_rough_weight = $sum_rough_weight + $cargo['rough_weight'];
                    $sum_net_weight = $sum_net_weight + $cargo['net_weight'];
                }
            }
            $zhuangxiangdan->setCellValue('E'.($k * 43+34), $sum_case_no);
            $zhuangxiangdan->setCellValue('G'.($k * 43+34), $sum_rough_weight);
            $zhuangxiangdan->setCellValue('I'.($k * 43+34), $sum_net_weight);
            $zhuangxiangdan->setCellValue('K'.($k * 43+34), $cbm);
        }
        
        unset($sum_case_no);
        unset($sum_rough_weight);
        unset($sum_net_weight);
        return $objPHPExcel;
    }

    /**
     * hetong 售 货 合 同
     * @param type $billHead
     * @param type $objPHPExcel
     * @return type
     */
    protected function contract($billHead, $objPHPExcel,$sheet_num) {
        $cargos = $billHead->contract;
        $hetong = $objPHPExcel->getSheet($sheet_num);
        $hetong->setCellValue('A1', $billHead->deliver->name);
        $hetong->setCellValue('J2', $billHead->contract_no);
        $hetong->setCellValue('J3', date("Y/m/d", $billHead->getData('create_time') - 90 * 24 * 3600));
        $hetong->setCellValue('B4', $billHead->deliver->name);
        $hetong->setCellValue('C27', date("Y年m月d日", $billHead->getData('create_time') - 15 * 24 * 3600));
        $hetong->setCellValue('D28', date("d/M.y", $billHead->getData('create_time') - 15 * 24 * 3600));
        $hetong->setCellValue('I30', $billHead->unloading_port_str);
        $hetong->setCellValue('J30', $billHead->country->country_na);
        $hetong->setCellValue('F45', $billHead->deliver->name);
        
        $a = 0;
        $startrow = 0;
        $HighestRow = $hetong->getHighestRow();
        $HighestColumn = $hetong->getHighestColumn();
        foreach ($cargos as $k => $cargos) {
            for ($y = 1; $y <= $HighestRow; $y++) {//行数是以第1行开始
                for ($x = 'A'; $x <= $HighestColumn; $x++) {//列数是以A列开始
                    $txt = trim($hetong->getCell($x . $y)->getValue());
                    if ($txt && $txt{0} == '#')
                        $txt = isset($ar[$i]) ? $ar[$i++] : '';
                    $h = $y + $startrow;
                    $hetong->getCell("$x$h")->setValue($txt);
                    $Width = $hetong->getColumnDimension($x)->getWidth();
                    $Height = $hetong->getRowDimension($y)->getRowHeight();
                    $hetong->getColumnDimension("$x")->setWidth($Width);
                    $hetong->getRowDimension("$h")->setRowHeight($Height);
                    $hetong->duplicateStyle($hetong->getStyle("$x$y"), "$x$h");
                }
            }
            foreach ($hetong->getMergeCells() as $merge) {
                $merge = preg_replace_callback('/\d+/', function ($matches)use($startrow) {
                    return $matches[0] + $startrow;
                }, $merge);
                $hetong->mergeCells($merge);
            }
            $startrow += $HighestRow + 1; //多加3行便于裁剪
        $sum_amount=0;
            foreach ($cargos as $i => $cargo) {
                $row = $k * 50 + $i + 13; //第X行开始插入
                if (empty($cargo['name'])) {
                    $hetong->setCellValue('C' . $row, "");
                    $hetong->setCellValue('G' . $row, "");
                    $hetong->setCellValue('H' . $row, "");
                    $hetong->setCellValue('I' . $row, "");
                    $hetong->setCellValue('J' . $row, "");
                } else {
                    $hetong->setCellValue('C' . $row, $cargo['name']);
                    $hetong->setCellValue('G' . $row, $cargo['unit1']);
                    $hetong->setCellValue('H' . $row, $cargo['unit']);
                    $hetong->setCellValue('I' . $row, $billHead['currency'] . $cargo['price']);
                    $hetong->setCellValue('J' . $row, $billHead['currency'] . $cargo['amount']);
                    $sum_amount = $sum_amount + $cargo['amount'];
                }
            }
            $hetong->setCellValue('J' . ( $k * 50 + 25), $billHead['currency'] . $sum_amount);
        }
        unset($sum_amount);
        return $objPHPExcel;
    }

    /**
     * ganghang 港航
     * @param type $billHead
     * @param type $objPHPExcel
     * @return type
     */
    protected function ganghang($billHead, $objPHPExcel,$sheet_num) {
        $ganghang = $objPHPExcel->getSheet($sheet_num);
        $ganghang->setCellValue('B6', $billHead->ship_name . "  /  " . $billHead->voyage_no);
        $ganghang->setCellValue('B7', $billHead->bill_no);
        $ganghang->setCellValue('D7', $billHead->sum_cargos->case_no);
        $ganghang->setCellValue('G7', $billHead->sum_cargos->rough_weight);
        $ganghang->setCellValue('B8', $billHead->box_no);
        $ganghang->setCellValue('D25', $billHead->create_time);
        return $objPHPExcel;
    }

    /**
     * weituoshu 代 理 报 关 委 托 书
     * @param type $billHead
     * @param type $objPHPExcel
     * @return type
     */
    protected function weituoshu($billHead, $objPHPExcel,$sheet_num) {
        $weituoshu = $objPHPExcel->getSheet($sheet_num);
        $weituoshu->setCellValue('AC2', $billHead->serial_no);
        $weituoshu->setCellValue('A3', $billHead->broker->name);
        $weituoshu->setCellValue('B9', "本委托书有效期自签字之日起至 " . date('Y') . " 年 12月  31日止。");
        $weituoshu->setCellValue('AC15', $billHead->create_time_chs);
        $weituoshu->setCellValue('C18', $billHead->deliver->name);
        $weituoshu->setCellValue('C19', $billHead->cargos[0]->name);
        $letter = array("D21", "F21", "H21", "J21", "L21", "N21", "P21", "R21", "T21", "V21");
        foreach ($billHead->cargos[0]->hs_code_arr as $i => $c) {
            $weituoshu->setCellValue($letter[$i], $c);
        }
        //被委托方业务签章：
        /*实例化插入图片类*/$objDrawing = new \PHPExcel_Worksheet_Drawing();		
	/* 设置图片路径 切记：只能是本地图片 */$objDrawing->setPath("..".$billHead->broker->office_seal);
//        /* 设置图片高度 */$objDrawing->setHeight(80);
        /* 设置图片要插入的单元格 */$objDrawing->setCoordinates("AA40");
        /* 设置图片所在单元格的格式 */$objDrawing->setOffsetX(40);
        $objDrawing->setRotation(20);
        $objDrawing->getShadow()->setVisible(true);
        $objDrawing->getShadow()->setDirection(60);
        $objDrawing->setWorksheet($weituoshu);
        //经办保关员签章：
        /*实例化插入图片类*/$objDrawing = new \PHPExcel_Worksheet_Drawing();		
	/* 设置图片路径 切记：只能是本地图片 */$objDrawing->setPath("..".$billHead->broker->customs_seal);
//        /* 设置图片高度 */$objDrawing->setHeight(80);
        /* 设置图片要插入的单元格 */$objDrawing->setCoordinates("AA47");
        /* 设置图片所在单元格的格式 */$objDrawing->setOffsetX(40);
        $objDrawing->setRotation(20);
        $objDrawing->getShadow()->setVisible(true);
        $objDrawing->getShadow()->setDirection(60);
        $objDrawing->setWorksheet($weituoshu);

        //委托方（盖章）
        $objDrawing = new \PHPExcel_Worksheet_Drawing();		
	$objDrawing->setPath("..".$billHead->deliver->office_seal);
        $objDrawing->setCoordinates("AB5");
        $objDrawing->setOffsetX(40);
        $objDrawing->setResizeProportional(false);
        $objDrawing->setHeight(160);//照片高度
        $objDrawing->setWidth(156);  //照片宽度
//        $objDrawing->setWidthAndHeight(145,136);
        $objDrawing->setRotation(20);
        $objDrawing->getShadow()->setVisible(true);
        $objDrawing->getShadow()->setDirection(60);
        $objDrawing->setWorksheet($weituoshu);
        //委托方（盖章）
        $objDrawing = new \PHPExcel_Worksheet_Drawing();		
	$objDrawing->setPath("..".$billHead->deliver->office_seal);
        $objDrawing->setCoordinates("B37");
        $objDrawing->setResizeProportional(false);
        $objDrawing->setHeight(160);//照片高度
        $objDrawing->setWidth(156);  //照片宽度
        $objDrawing->setOffsetX(0);
        $objDrawing->setRotation(20);
        $objDrawing->getShadow()->setVisible(true);
        $objDrawing->getShadow()->setDirection(60);
        $objDrawing->setWorksheet($weituoshu);
        
        // 经办人签章：
        $objDrawing = new \PHPExcel_Worksheet_Drawing();        
	$objDrawing->setPath("..".$billHead->deliver->customs_seal);
        $objDrawing->setCoordinates("AE11");
        $objDrawing->setOffsetX(40);
        $objDrawing->setRotation(20);
        $objDrawing->getShadow()->setVisible(true);
        $objDrawing->getShadow()->setDirection(60);
        $objDrawing->setWorksheet($weituoshu);
        // 经办人签章：
        $objDrawing = new \PHPExcel_Worksheet_Drawing();         
	$objDrawing->setPath("..".$billHead->deliver->customs_seal);
        $objDrawing->setCoordinates("P43");
        $objDrawing->setOffsetX(40);
        $objDrawing->setRotation(20);
        $objDrawing->getShadow()->setVisible(true);
        $objDrawing->getShadow()->setDirection(60);
        $objDrawing->setWorksheet($weituoshu);
        
        /*实例化插入图片类*/$objDrawing = new \PHPExcel_Worksheet_Drawing();		
	/* 设置图片路径 切记：只能是本地图片 */$objDrawing->setPath("../public/static/images/ccba.png");
//        /* 设置图片高度 */$objDrawing->setHeight(80);
        /* 设置图片要插入的单元格 */$objDrawing->setCoordinates("A50");
        /* 设置图片所在单元格的格式 */$objDrawing->setOffsetX(40);
        $objDrawing->setRotation(20);
        $objDrawing->getShadow()->setVisible(true);
        $objDrawing->getShadow()->setDirection(60);
        $objDrawing->setWorksheet($weituoshu);
        
        
        $weituoshu->setCellValue('C23', $billHead->currency);
        $weituoshu->setCellValue('I23', $billHead->sum_cargos->total);
        $weituoshu->setCellValue('C24', $billHead->create_time_chs);
        $weituoshu->setCellValue('C25', $billHead->bill_no);
        $weituoshu->setCellValue('C26', $billHead->supervise_mode_str);
        $weituoshu->setCellValue('P47', $billHead->create_time_chs);
        $weituoshu->setCellValue('AA18', $billHead->broker->name);
        $weituoshu->setCellValue('AA20', $billHead->create_time_chs);
        $weituoshu->setCellValue('AD47', $billHead->create_time_chs);
        return $objPHPExcel;
    }

    /**
     * 市场采购货物清单 shenbaoqingdan 
     * @param type $billHead
     * @param type $objPHPExcel
     * @return type
     */
    protected function markets_sb($billHead, $objPHPExcel,$sheet_num) {
        $cargos = $billHead->market;
        $shenbaoqingdan = $objPHPExcel->getSheet($sheet_num);
        $shenbaoqingdan->setCellValue('', $billHead->box_no);
        $shenbaoqingdan->setCellValue('', $billHead->bill_no);
        $shenbaoqingdan->setCellValue('', $billHead->deliver->name);
        $shenbaoqingdan->setCellValue('', $billHead->deliver->customs_code);
        foreach ($cargos as $cargos) {
            foreach ($cargos as $i=>$cargo) {
                if(empty($cargo['name'])){
                    continue;
                }
                $row = $i + 13; //第13行开始插入
                $shenbaoqingdan->setCellValue('' . $row, $cargo['name']);
                $shenbaoqingdan->setCellValue('' . $row, $cargo['number'] . $cargo['unit']);
                $shenbaoqingdan->setCellValue('' . $row, $cargo['case_no']);
                $shenbaoqingdan->setCellValue('' . $row, $cargo['pack_str']);
                $shenbaoqingdan->setCellValue('' . $row, $cargo['amount']);
                $shenbaoqingdan->setCellValue('' . $row, $cargo['currency_chs']);
                $shenbaoqingdan->setCellValue('' . $row, $cargo['rough_weight']);
            }
        }
        return $objPHPExcel;
    }

    /**
     * 小商品货物清单 jianhuaqingdan
     * @param type $billHead
     * @param type $objPHPExcel
     * @return type
     */
    protected function simple_list($billHead, $objPHPExcel,$sheet_num) {
        $cargos = $billHead->simple_list;
        $jianhuaqingdan = $objPHPExcel->getSheet($sheet_num);
        $jianhuaqingdan->setCellValue('A3', $billHead->box_no);
        $jianhuaqingdan->setCellValue('B3', $billHead->bill_no);
        foreach ($cargos as $cargos) {
            foreach ($cargos as $i => $cargo) {
                $row = $i + 6; //第6行开始插入
                $jianhuaqingdan->setCellValue('A' . $row, $cargo['hs_code']);
                $jianhuaqingdan->setCellValue('B' . $row, $cargo['name']);
                $jianhuaqingdan->setCellValue('C' . $row, !empty($cargo['name']) ? $cargo["code"]["standard"] : "");
                $jianhuaqingdan->setCellValue('D' . $row, $cargo['number']);
                $jianhuaqingdan->setCellValue('E' . $row, $cargo['unit']);
                $jianhuaqingdan->setCellValue('F' . $row, $cargo['net_weight']);
                $jianhuaqingdan->setCellValue('G' . $row, !empty($cargo['name']) ? $cargo["amount"] : "");
            }
        }
        return $objPHPExcel;
    }

    /**
     * 简化归类货物清单  jianhuaguilei
     * @param type $billHead
     * @param type $objPHPExcel
     * @return type
     */
    protected function simple_category($billHead, $objPHPExcel,$sheet_num) {
        $cargos = $billHead->simple_category;
        $jianhuaguilei = $objPHPExcel->getSheet($sheet_num);        
        $a = 0;
        $startrow = 0;
        $HighestRow = $jianhuaguilei->getHighestRow();
        $HighestColumn = $jianhuaguilei->getHighestColumn();
        foreach ($cargos as $k => $cargos) {
            for ($y = 1; $y <= $HighestRow; $y++) {//行数是以第1行开始
                for ($x = 'A'; $x <= $HighestColumn; $x++) {//列数是以A列开始
                    $txt = trim($jianhuaguilei->getCell($x . $y)->getValue());
                    if ($txt && $txt{0} == '#')
                        $txt = isset($ar[$i]) ? $ar[$i++] : '';
                    $h = $y + $startrow;
                    $jianhuaguilei->getCell("$x$h")->setValue($txt);
                    $Width = $jianhuaguilei->getColumnDimension($x)->getWidth();
                    $Height = $jianhuaguilei->getRowDimension($y)->getRowHeight();
                    $jianhuaguilei->getColumnDimension("$x")->setWidth($Width);
                    $jianhuaguilei->getRowDimension("$h")->setRowHeight($Height);
                    $jianhuaguilei->duplicateStyle($jianhuaguilei->getStyle("$x$y"), "$x$h");
                }
            }
            foreach ($jianhuaguilei->getMergeCells() as $merge) {
                $merge = preg_replace_callback('/\d+/', function ($matches)use($startrow) {
                    return $matches[0] + $startrow;
                }, $merge);
                $jianhuaguilei->mergeCells($merge);
            }
            $startrow += $HighestRow + 1; //多加3行便于裁剪
            foreach ($cargos as $i => $cargo) {
                $row = $k * 27 + $i + 9; //第X行开始插入
                if (empty($cargo['name'])) {
                    $jianhuaguilei->setCellValue('A' . $row, "");
                    $jianhuaguilei->setCellValue('B' . $row, "");
                    $jianhuaguilei->setCellValue('C' . $row, "");
                    $jianhuaguilei->setCellValue('D' . $row, "");
                    $jianhuaguilei->setCellValue('F' . $row, "");
                    $jianhuaguilei->setCellValue('G' . $row, "");
                    $jianhuaguilei->setCellValue('H' . $row, "");
                } else {
                    $jianhuaguilei->setCellValue('A' . $row, substr($cargo['hs_code'], 0, 2));
                    $jianhuaguilei->setCellValue('B' . $row, ++$a);
                    $jianhuaguilei->setCellValue('C' . $row, $cargo["hs_code"]);
                    $jianhuaguilei->setCellValue('D' . $row, $cargo['name']);
                    $jianhuaguilei->setCellValue('F' . $row, $cargo['case_no'] . "箱");
                    $jianhuaguilei->setCellValue('G' . $row, $cargo['amount']);
                    $jianhuaguilei->setCellValue('H' . $row, $cargo['net_weight']);
                }
            }
            $jianhuaguilei->setCellValue('G'.($k * 27+3), $billHead->deliver->name);
            $jianhuaguilei->setCellValue('D'.($k * 27+5), $billHead->box_no);
        }

        return $objPHPExcel;
    }

    /**
     * 联网清单  lianwangqingdan
     * @param type $billHead
     * @param type $objPHPExcel
     * @return type
     */
    protected function cargos($billHead, $objPHPExcel,$sheet_num) {
        $cargos = $billHead->cargos;
        $lianwangqingdan = $objPHPExcel->getSheet($sheet_num);
//        foreach ($cargos as $cargos) {
        foreach ($cargos as $i => $cargo) {
            $row = $i + 2; //第2行开始插入
            $lianwangqingdan->setCellValue('A' . $row, $cargo['market_no']);
            $lianwangqingdan->setCellValue('B' . $row, $cargo['name']);
            $lianwangqingdan->setCellValue('C' . $row, $cargo["hs_code"]);
            $lianwangqingdan->setCellValue('H' . $row, $cargo['number']);
            $lianwangqingdan->setCellValue('I' . $row, $cargo['unit']);
            $lianwangqingdan->setCellValue('J' . $row, $cargo['case_no']);
            $lianwangqingdan->setCellValue('K' . $row, $billHead->pack_str);
            $lianwangqingdan->setCellValue('L' . $row, $billHead->currency);
            $lianwangqingdan->setCellValue('M' . $row, $cargo['amount']);
            $lianwangqingdan->setCellValue('N' . $row, $cargo['net_weight']);
        }
//        }
        return $objPHPExcel;
    }

    /**
     * 市场采购贸易交易单 caigouqingdan
     * @param type $billHead
     * @param type $objPHPExcel
     * @return type
     */
    protected function markets($billHead, $objPHPExcel,$sheet_num) {
        $cargos = $billHead->markets;
        $caigouqingdan = $objPHPExcel->getSheet($sheet_num);
        $customerparams = CustomerParams::get(1);
        $a = 0;
        $startrow = 0;
        $HighestRow = $caigouqingdan->getHighestRow();
        $HighestColumn = $caigouqingdan->getHighestColumn();
        foreach ($cargos as $k => $cargos) {
            for ($y = 1; $y <= $HighestRow; $y++) {//行数是以第1行开始
                for ($x = 'A'; $x <= $HighestColumn; $x++) {//列数是以A列开始
                    $txt = trim($caigouqingdan->getCell($x . $y)->getValue());
                    if ($txt && $txt{0} == '#')
                        $txt = isset($ar[$i]) ? $ar[$i++] : '';
                    $h = $y + $startrow;
                    $caigouqingdan->getCell("$x$h")->setValue($txt);
                    $Width = $caigouqingdan->getColumnDimension($x)->getWidth();
                    $Height = $caigouqingdan->getRowDimension($y)->getRowHeight();
                    $caigouqingdan->getColumnDimension("$x")->setWidth($Width);
                    $caigouqingdan->getRowDimension("$h")->setRowHeight($Height);
                    $caigouqingdan->duplicateStyle($caigouqingdan->getStyle("$x$y"), "$x$h");
                }
            }
            foreach ($caigouqingdan->getMergeCells() as $merge) {
                $merge = preg_replace_callback('/\d+/', function ($matches)use($startrow) {
                    return $matches[0] + $startrow;
                }, $merge);
                $caigouqingdan->mergeCells($merge);
            }
            $startrow += $HighestRow + 1; //多加3行便于裁剪
            foreach ($cargos as $i => $cargo) {
                $row = $k * 33 + $i + 13; //第X行开始插入
                $caigouqingdan->setCellValue('C' . (2 + ($k * 33)), $cargos[0]->market_no ? $cargos[0]->market_no : $customerparams['def_merchant_code']);
                $caigouqingdan->setCellValue('C' . (9 + ($k * 33)), $billHead->create_time_chs);
                $caigouqingdan->setCellValue('G' . (9 + ($k * 33)), $customerparams["stuffing_locat"]);
                if (empty($cargo['name'])) {
                    $caigouqingdan->setCellValue('C' .$row, "");
                    $caigouqingdan->setCellValue('D' .$row, "");
                    $caigouqingdan->setCellValue('E' .$row, "");
                    $caigouqingdan->setCellValue('F' .$row, "");
                    $caigouqingdan->setCellValue('G' .$row, "");
                    $caigouqingdan->setCellValue('H' .$row, "");
                }
                $caigouqingdan->setCellValue('C' . $row, $cargo['name']);
                $caigouqingdan->setCellValue('D' . $row, $cargo['case_no']);
                $caigouqingdan->setCellValue('E' . $row, $cargo['per_qty'] ? $cargo['per_qty'] : $cargo['per_qty']);
                $caigouqingdan->setCellValue('F' . $row, $cargo['number'] . $cargo['unit']);
                $caigouqingdan->setCellValue('G' . $row, $cargo['np'] != 0 ? round($cargo['np'] * $customerparams["exchange_rate"], 2) : "");
                $caigouqingdan->setCellValue('H' . $row, !empty($cargo['amount']) ? round($cargo['np'] * $cargo['number'] * $customerparams["exchange_rate"], 2) : 0);
            }
        }
        return $objPHPExcel;
    }

    
    
    var  $startrow=0;
    public function add_data($ar) {        
        $ar = array_values($ar);
        if(!isset($this->target)) $this->target = clone $this->tpl;
        $sheet = $this->tpl->getActiveSheet(2);        
        $i = 0;        
        for ($y = 1; $y <= $sheet->getHighestRow(); $y++){//行数是以第1行开始
            for ($x = 'A'; $x <= $sheet->getHighestColumn(); $x++) {//列数是以A列开始
                $txt = trim($sheet->getCell($x . $y)->getValue());
                if ($txt && $txt{0} == '#') $txt = isset($ar[$i]) ? $ar[$i++] : '';
                $h = $y + $this->startrow;
                $this->target->getActiveSheet(2)->getCell("$x$h")->setValue($txt);
                $this->target->getActiveSheet(2)->duplicateStyle($sheet->getStyle("$x$y"), "$x$h");
            }
        }
        foreach ($sheet->getMergeCells() as $merge) {
            $merge = preg_replace_callback('/\d+/', function ($matches) {
                return $matches[0]+$this->startrow;
            }, $merge);

            $this->target->getActiveSheet(2)->mergeCells($merge);
        }
        $this->startrow += $sheet->getHighestRow() + 1; //多加3行便于裁剪        
    }
    function output($fn) {
        $t = \PHPExcel_IOFactory::createWriter($this->target, 'Excel5');
        $t->save($fn);
    }
    public function test() {
        $inputFileName = '../public/excel_temp/tempt1.xls';
        $this->tpl = \PHPExcel_IOFactory::load($inputFileName);
        $this->add_data(array(1, 2, 3, 7, 5, 6, 7, 8, 9)); //数据要按模板中“#”出现的次序排列
        $this->add_data(array(1, 2, 3, 8, 5, 6, 7, 8, 9)); //汉字要用utf-8的
        $this->add_data(array(1, 2, 3, 9, 5, 6, 7, 8, 9));
        $this->output('33.xls'); //输出到文件
    }
}