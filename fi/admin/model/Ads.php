<?php
namespace fi\admin\model;
use think\Db;
/**
 * 广告业务处理
 */
class ads extends Base{
	/**
	 * 分页
	 */
	public function pageQuery(){
		$where = [];
		$where['a.status'] = 1;
		$pt = (int)input('position_type');
		$apId = (int)input('ad_position_id');
		if($pt>0)$where['a.position_type'] = $pt;
		if($apId!=0)$where['a.ad_position_id'] = $apId;
		
		
		return Db::table('__ADS__')->alias('a')
		            ->join('__AD_POSITIONS__ ap','a.position_type=ap.position_type AND a.ad_position_id=ap.position_id AND ap.status=1','left')
					->field('ad_id,ad_name,ad_position_id,ad_url,ad_start_date,ad_end_date,ad_position_id,ad_file,ad_click_num,ap.position_name,a.ad_sort')
		            ->where($where)->order('ad_id desc')
		            ->order('ad_sort','asc')
		            ->paginate(input('pagesize/d'));
	}
	public function getById($id){
		return $this->get(['ad_id'=>$id,'status'=>1]);
	}
	/**
	 * 新增
	 */
	public function add(){
		$data = input('post.');
		$data['create_time'] = date('Y-m-d H:i:s');
		FIUnset($data,'ad_id');
		Db::startTrans();
		try{
			$result = $this->validate('ads.add')->allowField(true)->save($data);
			$id = $this->ad_id;
        	if(false !== $result){
        	    //启用上传图片
			    FIUseImages(1, $id, $data['ad_file']);
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
		$data = input('post.');
		FIUnset($data,'create_time');
		Db::startTrans();
		try{
			FIUseImages(1, (int)$data['ad_id'], $data['ad_file'], 'ads-pic', 'ad_file');
		    $result = $this->validate('ads.edit')->allowField(true)->save($data,['ad_id'=>(int)$data['ad_id']]);
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
		    $result = $this->setField(['ad_id'=>$id,'status'=>-1]);
		    FIUnuseImage('ads','ad_file',$id);
	        if(false !== $result){
	        	Db::commit();
	        	return FIReturn("删除成功", 1);
	        }
		}catch (\Exception $e) {
            Db::rollback();
            return FIReturn('删除失败',-1);
        }
	}
	/**
	* 修改广告排序
	*/
	public function changeSort(){
		$id = (int)input('id');
		$ad_sort = (int)input('ad_sort');
		$result = $this->setField(['ad_id'=>$id,'ad_sort'=>$ad_sort]);
		if(false !== $result){
        	return FIReturn("操作成功", 1);
        }else{
        	return FIReturn($this->getError(),-1);
        }
	}
	
}
