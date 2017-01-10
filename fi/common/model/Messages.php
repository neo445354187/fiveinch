<?php
namespace fi\common\model;
use fi\home\model\Shops;
/**
 * 商城消息
 */
class Messages extends Base{
   /**
    * 获取列表
    */
    public function pageQuery(){
      	 $user_id = (int)session('FI_USER.user_id');
         $where = ['receive_user_id'=>(int)$user_id,'status'=>1];
         $page = model('Messages')->where($where)->order('msg_status asc,id desc')->paginate(input('pagesize/d'))->toArray();
         foreach ($page['Rows'] as $key => $v){
         	$page['Rows'][$key]['msg_content'] = FIMSubstr(strip_tags($v['msg_content']),0,140);
         }
         return $page;
    }
   /**
    *  获取某一条消息详情
    */
    public function getById(){
    	$user_id = (int)session('FI_USER.user_id');
        $id = (int)input('msgId');
        $data = $this->get(['id'=>$id,'receive_user_id'=>$user_id]);
        if(!empty($data)){
          if($data['msg_status']==0)
            model('Messages')->where('id',$id)->setField('msg_status',1);
        }
        return $data;
    }

    /**
     * 删除
     */
    public function del(){
    	$user_id = (int)session('FI_USER.user_id');
        $id = input('id/d');
        $data = [];
        $data['status'] = -1;
        $result = $this->update($data,['id'=>$id,'receive_user_id'=>$user_id]);
        if(false !== $result){
            return FIReturn("删除成功", 1);
        }else{
            return FIReturn($this->getError(),-1);
        }
    }
    /**
    * 批量删除
    */
    public function batchDel(){
    	$user_id = (int)session('FI_USER.user_id');
        $ids = input('ids/a');
        $data = [];
        $data['status'] = -1;
        $result = $this->update($data,['id'=>['in',$ids],'receive_user_id'=>$user_id]);
        if(false !== $result){
            return FIReturn("删除成功", 1);
        }else{
            return FIReturn($this->getError(),-1);
        }
    }
    /**
    * 标记为已读
    */
    public function batchRead(){
    	$user_id = (int)session('FI_USER.user_id');
        $ids = input('ids/a');
        $data = [];
        $data['msg_status'] = 1;
        $result = $this->update($data,['id'=>['in',$ids],'receive_user_id'=>$user_id]);
        if(false !== $result){
            return FIReturn("操作成功", 1);
        }else{
            return FIReturn($this->getError(),-1);
        }
    }

    
}
