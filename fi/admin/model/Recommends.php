<?php
namespace fi\admin\model;
/**
 * 推荐业务处理
 */
use think\Db;
class Recommends extends Base{
	/**
	 * 获取已推荐商品
	 */
	public function listQueryByGoods(){
		$data_type = (int)input('post.data_type');
	    $goods_cat_id = (int)input('post.goods_cat_id');
		$rs = $this->alias('r')->join('__GOODS__ g','r.data_id=g.goods_id','inner')
		           ->join('__SHOPS__ s','s.shop_id=g.shop_id','inner')
		           ->where(['data_src'=>0,'data_type'=>$data_type,'r.goods_cat_id'=>$goods_cat_id])
		           ->field('data_id,goods_name,shop_name,data_sort,is_sale,g.status,goods_status')->order('data_sort asc')->select();
		$data = [];
		foreach ($rs as $key => $v){
			if($v['is_sale']!=1 || $v['status']!=1 || $v['goods_status']!=1)$v['invalid'] = true;
			$data[] = $v;
		}   
		return $data;        
	}
	/**
	 * 推荐商品
	 */
    public function editGoods(){
	    $ids = input('post.ids');
	    $data_type = (int)input('post.data_type');
	    $goods_cat_id = (int)input('post.goods_cat_id');
	    if($ids=='')return FIReturn("请选择要推荐的商品");
	    $ids = explode(',',$ids);
	    //查看商品是否有效
	    $rs = Db::table('__GOODS__')->where(['goods_status'=>1,'status'=>1,'goods_id'=>['in',$ids]])->field('goods_id')->select();
	    if(!$rs)return FIReturn("请选择要推荐的商品");
	    Db::startTrans();
	    try{
		    $this->where(['data_src'=>0,'data_type'=>$data_type,'goods_cat_id'=>$goods_cat_id])->delete();
		    $data = [];
		    foreach ($rs as $key => $v){
		    	$tmp = [];
		    	$tmp['goods_cat_id'] = $goods_cat_id;
		    	$tmp['data_src'] = 0;
		    	$tmp['data_type'] = $data_type;
		    	$tmp['data_id'] = $v['goods_id'];
		    	$tmp['data_sort'] = (int)input('post.ipt'.$v['goods_id']);
		    	$data[] = $tmp;
		    }
		    $this->saveAll($data);
		    Db::commit();
	        return FIReturn("提交成功", 1);
	    }catch(\Exception $e) {
            Db::rollback();
            return FIReturn('提交失败',-1);
        }
	}
	
	
    /**
	 * 获取已推荐店铺
	 */
	public function listQueryByShops(){
		$data_type = (int)input('post.data_type');
	    $goods_cat_id = (int)input('post.goods_cat_id');
		$rs = $this->alias('r')->join('__SHOPS__ s','r.data_id=s.shop_id','inner')
		           ->where(['data_src'=>1,'data_type'=>$data_type,'r.goods_cat_id'=>$goods_cat_id])
		           ->field('data_id,shop_sn,shop_name,data_sort,shop_status,status')->order('data_sort asc')->select();
		$data = [];
		foreach ($rs as $key => $v){
			if($v['status']!=1 || $v['shop_status']!=1)$v['invalid'] = true;
			$data[] = $v;
		}   
		return $data;        
	}
    /**
	 * 推荐店铺
	 */
    public function editShops(){
	    $ids = input('post.ids');
	    $data_type = (int)input('post.data_type');
	    $goods_cat_id = (int)input('post.goods_cat_id');
	    if($ids=='')return FIReturn("请选择要推荐的店铺");
	    $ids = explode(',',$ids);
	    //查看商品是否有效
	    $rs = Db::table('__SHOPS__')->where(['shop_status'=>1,'status'=>1,'shop_id'=>['in',$ids]])->field('shop_id')->select();
	    if(!$rs)return FIReturn("请选择要推荐的店铺");
	    Db::startTrans();
	    try{
		    $this->where(['data_src'=>1,'data_type'=>$data_type,'goods_cat_id'=>$goods_cat_id])->delete();
		    $data = [];
		    foreach ($rs as $key => $v){
		    	$tmp = [];
		    	$tmp['goods_cat_id'] = $goods_cat_id;
		    	$tmp['data_src'] = 1;
		    	$tmp['data_type'] = $data_type;
		    	$tmp['data_id'] = $v['shop_id'];
		    	$tmp['data_sort'] = (int)input('post.ipt'.$v['shop_id']);
		    	$data[] = $tmp;
		    }
		    $this->saveAll($data);
		    Db::commit();
	        return FIReturn("提交成功", 1);
	    }catch(\Exception $e) {
            Db::rollback();
            print_r($e);
            return FIReturn('提交失败',-1);
        }
	}
	
	
    /**
	 * 获取已推荐品牌
	 */
	public function listQueryByBrands(){
		$data_type = (int)input('post.data_type');
	    $goods_cat_id = (int)input('post.goods_cat_id');
		$rs = $this->alias('r')->join('__BRANDS__ s','r.data_id=s.brand_id','inner')
		           ->where(['data_src'=>2,'data_type'=>$data_type,'r.goods_cat_id'=>$goods_cat_id])
		           ->field('data_id,brand_name,data_sort,status')->order('data_sort asc')->select();
		$data = [];
		foreach ($rs as $key => $v){
			if($v['status']!=1)$v['invalid'] = true;
			$data[] = $v;
		}   
		return $data;        
	}
    /**
	 * 推荐品牌
	 */
    public function editBrands(){
	    $ids = input('post.ids');
	    $data_type = (int)input('post.data_type');
	    $goods_cat_id = (int)input('post.goods_cat_id');
	    if($ids=='')return FIReturn("请选择要推荐的品牌");
	    $ids = explode(',',$ids);
	    //查看商品是否有效
	    $rs = Db::table('__BRANDS__')->where(['status'=>1,'brand_id'=>['in',$ids]])->field('brand_id')->select();
	    if(!$rs)return FIReturn("请选择要推荐的品牌");
	    Db::startTrans();
	    try{
		    $this->where(['data_src'=>2,'data_type'=>$data_type,'goods_cat_id'=>$goods_cat_id])->delete();
		    $data = [];
		    foreach ($rs as $key => $v){
		    	$tmp = [];
		    	$tmp['goods_cat_id'] = $goods_cat_id;
		    	$tmp['data_src'] = 2;
		    	$tmp['data_type'] = $data_type;
		    	$tmp['data_id'] = $v['brand_id'];
		    	$tmp['data_sort'] = (int)input('post.ipt'.$v['brand_id']);
		    	$data[] = $tmp;
		    }
		    $this->saveAll($data);
		    Db::commit();
	        return FIReturn("提交成功", 1);
	    }catch(\Exception $e) {
            Db::rollback();
            print_r($e);
            return FIReturn('提交失败',-1);
        }
	}
}
