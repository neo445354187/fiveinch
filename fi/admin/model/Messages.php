<?php
namespace fi\admin\model;
use think\Db;
/**
 * 商城消息业务处理
 */
class Messages extends Base{
	/**
	 * 分页
	 */
	public function pageQuery(){
		$where = [];
		$where['m.status'] = 1;
		$msg_type = (int)input('msg_type');  
		if($msg_type >= 0)$where['msg_type'] = $msg_type;
		$msg_content = input('msg_content');
		if(!empty($msg_content))$where['msg_content']=['like',"%$msg_content%"];
		$rs = $this->alias('m')
		->field('m.*,u.login_name,s.shop_name,st.login_name stName')
		->join('__USERS__ u','m.receive_user_id=u.user_id','left')
		->join('__SHOPS__ s','m.receive_user_id=s.shop_id','left')
		->join('__STAFFS__ st','m.send_user_id=st.staff_id','left')
		->order('id desc')
		->where($where)
		->paginate(input('pagesize/d'))->toArray();
	    foreach ($rs['Rows'] as $key => $v){
         	$rs['Rows'][$key]['msg_content'] = FIMSubstr(strip_tags($v['msg_content']),0,140);
        }
		return $rs;
	}
	public function getById($id){
		return $this->get(['id'=>$id,'status'=>1]);
	}
	/**
	 * 新增
	 */
	public function add(){
		$data = input('post.');
		// 图片记录
		$rule = '/src="\/(upload.*?)"/';
        preg_match_all($rule,$data['msg_content'],$result);
        // 获取src数组
        $imgs = $result[1];

		$data['create_time'] = date('Y-m-d H:i:s');
		$data['send_user_id'] = session('FI_STAFF.staff_id');
		//判断发送对象
		if($data['sendType']=='theUser'){
			$ids = explode(',',$data['htarget']);
		}
		elseif($data['sendType']=='shop'){
			//获取所有店铺的id
			$ids = model('Shops')->getAllShopId();
		}elseif($data['sendType']=='users'){
			//获取所有用户id
			$ids = model('users')->getAllUserId();
		}
		FIUnset($data,'id,sendType,htarget');//删除多余字段
		$list = [];
		//去重
		array_unique($ids);
		foreach($ids as $v)
		{
			$data['receive_user_id'] = $v;
			$data['msg_type'] = 0;//后台手工发送消息
			$list[] = $data;
		}

		Db::startTrans();
		try{
			$result = $this->allowField(true)->saveAll($list);
			$id = $result[0]['id'];//新增的第一条消息id
        	if(false !== $result){
        	    //启用上传图片
			    FIUseImages(1, $id, $imgs);
        		Db::commit();
        	    return FIReturn("新增成功", 1);
        	}
		}catch (\Exception $e) {
            Db::rollback();
        }
        return FIReturn('新增失败',-1);
        
	}
	/**
	 * 删除
	 */
    public function del(){
	    $id = input('post.id/d');
		$data = [];
		$data['status'] = -1;
	    $result = $this->update($data,['id'=>$id]);
        if(false !== $result){
        	return FIReturn("删除成功", 1);
        }else{
        	return FIReturn($this->getError(),-1);
        }
	}
	
}
