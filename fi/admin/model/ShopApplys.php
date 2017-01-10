<?php
namespace fi\admin\model;
use Think\Db;
use fi\admin\model\Shops;
/**
 * 门店申请业务处理
 */
class ShopApplys extends Base{
    /**
	 * 分页
	 */
	public function pageQuery(){
		$page = Db::table('__SHOP_APPLYS__')->alias('s')->join('__USERS__ u','s.user_id=u.user_id and u.status=1','left')
			->where(['s.status'=>1])
			->field('u.login_name,s.user_id,s.shop_id,s.linkman,s.phone_no,apply_status,s.create_time,apply_desc,apply_id')
			->order('s.apply_id', 'desc')
			->paginate(input('pagesize/d'))->toArray();
		return $page;
	}
	
	/**
	 * 获取信息
	 */
	public function getById($id){
		return Db::table('__SHOP_APPLYS__')->alias('s')->join('__USERS__ u','s.user_id=u.user_id and u.status=1 and s.status=1','left')
			->where(['s.status'=>1,'s.apply_id'=>$id])
			->field('u.login_name,s.*')->find();
	}
	
	/**
	 * 删除菜单
	 */
	public function del(){
	    $id = input('post.id/d');
		$data = [];
		$data['status'] = -1;
	    $result = $this->update($data,['apply_id'=>$id]);
        if(false !== $result){
        	return FIReturn("删除成功", 1);
        }else{
        	return FIReturn($this->getError(),-1);
        }
	}
	
	/**
	 * 处理申请
	 */
	public function handle(){
		$id = input('post.apply_id/d');
		$data = [];
		$data['apply_status'] = input('post.apply_status/d');
		$data['handle_desc'] = input('post.handle_desc');
		if(!in_array($data['apply_status'],array(-1,1)))return FIReturn("无效的处理状态", -1);
		if($data['apply_status']==-1 && $data['handle_desc']=='')return FIReturn("请输入申请失败原因", -1);
		$result = $this->where(['apply_id'=>$id])->update($data);
        if(false !== $result){
        	return FIReturn("编辑成功", 1);
        }else{
        	return FIReturn($this->getError(),-1);
        }
	}
	
	/**
	 * 检测该开店申请是否开店
	 */
	public function checkOpenShop($id){
		return Db::table('__SHOP_APPLYS__')->alias('s')
			->where(['s.status'=>1,'apply_id'=>$id])
			->field('s.user_id,s.shop_id')
			->find();
	}
	/**
	 * 修改开店状态
	 */
	public function editApplyOpenStatus($id,$shop_id){
		$this->where(['apply_id'=>$id,'shop_id'=>0])->update(['shop_id'=>$shop_id]);
	}
}
