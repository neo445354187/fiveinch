<?php
namespace fi\common\model;
/**
 * 门店分类
 */
class ShopCats extends Base{
	
	/**
	 * 批量保存商品分类
	 */
	public function batchSaveCats(){
		
		$shop_id = (int)session('FI_USER.shop_id');
		$create_time = date("Y-m-d H:i:s");
		//先保存了已经有父级的分类
		$otherNo = input('post.otherNo/d');
		for($i=0;$i<$otherNo;$i++){
			
			$data = array();
			$data['cat_name'] = input('post.cat_name_o_'.$i);
			if($data['cat_name']=='')continue;
			$data['shop_id'] = $shop_id;
			$data['parent_id'] = input('post.cat_id_o_'.$i."/d");
			$data['cat_sort'] = input('post.cat_sort_o_'.$i."/d");
			$data['is_show'] = input('post.catShow_o_'.$i."/d");
			$data['create_time'] = $create_time;
			$rs = $this->where(["status"=>1,"shop_id"=>$shop_id,"cat_id"=>$data['parent_id']])->find();
			if(empty($rs))continue;
			$this->isUpdate(false)->allowField(["cat_name","shop_id","parent_id","cat_sort","is_show"])->save($data);
		}
		
		//保存没有父级分类的
		$fristNo = input('post.fristNo/d');
	    for($i=0;$i<$fristNo;$i++){
			$data = array();
			
			$data['cat_name'] = input('post.cat_name_'.$i);
			if($data['cat_name']=='')continue;
			$data['parent_id'] = 0;
			$data['shop_id'] = $shop_id;
			$data['cat_sort'] = input('post.cat_sort_'.$i."/d");
			$data['is_show'] = input('post.catShow_'.$i."/d");
			$data['create_time'] = $create_time;
			$parent_id = $this->isUpdate(false)->allowField(["cat_name","shop_id","parent_id","cat_sort","is_show"])->save($data);
			$parent_id = $this->cat_id;
			if(false !== $parent_id){
				//新增子类
				$catSecondNo = (int)input('post.catSecondNo_'.$i."/d");
		        for($j=0;$j<$catSecondNo;$j++){
					$data = array();
					$data['cat_name'] = input('post.cat_name_'.$i."_".$j);
					if($data['cat_name']=='')continue;
					$data['shop_id'] = $shop_id;
					$data['parent_id'] = $parent_id;
					$data['cat_sort'] = input('post.cat_sort_'.$i."_".$j."/d");
					$data['is_show'] = input('post.catShow_'.$i."_".$j."/d");
					$data['create_time'] = $create_time;
					$this->isUpdate(false)->allowField(["cat_name","shop_id","parent_id","cat_sort","is_show"])->save($data);
			    }
			}
		}
		return FIReturn("",1);
	}
	
	 /**
	  * 修改名称
	  */
	 public function editName(){
	 	$rd = array('status'=>-1);
	 	$id = input("post.id/d");
		$data = array();
		$data["cat_name"] = input("cat_name");
		$shop_id = (int)session('FI_USER.shop_id');
		
		$rs = $this->validate("ShopCats.edit")->save($data,["cat_id"=>$id,"shop_id"=>$shop_id]);
		if(false !== $rs){
			return FIReturn("",1);
		}
		return FIReturn($this->getError());
	 } 
	 /**
	  * 修改排序号
	  */
	 public function editSort(){
	 	$rd = array('status'=>-1);
	 	$id = input("post.id/d");
		$data = array();
		$data["cat_sort"] = input("post.cat_sort/d");
		$shop_id = (int)session('FI_USER.shop_id');
		$rs = $this->save($data,["cat_id"=>$id,"shop_id"=>$shop_id]);
		if(false !== $rs){
			return FIReturn("",1);
		}
		return FIReturn($this->getError());
	 } 
	 /**
	  * 获取指定对象
	  */
     public function getById($id){
		return $this->where(["cat_id"=>(int)$id])->find();
	 }
	 
	 /**
	  * 获取树形分类
	  */
	public function getCatAndChild($shop_id){
	 	 //获取第一级分类
	 	 $rs1 = $this->where(['shop_id'=>$shop_id,'status'=>1,'parent_id'=>0])->order('cat_sort asc')->select();
	 	 if(count($rs1)>0){
	 	 	$ids = array();
	 	 	foreach ($rs1 as $key => $v){
	 	 		$ids[] = $v['cat_id'];
	 	 	}
	 	 	$rs2 = $this->where(['shop_id'=>$shop_id,'status'=>1])
	 	 				->where('parent_id', 'in', implode(',',$ids))
	 	 				->order('cat_sort asc,cat_id asc')->select();
	 	 	if(count($rs2)>0){
	 	 		$tmpArr = array();
	 	 		foreach ($rs2 as $key => $v){
	 	 			$tmpArr[$v['parent_id']][] = $v;
	 	 		}
	 	 		foreach ($rs1 as $key => $v){
	 	 			$rs1[$key]['child'] = array_key_exists($v['cat_id'],$tmpArr)?$tmpArr[$v['cat_id']]:null;
	 	 			$rs1[$key]['childNum'] = array_key_exists($v['cat_id'],$tmpArr)?count($tmpArr[$v['cat_id']]):0;;
	 	 		}
	 	 	}
		}
		return $rs1;
	}
	 
