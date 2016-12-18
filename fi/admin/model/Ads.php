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
		$where['a.dataFlag'] = 1;
		$pt = (int)input('positionType');
		$apId = (int)input('adPositionId');
		if($pt>0)$where['a.positionType'] = $pt;
		if($apId!=0)$where['a.adPositionId'] = $apId;
		
		
		return Db::table('__ADS__')->alias('a')
		            ->join('__AD_POSITIONS__ ap','a.positionType=ap.positionType AND a.adPositionId=ap.positionId AND ap.dataFlag=1','left')
					->field('adId,adName,adPositionId,adURL,adStartDate,adEndDate,adPositionId,adFile,adClickNum,ap.positionName,a.adSort')
		            ->where($where)->order('adId desc')
		            ->order('adSort','asc')
		            ->paginate(input('pagesize/d'));
	}
	public function getById($id){
		return $this->get(['adId'=>$id,'dataFlag'=>1]);
	}
	/**
	 * 新增
	 */
	public function add(){
		$data = input('post.');
		$data['createTime'] = date('Y-m-d H:i:s');
		FIUnset($data,'adId');
		Db::startTrans();
		try{
			$result = $this->validate('ads.add')->allowField(true)->save($data);
			$id = $this->adId;
        	if(false !== $result){
        	    //启用上传图片
			    FIUseImages(1, $id, $data['adFile']);
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
		FIUnset($data,'createTime');
		Db::startTrans();
		try{
			FIUseImages(1, (int)$data['adId'], $data['adFile'], 'ads-pic', 'adFile');
		    $result = $this->validate('ads.edit')->allowField(true)->save($data,['adId'=>(int)$data['adId']]);
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
		    $result = $this->setField(['adId'=>$id,'dataFlag'=>-1]);
		    FIUnuseImage('ads','adFile',$id);
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
		$adSort = (int)input('adSort');
		$result = $this->setField(['adId'=>$id,'adSort'=>$adSort]);
		if(false !== $result){
        	return FIReturn("操作成功", 1);
        }else{
        	return FIReturn($this->getError(),-1);
        }
	}
	
}
