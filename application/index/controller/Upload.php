<?php
namespace app\index\controller;
use app\common\LoginController;
use think\Request;

class Upload extends LoginController
{
	// 文件上传提交
	public function index(Request $request)
	{
		// 获取表单上传文件
		$file = $request->file('fileList');
		if (empty($file)) {
			return '请选择上传文件';
		}
		// 移动到框架应用根目录/public/uploads/
		$info = $file->validate(['ext' => 'xlsx,xls,pdf,jpg,png,jpeg,gif'])->move(ROOT_PATH . 'public' . DS .'uploads');
		if ($info) {
				return json("/public/uploads/".$info->getSaveName());
			} else {
				// 上传失败获取错误信息
				return $file->getError();
			}
		}
            // 文件上传提交
        public function upload(Request $request) {                
                // 获取表单上传文件
                $file = $request->file('file');

                if (empty($file)) {
                        return json('请选择上传文件');
                }
                // 移动到框架应用根目录/public/uploads/
                $info = $file->validate(['ext' => 'xlsx,xls,pdf,jpg,png,jpeg,gif'])->move(ROOT_PATH . 'public' . DS . 'uploads');
                if ($info) {
                         return json("/public/uploads/" . $info->getSaveName());
                } else {
                    // 上传失败获取错误信息
                         return $file->getError();
                }
        }
}