	/**
	* 获取列表
	*/
	public function listQuery($shop_id,$parent_id){
		$rs = $this->where(['shop_id'=>$shop_id,'status'=>1,'is_show'=>1,'parent_id'=>$parent_id,'shop_id'=>$shop_id])
				   ->order('cat_sort asc')->select();
		return $rs;
	}
	  
	 /**
	  * 删除
	  */
	 public function del(){
	 	$id = input("post.id/d");
	 	if($id==0)return $rd;
		$shop_id = (int)session('FI_USER.shop_id');
		//把相关的商品下架了
		$data = array();
		$data['is_sale'] = 0;
		$gm = new \fi\home\model\Goods();
		$gm->save($data,['shop_id'=>$shop_id,"shop_cat_id1"=>$id]);
		$gm->save($data,['shop_id'=>$shop_id,"shop_cat_id2"=>$id]);
		//删除商品分类
		$data = array();
		$data["status"] = -1;
	 	$rs = $this->where("cat_id|parent_id",$id)->where(["shop_id"=>$shop_id])->update($data);
	    if(false !== $rs){
			return FIReturn("",1);
		}else{
			return FIReturn($this->getError());
		}
		
	 }
	 
	 
	/**
	  * 获取店铺商品分类列表
	*/
    public function getShopCats($shop_id = 0){
		$data = [];
		if(!$data){
			$data = $this->field("cat_id,parent_id,cat_name,shop_id")->where(["shop_id"=>$shop_id,"parent_id"=>0,"is_show"=>1 ,"status"=>1])->order("cat_sort asc")->select();
			if(count($data)>0){
				$ids = array();
				foreach ($data as $v){
					$ids[] = $v['cat_id'];
				}
				
				$crs = $this->field("cat_id,parent_id,cat_name,shop_id")
							->where(["shop_id"=>$shop_id,"is_show"=>1 ,"status"=>1])
							->where("parent_id","in",implode(',',$ids))
							->order("cat_sort asc")->select();
				$ids = array();
			    foreach ($crs as $v){
			    	$ids[$v['parent_id']][] = $v;
				}
				foreach ($data as $key =>$v){
					$data[$key]['children'] = '';
					if(isset($ids[$v['cat_id']])){
						$data[$key]['children'] = $ids[$v['cat_id']];
					}
				}
			}
	    }
		return $data;
	}
	
	/**
	 * 显示状态
	 */
	public function changeCatStatus(){
		$id = input("post.id/d");
		$is_show = input("post.is_show/d");
		$parent_id = input("post.pid/d");
		$data = array();
		$data["is_show"] = $is_show;
		$shop_id = (int)session('FI_USER.shop_id');

		$this->save($data,["cat_id"=>$id,"shop_id"=>$shop_id]);
		$this->save($data,["parent_id"=>$id,"shop_id"=>$shop_id]);
		if($parent_id>0 && $is_show==1){
			$this->save($data,["cat_id"=>$parent_id,"shop_id"=>$shop_id]);
		}
		//如果是隐藏的话还要下架的商品
		if($is_show==0){
			$gm = new \fi\home\model\Goods();
			$data = array();
			$data["is_sale"] = 0;
			$gm->save($data,["shop_id"=>$shop_id,"shop_cat_id1|shop_cat_id2"=>['=',$id]]);
		}
		return FIReturn("",1);
	}
	
	 /**
     * 获取自营店铺首页楼层
     */
    public function getFloors(){
    	$shop_id = (int)input('shop_id');
	    $cats1 = $this->where(['status'=>1, 'is_show' => 1,'parent_id'=>0,'shop_id'=>$shop_id])
		             ->field("cat_name,cat_id")->order('cat_sort asc')->select();
		if(!empty($cats1)){
			$ids = [];
			foreach ($cats1 as $key =>$v){
				$ids[] = $v['cat_id'];
			}
			$cats2 = [];
			$rs = $this->where(['status'=>1, 'is_show' => 1,'parent_id'=>['in',$ids],'shop_id'=>$shop_id])
				             ->field("parent_id,cat_name,cat_id")->order('cat_sort asc')->select();
			foreach ($rs as $key => $v){
				$cats2[$v['parent_id']][] = $v;
			}
			foreach ($cats1 as $key =>$v){
				$cats1[$key]['children'] = (isset($cats2[$v['cat_id']]))?$cats2[$v['cat_id']]:[];
			}
		}
		return $cats1;
    }
}
