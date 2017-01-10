<?php
namespace fi\common\model;
use think\Db;
use fi\home\model\Shops;
/**
 * 收藏类
 */
class Favorites extends Base{
	/**
	 * 关注的商品列表
	 */
	public function listGoodsQuery(){
		$pagesize = input("param.pagesize/d");
		$user_id = (int)session('FI_USER.user_id');
		$page = Db::table("__FAVORITES__")->alias('f')
    	->join('__GOODS__ g','g.goods_id = f.target_id','left')
    	->join('__SHOPS__ s','s.shop_id = g.shop_id','left')
    	->field('f.favorite_id,f.target_id,g.goods_id,g.goods_name,g.goods_img,g.shop_price,g.market_price,g.sale_num,g.appraise_num,s.shop_id,s.shop_name')
    	->where(['f.user_id'=> $user_id,'favorite_type'=> 0])
    	->order('f.favorite_id desc')
    	->paginate($pagesize)->toArray();
		foreach ($page['Rows'] as $key =>$v){
			//认证
			$shop = new Shops();
			$accreds = $shop->shopAccreds($v["shop_id"]);
			$page['Rows'][$key]['accreds'] = $accreds;
		}
		return $page;
	}
	/**
	 * 关注的店铺列表
	 */
	public function listShopQuery(){
		$pagesize = input("param.pagesize/d");
		$user_id = (int)session('FI_USER.user_id');
		$page = Db::table("__FAVORITES__")->alias('f')
		->join('__SHOPS__ s','s.shop_id = f.target_id','left')
		->field('f.favorite_id,f.target_id,s.shop_id,s.shop_name,s.shop_img')
		->where(['f.user_id'=> $user_id,'favorite_type'=> 1])
		->order('f.favorite_id desc')
		->paginate($pagesize)->toArray();
		foreach ($page['Rows'] as $key =>$v){
			//商品列表
			$goods = db('goods')->where(['status'=> 1,'is_sale'=>1,'shop_id'=> $v["shop_id"]])->field('goods_id,goods_name,shop_price,goods_img')
			->limit(10)->order('sale_time desc')->select();
			$page['Rows'][$key]['goods'] = $goods;
		}
		return $page;
	}
	/**
	 * 取消关注
	 */
	public function del(){
		$id = input("param.id/d");
		$type = input("param.type/d");
		$user_id = (int)session('FI_USER.user_id');
		if(!$id)return FIReturn("取消失败", -1);
		$rs = $this->where(['favorite_id'=> $id,'favorite_type'=> $type,'user_id'=>$user_id])->delete();
		if(false !== $rs){
			return FIReturn("取消成功", 1);
		}else{
			return FIReturn($this->getError(),-1);
		}
	}
	
	/**
	 * 新增关注
	 */
	public function add(){
	    $id = input("param.id/d");
		$type = input("param.type/d");
		$user_id = (int)session('FI_USER.user_id');
		//判断记录是否存在
		$isFind = false;
		if($type==0){
			$c = Db::table('__GOODS__')->where(['goods_status'=>1,'status'=>1,'goods_id'=>$id])->count();
			$isFind = ($c>0);
		}else{
			$c = Db::table('__SHOPS__')->where(['shop_status'=>1,'status'=>1,'shop_id'=>$id])->count();
			$isFind = ($c>0);
		}
		if(!$isFind)return FIReturn("关注失败，无效的关注对象", -1);
		$data = [];
		$data['user_id'] = $user_id;
		$data['favorite_type'] = $type;
		$data['target_id'] = $id;
		//判断是否已关注
		$rc = $this->where($data)->count();
		if($rc>0)return FIReturn("关注成功", 1);
		$data['create_time'] = date('Y-m-d H:i:s');
		$rs = $this->save($data);
		if(false !== $rs){
			return FIReturn("关注成功", 1);
		}else{
			return FIReturn($this->getError(),-1);
		}
	}
	/**
	 * 判断是否已关注
	 */
	public function checkFavorite($id,$type){
		$rs = $this->where(['user_id'=>(int)session('FI_USER.user_id'),'favorite_type'=>$type,'target_id'=>$id])->find();
		return empty($rs)?0:$rs['favorite_id'];
	}
}
