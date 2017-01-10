<?php
namespace fi\admin\model;
use think\Db;
/**
 * 登录日志业务处理
 */
class LogStaffLogins extends Base{
    /**
	 * 分页
	 */
	public function pageQuery(){
		$startDate = input('get.startDate');
		$endDate = input('get.endDate');
		$where = [];
		if($startDate!='')$where['l.login_time'] = ['>=',$startDate." 00:00:00"];
		if($endDate!='')$where[' l.login_time'] = ['<=',$endDate." 23:59:59"];
		return $mrs = Db::table('__LOG_STAFF_LOGINS__')->alias('l')->join('__STAFFS__ s',' l.staff_id=s.staff_id','left')
			->where($where)
			->field('l.*,s.staff_name')
			->order('l.login_id', 'desc')->paginate(input('pagesize/d'));
			
	}
}
