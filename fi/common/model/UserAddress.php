<?php
namespace fi\common\model;
/**
 * 用户地址
 */
use think\Db;
class UserAddress extends Base{
     /**
      * 获取列表
      */
      public function listQuery($user_id){
         $where = ['user_id'=>(int)$user_id,'status'=>1];
         $rs = $this->order('address_id desc')->where($where)->select();
         $area_ids = [];
         $areaMaps = [];
         foreach ($rs as $key => $v){
         	 $tmp = explode('_',$v['area_id_path']);
         	 foreach ($tmp as $vv){
         		if($vv=='')continue;
         	    if(!in_array($vv,$area_ids))$area_ids[] = $vv;
         	 }
         	 $rs[$key]['area_id2'] = $tmp[1];
         }
         if(!empty($area_ids)){
	         $areas = Db::table('__AREAS__')->where(['status'=>1,'area_id'=>['in',$area_ids]])->field('area_id,area_name')->select();
	         foreach ($areas as $v){
	         	 $areaMaps[$v['area_id']] = $v['area_name'];
	         }
	         foreach ($rs as $key => $v){
	         	 $tmp = explode('_',$v['area_id_path']);
	         	 $area_names = [];
		         foreach ($tmp as $vv){
	         		if($vv=='')continue;
	         	    $area_names[] = $areaMaps[$vv];
	         	 }
	         	 $rs[$key]['area_name'] = implode('',$area_names);
	         	 $rs[$key]['area_name1'] = $areaMaps[$v['area_id2']];
	         }
         }
         return $rs;
      }
    /**
    *  获取用户信息
    */
    public function getById($id){
        return $this->get(['address_id'=>$id,'user_id'=>(int)session('FI_USER.user_id')]);
    }
    /**
     * 新增
     */
    public function add(){
        $data = input('post.');
        $data['user_id'] = (int)session('FI_USER.user_id');
        $data['create_time'] = date('Y-m-d H:i:s');
        $area_ids = model('Areas')->getParentIs((int)input('area_id'));
        if(!empty($area_ids))$data['area_id_path'] = implode('_',$area_ids)."_";
        $result = $this->validate('UserAddress.add')->allowField(true)->save($data);
        if(false !== $result){
            //修改默认地址
            if((int)input('post.is_default')==1)
              $this->where("address_id != $this->address_id")->setField('is_default',0);
            return FIReturn("新增成功", 1,['address_id'=>$this->address_id]);
        }else{
            return FIReturn($this->getError(),-1);
        }
    }
    /**
     * 编辑资料
     */
    public function edit(){
        $id = (int)input('post.address_id');
        $data = input('post.');
        $area_ids = model('Areas')->getParentIs((int)input('area_id'));
        if(!empty($area_ids))$data['area_id_path'] = implode('_',$area_ids)."_";
        $result = $this->validate('UserAddress.edit')->allowField(true)->save($data,['address_id'=>$id,'user_id'=>(int)session('FI_USER.user_id')]);
        //修改默认地址
        if((int)input('post.is_default')==1)
          $this->where("address_id != $id")->setField('is_default',0);
        if(false !== $result){
            return FIReturn("编辑成功", 1);
        }else{
            return FIReturn($this->getError(),-1);
        }
    }
    /**
     * 删除
     */
    public function del(){
        $id = input('post.id/d');
        $data = [];
        $data['status'] = -1;
        $result = $this->update($data,['address_id'=>$id,'user_id'=>(int)session('FI_USER.user_id')]);
        if(false !== $result){
            return FIReturn("删除成功", 1);
        }else{
            return FIReturn($this->getError(),-1);
        }
    }

    /**
    * 设置为默认地址
    */
    public function setDefault(){
        $id = (int)input('post.id');
        $this->where("address_id != $id")->setField('is_default',0);
        $rs = $this->where("address_id = $id and user_id=".(int)session('FI_USER.user_id'))->setField('is_default',1);
        if(false !== $rs){
            return FIReturn("设置成功", 1);
        }else{
            return FIReturn($this->getError(),-1);
        }
    }
    
    /**
     * 获取默认地址
     */
    public function getDefaultAddress(){
    	$user_id = (int)session('FI_USER.user_id');
    	$where = ['user_id'=>$user_id,'status'=>1];
        $rs = $this->where($where)->order('is_default desc,address_id desc')->find();
        if(empty($rs))return [];
        $area_ids = [];
        $areaMaps = [];
        $tmp = explode('_',$rs['area_id_path']);
        $rs['area_id2'] = $tmp[1];
        foreach ($tmp as $vv){
         	if($vv=='')continue;
         	if(!in_array($vv,$area_ids))$area_ids[] = $vv;
        }
        if(!empty($area_ids)){
	         $areas = Db::table('__AREAS__')->where(['status'=>1,'area_id'=>['in',$area_ids]])->field('area_id,area_name')->select();
	         foreach ($areas as $v){
	         	 $areaMaps[$v['area_id']] = $v['area_name'];
	         }
	         $tmp = explode('_',$rs['area_id_path']);
	         $area_names = [];
		     foreach ($tmp as $vv){
	         	 if($vv=='')continue;
	         	 $area_names[] = $areaMaps[$vv];
	         	 $rs['area_name'] = implode('',$area_names);
	         }
         }
         return $rs;
    }
}
