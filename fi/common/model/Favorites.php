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
		$userId = (int)session('FI_USER.userId');
		$page = Db::table("__FAVORITES__")->alias('f')
    	->join('__GOODS__ g','g.goodsId = f.targetId','left')
    	->join('__SHOPS__ s','s.shopId = g.shopId','left')
    	->field('f.favoriteId,f.targetId,g.goodsId,g.goodsName,g.goodsImg,g.shopPrice,g.marketPrice,g.saleNum,g.appraiseNum,s.shopId,s.shopName')
    	->where(['f.userId'=> $userId,'favoriteType'=> 0])
    	->order('f.favoriteId desc')
    	->paginate($pagesize)->toArray();
		foreach ($page['Rows'] as $key =>$v){
			//认证
			$shop = new Shops();
			$accreds = $shop->shopAccreds($v["shopId"]);
			$page['Rows'][$key]['accreds'] = $accreds;
		}
		return $page;
	}
	/**
	 * 关注的店铺列表
	 */
	public function listShopQuery(){
		$pagesize = input("param.pagesize/d");
		$userId = (int)session('FI_USER.userId');
		$page = Db::table("__FAVORITES__")->alias('f')
		->join('__SHOPS__ s','s.shopId = f.targetId','left')
		->field('f.favoriteId,f.targetId,s.shopId,s.shopName,s.shopImg')
		->where(['f.userId'=> $userId,'favoriteType'=> 1])
		->order('f.favoriteId desc')
		->paginate($pagesize)->toArray();
		foreach ($page['Rows'] as $key =>$v){
			//商品列表
			$goods = db('goods')->where(['dataFlag'=> 1,'isSale'=>1,'shopId'=> $v["shopId"]])->field('goodsId,goodsName,shopPrice,goodsImg')
			->limit(10)->order('saleTime desc')->select();
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
		$userId = (int)session('FI_USER.userId');
		if(!$id)return FIReturn("取消失败", -1);
		$rs = $this->where(['favoriteId'=> $id,'favoriteType'=> $type,'userId'=>$userId])->delete();
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
		$userId = (int)session('FI_USER.userId');
		//判断记录是否存在
		$isFind = false;
		if($type==0){
			$c = Db::table('__GOODS__')->where(['goodsStatus'=>1,'dataFlag'=>1,'goodsId'=>$id])->count();
			$isFind = ($c>0);
		}else{
			$c = Db::table('__SHOPS__')->where(['shopStatus'=>1,'dataFlag'=>1,'shopId'=>$id])->count();
			$isFind = ($c>0);
		}
		if(!$isFind)return FIReturn("关注失败，无效的关注对象", -1);
		$data = [];
		$data['userId'] = $userId;
		$data['favoriteType'] = $type;
		$data['targetId'] = $id;
		//判断是否已关注
		$rc = $this->where($data)->count();
		if($rc>0)return FIReturn("关注成功", 1);
		$data['createTime'] = date('Y-m-d H:i:s');
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
		$rs = $this->where(['userId'=>(int)session('FI_USER.userId'),'favoriteType'=>$type,'targetId'=>$id])->find();
		return empty($rs)?0:$rs['favoriteId'];
	}
}
