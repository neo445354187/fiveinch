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
		return $this->where('dataFlag',1)->field(true)->order('rankId desc')->paginate(input('pagesize/d'));
	}
	public function getById($id){
		return $this->get(['rankId'=>$id,'dataFlag'=>1]);
	}
	/**
	 * 新增
	 */
	public function add(){
		$data = input('post.');
		$data['createTime'] = date('Y-m-d H:i:s');
		FIUnset($data,'rankId');
		Db::startTrans();
		try{
			$result = $this->validate('UserRanks.add')->allowField(true)->save($data);
			$id = $this->rankId;
			//启用上传图片
			FIUseImages(1, $id, $data['userrankImg']);
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
		$Id = (int)input('post.rankId');
		$data = input('post.');
		Db::startTrans();
		try{
			FIUseImages(1, $Id, $data['userrankImg'], 'user_ranks', 'userrankImg');
			FIUnset($data,'createTime');
		    $result = $this->validate('UserRanks.edit')->allowField(true)->save($data,['rankId'=>$Id]);
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
			$data['dataFlag'] = -1;
		    $result = $this->update($data,['rankId'=>$id]);
	        if(false !== $result){
	        	FIUnuseImage('user_ranks','userrankImg',$id);
	        	Db::commit();
	        	return FIReturn("删除成功", 1);
	        }
		}catch (\Exception $e) {
            Db::rollback();
            return FIReturn('编辑失败',-1);
        }	
	}
	
}
