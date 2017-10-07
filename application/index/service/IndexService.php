<?php
namespace app\index\service;
use think\Db;

class IndexService
{	
	public $user;
	//     0:保存;   1为提交(待审核),2为已审核；3、已申报；4、退回
	//状态：0为保存; 1为提交(待审核); 2为已审核; 3、已申报; 4、退回;
	public $statusExplain = ['save', 'check',  'checked', 'success', 'back'];

	function getOverview()
	{
		$data = Db::table('bill_head')->field("status, COUNT(*) as 'count'")
					//->where('user_id', '=', $this->user['id'])
    					->group('status')->select();
    	$result = $this->overviewResultInit();
		if (is_array($data)) {
			foreach ($data as $k => $v) {
				if ($v['status'] < count($this->statusExplain)){
					$result[$this->statusExplain[$v['status']]] = $v['count'];
				}
			}
		}
    	return $result;
	}

	private function overviewResultInit()
	{
    	$result['save'] = 0;
    	$result['check'] = 0;
    	$result['checked'] = 0;
    	$result['success'] = 0;
    	$result['back'] = 0;
    	return $result;
	}
}	


