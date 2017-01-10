<?php
namespace fi\admin\model;
/**
 * 友情链接业务处理
 */
use think\Db;
class FriendLinks extends Base{
	/**
	 * 分页
	 */
	public function pageQuery(){
		return $this->where('status',1)->field('friend_link_id,friend_link_name,friend_link_ico,friend_link_sort,friend_link_url')->order('friend_link_id desc')->paginate(input('pagesize/d'));
	}
	public function getById($id){
		return $this->get(['friend_link_id'=>$id,'status'=>1]);
	}
	/**
	 * 新增
	 */
	public function add(){
		$data = input('post.');
		$data['create_time'] = date('Y-m-d H:i:s');
		FIUnset($data,'friend_link_id');
		Db::startTrans();
		try{
			$result = $this->validate('friend_links.add')->allowField(true)->save($data);
			$id = $this->friend_link_id;
	        if(false !== $result){
	        	//启用上传图片
			    FIUseImages(1, $id, $data['friend_link_ico']);
			    Db::commit();
	        	return FIReturn("新增成功", 1);
	        }
		}catch (\Exception $e) {
            Db::rollback();
        }
        return FIReturn('新增失败',-1);
	}
    /**
	 * 编辑
	 */
	public function edit(){
		$id = (int)input('post.friend_link_id');
		$data = input('post.');
		FIUnset($data,'create_time');

		Db::startTrans();
		try{
			FIUseImages(1, $id, $data['friend_link_ico'], 'friend_links', 'friend_link_ico');
		    $result = $this->validate('friend_links.edit')->allowField(true)->save($data,['friend_link_id'=>$id]);
	        if(false !== $result){
	        	Db::commit();
	        	return FIReturn("编辑成功", 1);
	        }
	    }catch (\Exception $e) {
            Db::rollback();
        }
        return FIReturn('编辑失败',-1);  
	}
	/**
	 * 删除
	 */
    public function del(){
	    $id = input('post.id/d');
	    Db::startTrans();
		try{
			$data = [];
			$data['status'] = -1;
		    $result = $this->update($data,['friend_link_id'=>$id]);
	        if(false !== $result){
	        	FIUnuseImage('friend_links','friend_link_ico',$id);
	        	Db::commit();
	        	return FIReturn("删除成功", 1);
	        }
		}catch (\Exception $e) {
            Db::rollback();
            return FIReturn('删除失败',-1);
        }
	}
	
}
