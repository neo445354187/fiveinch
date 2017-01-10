<?php
namespace fi\admin\model;
use think\Db;
/**
 * 商家认证业务处理
 */
class Accreds extends Base{
	/**
	 * 分页
	 */
	public function pageQuery(){
		return $this->where('status',1)->field(true)->order('accred_id desc')->paginate(input('pagesize/d'));
	}
	/**
	 * 列表
	 */
    public function listQuery(){
		return $this->where('status',1)->field(true)->select();
	}
	public function getById($id){
		return $this->get(['accred_id'=>$id,'status'=>1]);
	}
	/**
	 * 新增
	 */
	public function add(){
		$data = input('post.');
		$data['create_time'] = date('Y-m-d H:i:s');
		FIUnset($data,'accred_id');
		Db::startTrans();
		try{
			$result = $this->validate('Accreds.add')->allowField(true)->save($data);
			if(false !==$result){
				$id = $this->accred_id;
				//启用上传图片
				FIUseImages(1, $id, $data['accred_img']);
		        if(false !== $result){
		        	Db::commit();
		        	return FIReturn("新增成功", 1);
		        }
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
		$data = input('post.');
		FIUnset($data,'create_time');
		Db::startTrans();
		try{
			FIUseImages(1, (int)$data['accred_id'], $data['accred_img'], 'accreds', 'accred_img');
		    $result = $this->validate('Accreds.edit')->allowField(true)->save($data,['accred_id'=>(int)$data['accred_id']]);
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
	    $id = (int)input('post.id/d');
	    Db::startTrans();
		try{
		    $result = $this->setField(['status'=>-1,'accred_id'=>$id]);
		    FIUnuseImage('accreds','accred_img',$id);	
	        if(false !== $result){
	        	Db::commit();
	        	return FIReturn("删除成功", 1);
	        }
		}catch (\Exception $e) {
            Db::rollback();
        }
        return FIReturn('删除失败',-1); 
	}
	
}
