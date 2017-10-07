<?php
namespace app\index\controller;

use think\Request;
use think\Url;
use app\common\LoginController;
use app\index\service\Tcs01;
use app\index\service\Sfd01;
use app\common\helper\FileHelper;
use Curl\Curl;

class DigitalChina extends LoginController
{
    const API_URL = 'http://dcis2015.cnsaas.com/interface_HttpService/DoAction.aspx';

    public function tcso1(Curl $curl, Tcs01 $dc, Sfd01 $sfd)
    {
        $dc->setDocumentInformation($sfd);

        $bodyParameter = $dc->bodyParameter();

        //dump(json_decode($bodyParameter['data'], true));

        $curl->post(self::API_URL, $bodyParameter);

        dump($curl->response);

        //dump($bodyParameter);

        echo "以下为sfd<br/>";

        $this->sfd01($curl, $sfd);

    }

    public function sfd01(Curl $curl, Sfd01 $dc)
    {
        foreach ($dc as $pdf => $obj) {

            if ('jianhuaguilei' === $pdf && $dc->getBillHead()->classify_type == '1') {
                continue;
            }

            $curl->get(HTTP_HOST. Url::build('index/pdf/show', ['id'=> Sfd01::$id]) . '?type=' . $pdf);
            
            //echo $pdf, PHP_EOL, 'uploads/' . $pdf . '_' . Sfd01::$id . '.pdf' , is_file('uploads/' . $pdf . '_' . Sfd01::$id . '.pdf') ? '文件存在':'';

            $content = FileHelper::file2binary('uploads/' . $pdf . '_' . Sfd01::$id . '.pdf');               

            $curl->post(self::API_URL, $obj->setEDOCData(base64_encode($content))->bodyParameter(true));
            
            //dump($obj->bodyParameter());

            dump($curl->response);
        }
    }


}





