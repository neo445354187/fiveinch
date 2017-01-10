<?php
namespace fi\admin\model;
/**
 * 规格业务处理
 */
class Attributes extends Base{
	
	/**
	 * 新增
	 */
	public function add(){
		$data = input('post.');
		FIUnset($data, 'attr_id,status');
		$data['create_time'] = date('Y-m-d H:i:s');
		$data['attr_val'] = str_replace('，',',',$data['attr_val']); 
		$data["status"] = 1;
		$goodsCats = model('GoodsCats')->getParentIs($data['goods_cat_id']);
		krsort($goodsCats);
		if(!empty($goodsCats))$data['goods_cat_path'] = implode('_',$goodsCats)."_";
		$result = $this->validate('Attributes.add')->allowField(true)->save($data);
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
		$attr_id = input('post.attr_id/d');
		$data = input('post.');
		FIUnset($data, 'attr_id,status,create_time');
		$data['attr_val'] = str_replace('，',',',$data['attr_val']); 
		$goodsCats = model('GoodsCats')->getParentIs($data['goods_cat_id']);
		krsort($goodsCats);
		if(!empty($goodsCats))$data['goods_cat_path'] = implode('_',$goodsCats)."_";
	    $result = $this->validate('Attributes.edit')->allowField(true)->save($data,['attr_id'=>$attr_id]);
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
	    $attr_id = input('post.attr_id/d');
	    $data["status"] = -1;
	  	$result = $this->save($data,['attr_id'=>$attr_id]);
        if(false !== $result){
        	return FIReturn("删除成功", 1);
        }else{
        	return FIReturn($this->getError(),-1);
        }
	}
	
	/**
	 * 
	 * 根据ID获取
	 */
	public function getById($attr_id){
		$obj = null;
		if($attr_id>0){
			$obj = $this->get(['attr_id'=>$attr_id,'status'=>1]);
		}else{
			$obj = self::getEModel("attributes");
		}
		return $obj;
	}
	
	/**
	 * 显示隐藏
	 */
	public function setToggle(){
		$attr_id = input('post.attr_id/d');
		$is_show = input('post.is_show/d');
		$result = $this->where(['attr_id'=>$attr_id,"status"=>1])->setField("is_show", $is_show);
		if(false !== $result){
			return FIReturn("设置成功", 1);
		}else{
			return FIReturn($this->getError(),-1);
		}
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
		$page = $dbo->field(true)->where($map)->paginate(input('pagesize/d'))->toArray();
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
	
	/**
	 * 列表
	 */
	public function listQuery(){
		$cat_id = input("post.cat_id/d");
		$rs = $this->field("attr_id id, attr_id, cat_id, attr_name name,  '' goodsCatNames")->where(["status"=>1,"cat_id"=>$cat_id])->sort('attr_sort asc,attr_id asc')->select();
		return $rs;
	}
}
