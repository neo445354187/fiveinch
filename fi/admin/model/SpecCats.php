<?php
namespace fi\admin\model;
/**
 * 规格分类业务处理
 */
class SpecCats extends Base{
	
	/**
	 * 新增
	 */
	public function add(){
		$isExistAllowImg = false;
		$msg = '';
		$data = input('post.');
		if($data['is_allow_img']==1){
			if($this->checkExistAllowImg((int)$data['goods_cat_id'],0)){
				return FIReturn("同一分类下已存在允许上传图片规格类型，请先修改之后再新增");
			}
		}
		$data['create_time'] = date('Y-m-d H:i:s');
		$data["status"] = 1;
		$goodsCats = model('GoodsCats')->getParentIs($data['goods_cat_id']);
		if(!empty($goodsCats))$data['goods_cat_path'] = implode('_',$goodsCats)."_";
		$result = $this->validate('SpecCats.add')->allowField(['cat_name','is_show','is_allow_img','goods_cat_path','goods_cat_id','status','create_time'])->save($data);
        if(false !== $result){
        	return FIReturn("新增成功", 1);
        }else{
        	return FIReturn($this->getError(),-1);
        }
	}
	/**
	 * 检测是否存在统一分类下的上传图片分类
	 */
	public function checkExistAllowImg($goods_cat_id,$cat_id){
		$dbo = $this->where(['goods_cat_id'=>$goods_cat_id,'status'=>1,'is_allow_img'=>1]);
		if($cat_id>0)$dbo->where('cat_id','<>',$cat_id);
		$rs = $dbo->count();
		if($rs>0)return true;
		return false;
	}
    /**
	 * 编辑
	 */
	public function edit(){
		$cat_id = input('post.cat_id/d');
		$data = input('post.');
	    if($data['is_allow_img']==1){
			if($this->checkExistAllowImg((int)$data['goods_cat_id'],$cat_id)){
				return FIReturn("同一分类下已存在允许上传图片规格类型，请先修改之后再保存");
			}
		}
		$goodsCats = model('GoodsCats')->getParentIs($data['goods_cat_id']);
		if(!empty($goodsCats))$data['goods_cat_path'] = implode('_',$goodsCats)."_";
	    $result = $this->validate('SpecCats.edit')->allowField(['cat_name','goods_cat_path','goods_cat_id','is_show','is_allow_img'])->save($data,['cat_id'=>$cat_id,"status"=>1]);
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
	    $cat_id = input('post.cat_id/d');
	    $data["status"] = -1;
	  	$result = $this->save($data,['cat_id'=>$cat_id]);
        if(false !== $result){
        	return FIReturn("删除成功", 1);
        }else{
        	return FIReturn($this->getError(),-1);
        }
	}
	
	/**
	 * 显示隐藏
	 */
	public function setToggle(){
		$cat_id = input('post.cat_id/d');
		$is_show = input('post.is_show/d');
		$result = $this->where(['cat_id'=>$cat_id,"status"=>1])->setField("is_show", $is_show);
		if(false !== $result){
			return FIReturn("设置成功", 1);
		}else{
			return FIReturn($this->getError(),-1);
		}
	}
	
	/**
	 * 
	 * 根据ID获取
	 */
	public function getById($cat_id){
		$obj = null;
		if($cat_id>0){
			$obj = $this->get(['cat_id'=>$cat_id,"status"=>1]);
		}else{
			$obj = self::getEModel("spec_cats");
		}
		return $obj;
	}
	
	
	/**
	 * 分页
	 */
	public function pageQuery(){
		$keyName = input('get.keyName');
		$goods_cat_path = input('get.goods_cat_path');
		$dbo = $this->field(true);
		$map = array();
		$map['status']  = 1;
		if($keyName!="")$map['cat_name']  = ["like","%".$keyName."%"];
		if($goods_cat_path!='')$map['goods_cat_path']  = ["like",$goods_cat_path."_%"];
		$dbo = $dbo->field("cat_name name, cat_id id, is_show ,is_allow_img")->where($map);
		$page = $dbo->order('cat_sort asc,cat_id asc')->paginate(input('pagesize/d'))->toArray();
		if(count($page['Rows'])>0){
			$keyCats = model('GoodsCats')->listKeyAll();
			foreach ($page['Rows'] as $key => $v){
				$goods_cat_path = $page['Rows'][$key]['goods_cat_path'];
				$page['Rows'][$key]['goodsCatNames'] = self::getGoodsCatNames($goods_cat_path,$keyCats);
				$page['Rows'][$key]['children'] = [];
				$page['Rows'][$key]['isextend'] = false;
			}
		}
		return $page;
	}
	
	public function getGoodsCatNames($goods_cat_path, $keyCats){
		$cat_ids = explode("_",$goods_cat_path);
		$cat_names = array();
		for($i=0,$k=count($cat_ids);$i<$k;$i++){
			if($cat_ids[$i]=='')continue;
			$cat_names[] = $keyCats[$cat_ids[$i]];
		}
		return implode("→",$cat_names);
	}
}
