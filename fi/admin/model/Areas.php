<?php
namespace fi\admin\model;
/**
 * 地区业务处理
 */
class Areas extends Base{
	/**
	 * 分页
	 */
	public function pageQuery(){
		$parent_id = input('get.parent_id/d',0);
		return $this->where(['status'=>1,'parent_id'=>$parent_id])->order('area_id desc')->paginate(input('post.pagesize/d'));
	}
	
	/**
	 * 获取指定对象
	 */
	public function getById($id){
		return $this->get(['status'=>1,'area_id'=>$id]);
	}
	
	/**
	 * 获取地区
	 */
	public function getFieldsById($id,$fileds){
		return $this->where(['status'=>1,'area_id'=>$id])->field($fileds)->find();
	}
	
	/**
	 * 显示是否显示/隐藏
	 */
	public function editiIsShow(){
		//获取子集
		$ids = array();
		$ids[] = input('post.id/d',0);
		$ids = $this->getChild($ids,$ids);
		$is_show = input('post.is_show/d',0)?0:1;
		$result = $this->where("area_id in(".implode(',',$ids).")")->update(['is_show' => $is_show]);
		if(false !== $result){
			return FIReturn("操作成功", 1);
		}else{
			return FIReturn($this->getError(),-1);
		}
	}
	
	/**
	 * 迭代获取下级
	 */
	public function getChild($ids = array(),$pids = array()){
		$result = $this->where("status=1 and parent_id in(".implode(',',$pids).")")->select();
		if(count($result)>0){
			$cids = array();
			foreach ($result as $key =>$v){
				$cids[] = $v['area_id'];
			}
			$ids = array_merge($ids,$cids);
			return $this->getChild($ids,$cids);
		}else{
			return $ids;
		}
	}
	
    /**
	 * 根据子分类获取其父级分类
	 */
	public function getParentIs($id,$data = array()){
		$data[] = $id;
		$parent_id = $this->where('area_id',$id)->value('parent_id');
		if($parent_id==0){
			krsort($data);
			return $data;
		}else{
			return $this->getParentIs($parent_id, $data);
		}
	}
	
	/**
	 * 排序字母
	 */
	public function letterObtain(){
		$area_name =  input('code');
		if($area_name =='')return FIReturn("", 1);
		$area_name = FIGetFirstCharter($area_name);
		if($area_name){
			return FIReturn($area_name, 1);
		}else{
			return FIReturn("", 1);
		}
	}
	
	/**
	 * 新增
	 */
	public function add(){
		$area_type = 0;
		$parent_id = input('post.parent_id/d',0);
		if($parent_id>0){
			$prs = $this->getFieldsById($parent_id,['area_type']);
			$area_type = $prs['area_type']+1;
		}
		$data = input('post.');
		FIUnset($data,'area_id,status');
		$data['area_type'] = $area_type;
		$data['create_time'] = date('Y-m-d H:i:s');
		$result = $this->validate('Areas.add')->allowField(true)->save($data);
		if(false !== $result){
			return FIReturn("新增成功", 1);
		}else{
			return FIReturn($this->getError(),-1);
		}
	}
	
	/**
	 * 编辑
	 */
	public function edit(){
		$area_id = input('post.area_id/d');
		$result = $this->validate('Areas.edit')->allowField(['area_name','is_show','area_sort','area_key'])->save(input('post.'),['area_id'=>$area_id]);
		$ids = array();
		$ids[] = $area_id;
		$ids = $this->getChild($ids,$ids);
		$this->where("area_id in(".implode(',',$ids).")")->update(['is_show' => input('post.')['is_show']]);
		if(false !== $result){
			return FIReturn("修改成功", 1);
		}else{
			return FIReturn($this->getError(),-1);
		}
	}
	
	/**
	 * 删除
	 */
	public function del(){
		$ids = array();
		$ids[] = input('post.id/d');
		$ids = $this->getChild($ids,$ids);
		$data = [];
		$data['status'] = -1;
		$result = $this->where("area_id in(".implode(',',$ids).")")->update($data);
		if(false !== $result){
			return FIReturn("删除成功", 1);
		}else{
			return FIReturn($this->getError(),-1);
		}
	}
	
	/**
	 *  获取地区列表
	 */
	public function listQuery($parent_id){
		return $this->where(['status'=>1,'parent_id'=>$parent_id,'is_show'=>1])->field('area_id,area_name,parent_id')->order('area_sort desc')->select();
	}

	
}