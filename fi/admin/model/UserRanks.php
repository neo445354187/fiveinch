<?php
namespace fi\admin\model;
use think\Db;
/**
 * 会员等级业务处理
 */
class UserRanks extends Base{
	/**
	 * 分页
	 */
	public function pageQuery(){
		return $this->where('status',1)->field(true)->order('rank_id desc')->paginate(input('pagesize/d'));
	}
	public function getById($id){
		return $this->get(['rank_id'=>$id,'status'=>1]);
	}
	/**
	 * 新增
	 */
	public function add(){
		$data = input('post.');
		$data['create_time'] = date('Y-m-d H:i:s');
		FIUnset($data,'rank_id');
		Db::startTrans();
		try{
			$result = $this->validate('UserRanks.add')->allowField(true)->save($data);
			$id = $this->rank_id;
			//启用上传图片
			FIUseImages(1, $id, $data['userrank_img']);
	        if(false !== $result){
	        	Db::commit();
	        	return FIReturn("新增成功", 1);
	        }
		}catch (\Exception $e) {
            Db::rollback();
            return FIReturn('删除失败',-1);
        }
	}
    /**
	 * 编辑
	 */
	public function edit(){
		$Id = (int)input('post.rank_id');
		$data = input('post.');
		Db::startTrans();
		try{
			FIUseImages(1, $Id, $data['userrank_img'], 'user_ranks', 'userrank_img');
			FIUnset($data,'create_time');
		    $result = $this->validate('UserRanks.edit')->allowField(true)->save($data,['rank_id'=>$Id]);
	        if(false !== $result){
	        	Db::commit();
	        	return FIReturn("编辑成功", 1);
	        }
		}catch (\Exception $e) {
            Db::rollback();
            return FIReturn('编辑失败',-1);
        }	        
	}
	/**
	 * 删除
	 */
    public function del(){
	    $id = (int)input('post.id/d');
	    Db::startTrans();
		try{
			$data = [];
			$data['status'] = -1;
		    $result = $this->update($data,['rank_id'=>$id]);
	        if(false !== $result){
	        	FIUnuseImage('user_ranks','userrank_img',$id);
	        	Db::commit();
	        	return FIReturn("删除成功", 1);
	        }
		}catch (\Exception $e) {
            Db::rollback();
            return FIReturn('编辑失败',-1);
        }	
	}
	
}
