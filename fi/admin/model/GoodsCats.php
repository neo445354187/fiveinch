<?php
namespace fi\admin\model;
/**
 * 商品分类业务处理
 */
use think\Db;
class GoodsCats extends Base{
	/**
	 * 获取树形分类
	 */
	public function pageQuery(){
		return $this->where(['status'=>1,'parent_id'=>input('cat_id/d',0)])->order('cat_sort asc,cat_id desc')->paginate(input('post.pagesize/d'))->toArray();
	}
	/**
	 * 获取列表
	 */
	public function listQuery($parent_id){
		return $this->where(['status'=>1,'parent_id'=>$parent_id])->order('cat_sort asc,cat_name asc')->select();
	}
	
	/**
	 *获取商品分类名值对
	 */
	public function listKeyAll(){
		$rs = $this->field("cat_id,cat_name")->where(['status'=>1])->order('cat_sort asc,cat_name asc')->select();
		$data = array();
		foreach ($rs as $key => $cat) {
			$data[$cat["cat_id"]] = $cat["cat_name"];
		}
		return $data;
	}
	
	/**
	 *	获取树形分类
	 */
	public function getTree($data, $parent_id=0){
		$arr = array();
		foreach($data as $k=>$v)
		{
			if($v['parent_id']==$parent_id && $v['status']==1)
			{
				//再查找该分类下是否还有子分类
				$v['child'] = $this->getTree($data, $v['cat_id']);
				//统计child
				$v['childNum'] = count($v['child']);
				//将找到的分类放回该数组中
				$arr[]=$v;
			}
		}
		return $arr;
	}
	
	/**
	 * 迭代获取下级
	 * 获取一个分类下的所有子级分类id
	 */
	public function getChild($pid){
		$data = $this->where("status=1")->select();
		//获取该分类id下的所有子级分类id
		$ids = $this->_getChild($data, $pid, true);//每次调用都清空一次数组
		//把自己也放进来
		array_unshift($ids, $pid);
		return $ids;
	}
	
	public function _getChild($data, $pid, $isClear=false){
		static $ids = array();
		if($isClear)//是否清空数组
			$ids = array();
		foreach($data as $k=>$v)
		{
			if($v['parent_id']==$pid && $v['status']==1)
			{
				$ids[] = $v['cat_id'];//将找到的下级分类id放入静态数组
				//再找下当前id是否还存在下级id
				$this->_getChild($data, $v['cat_id']);
			}
		}
		return $ids;
	}
	
	/**
	 * 获取指定对象
	 */
	public function getGoodscats($id){
		return $this->where(['cat_id'=>$id])->find();
	}
	 
	 /**
	  * 显示是否推荐/不推荐
	  */
	 public function editiIsFloor(){
	    $ids = array();
		$id = input('post.id/d');
		$ids = $this->getChild($id);
	 	$is_floor = input('post.is_floor/d')?1:0;
	 	$result = $this->where("cat_id in(".implode(',',$ids).")")->update(['is_floor' => $is_floor]);
	 	if(false !== $result){
	 		return FIReturn("操作成功", 1);
	 	}else{
	 		return FIReturn($this->getError(),-1);
	 	}
	 }
	
	/**
	 * 显示是否显示/隐藏
	 */
	public function editiIsShow(){
		$ids = array();
		$id = input('post.id/d');
		$ids = $this->getChild($id);
		$is_show = input('post.is_show/d')?1:0;
		Db::startTrans();
        try{
			$result = $this->where("cat_id in(".implode(',',$ids).")")->update(['is_show' => $is_show]);
			if(false !== $result){
				if($is_show==0){
					//删除购物车里的相关商品
					$goods = Db::table('__GOODS__')->where(["goods_cat_id"=>['in',$ids],'is_sale'=>1])->field('goods_id')->select();
					if(count($goods)>0){
						$goods_ids = [];
						foreach ($goods as $key =>$v){
							$goods_ids[] = $v['goods_id'];
						}
						Db::table('__CARTS__')->where(['goods_id'=>['in',$goods_ids]])->delete();
					}
					//把相关的商品下架了
					Db::table('__GOODS__')->where("goods_cat_id in(".implode(',',$ids).")")->update(['is_sale' => 0]);
				}
		    }
		    Db::commit();
	        return FIReturn("操作成功", 1);
        }catch (\Exception $e) {
            Db::rollback();
            return FIReturn('删除失败',-1);
        }
			
	}
	
	/**
	 * 新增
	 */
	public function add(){
		$parent_id = input('post.parent_id/d');
		$data = input('post.');
		FIUnset($data,'cat_id,status');
		$data['parent_id'] = $parent_id;
		$data['create_time'] = date('Y-m-d H:i:s');
		$result = $this->validate('GoodsCats.add')->allowField(true)->save($data);
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
		$cat_id = input('post.id/d');
		$data = input('post.');
		FIUnset($data,'cat_id,status,create_time');
		$result = $this->validate('GoodsCats.edit')->allowField(true)->save($data,['cat_id'=>$cat_id]);
		$ids = array();
		$ids = $this->getChild($cat_id);
		$this->where("cat_id in(".implode(',',$ids).")")->update(['is_show' => (int)$data['is_show'],'is_floor'=> $data['is_floor']]);
		if(false !== $result){
			if($data['is_show']==0){
				//删除购物车里的相关商品
				$goods = Db::table('__GOODS__')->where(["goods_cat_id"=>['in',$ids],'is_sale'=>1])->field('goods_id')->select();
				if(count($goods)>0){
					$goods_ids = [];
					foreach ($goods as $key =>$v){
							$goods_ids[] = $v['goods_id'];
					}
					Db::table('__CARTS__')->where(['goods_id'=>['in',$goods_ids]])->delete();
				}
		    	//把相关的商品下架了
		        Db::table('__GOODS__')->where("goods_cat_id in(".implode(',',$ids).")")->update(['is_sale' => 0]);
			}
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
		$id = input('post.id/d');
		$ids = $this->getChild($id);
		Db::startTrans();
        try{
		    $data = [];
		    $data['status'] = -1;
		    $result = $this->where("cat_id in(".implode(',',$ids).")")->update($data);
		    if(false !== $result){
		        //删除购物车里的相关商品
				$goods = Db::table('__GOODS__')->where(["goods_cat_id"=>['in',$ids],'is_sale'=>1])->field('goods_id')->select();
				if(count($goods)>0){
					$goods_ids = [];
					foreach ($goods as $key =>$v){
							$goods_ids[] = $v['goods_id'];
					}
					Db::table('__CARTS__')->where(['goods_id'=>['in',$goods_ids]])->delete();
				}
		    	//把相关的商品下架了
		        Db::table('__GOODS__')->where("goods_cat_id in(".implode(',',$ids).")")->update(['is_sale' => 0]);
		    }
            Db::commit();
	        return FIReturn("删除成功", 1);
        }catch (\Exception $e) {
            Db::rollback();
            return FIReturn('删除失败',-1);
        }
	}
	
    /**
	 * 根据子分类获取其父级分类
	 */
	public function getParentIs($id,$data = array()){
		$data[] = $id;
		$parent_id = $this->where('cat_id',$id)->value('parent_id');
		if($parent_id==0){
			krsort($data);
			return $data;
		}else{
			return $this->getParentIs($parent_id, $data);
		}
	}
}