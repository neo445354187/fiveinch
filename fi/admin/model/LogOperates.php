<?php
namespace fi\admin\model;
use think\Db;
/**
 * 操作日志业务处理
 */
class LogOperates extends Base{
    /**
	 * 分页
	 */
	public function pageQuery(){
		$startDate = input('get.startDate');
		$endDate = input('get.endDate');
		$where = [];
		if($startDate!='')$where['l.login_time'] = ['>=',$startDate." 00:00:00"];
		if($endDate!='')$where[' l.login_time'] = ['<=',$endDate." 23:59:59"];
		return $mrs = Db::table('__LOG_OPERATES__')->alias('l')->join('__STAFFS__ s',' l.staff_id=s.staff_id','left')
		    ->join('__MENUS__ m',' l.menu_id=m.menu_id','left')
			->where($where)
			->field('l.*,s.staff_name,m.menu_name')
			->order('l.operate_id', 'desc')->paginate(input('pagesize/d'));
			
	}
	
	/**
	 * 新增操作权限
	 */
	public function add($param){
		$data = [];
		$data['staff_id'] = (int)session('FI_STAFF.staff_id');
		$data['operate_time'] = date('Y-m-d H:i:s');
		$data['menu_id'] = $param['menu_id'];
		$data['operate_desc'] = $param['operate_desc'];
		$data['content'] = $param['content'];
		$data['operate_url'] = $param['operate_url'];
		$data['operate_ip'] = $param['operate_ip'];
		$this->create($data);
	}
	
	/**
	 *  获取指定的操作记录
	 */
	public function getById($id){
		$rs = $this->get($id);
		if(!empty($rs)){
			return FIReturn('', 1,$rs);
		}
		return FIReturn('对不起，没有找到该记录', -1);
	}
}
