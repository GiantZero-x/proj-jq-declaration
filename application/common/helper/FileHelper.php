<?php
namespace app\common\helper;

class FileHelper
{
	public static function binary2file($file)
	{
		$content = $GLOBALS['HTTP_RAW_POST_DATA'];
        
        if (empty($content)) {  
            $content = file_get_contents('php://input');
        }

        return file_put_contents($file, $content, true);  
	}

	public static function file2binary($file)
	{
		return file_get_contents($file);
	}
}	