<?php
namespace fi\common\model;
use think\Db;
/**
 * 购物车业务处理类
 */

class Carts extends Base{
	
	/**
	 * 加入购物车
	 */
	public function addCart(){
		$user_id = (int)session('FI_USER.user_id');
		$goods_id = (int)input('post.goods_id');
		$goods_spec_id = (int)input('post.goods_spec_id');
		$cart_num = (int)input('post.buyNum',1);
		$cart_num = ($cart_num>0)?$cart_num:1;
		//验证传过来的商品是否合法
		$chk = $this->checkGoodsSaleSpec($goods_id,$goods_spec_id);
		if($chk['status']==-1)return $chk;
		//检测库存是否足够
		if($chk['data']['stock']<$cart_num)return FIReturn("加入购物车失败，商品库存不足", -1);
		$goods_spec_id = $chk['data']['goods_spec_id'];
		$goods = $this->where(['user_id'=>$user_id,'goods_id'=>$goods_id,'goods_spec_id'=>$goods_spec_id])->select();
		if(empty($goods)){
			$data = array();
			$data['user_id'] = $user_id;
			$data['goods_id'] = $goods_id;
			$data['goods_spec_id'] = $goods_spec_id;
			$data['is_check'] = 1;
			$data['cart_num'] = $cart_num;
			$rs = $this->save($data);
			if(false !==$rs){
				return FIReturn("添加成功", 1);
			}
		}else{
			$rs = $this->where(['user_id'=>$user_id,'goods_id'=>$goods_id,'goods_spec_id'=>$goods_spec_id])->setInc('cart_num',$cart_num);
		    if(false !==$rs){
				return FIReturn("添加成功", 1);
			}
		}
		return FIReturn("加入购物车失败", -1);
	}
	/**
	 * 验证商品是否合法
	 */
	public function checkGoodsSaleSpec($goods_id,$goods_spec_id){
		$goods = model('Goods')->where(['goods_status'=>1,'status'=>1,'is_sale'=>1,'goods_id'=>$goods_id])->field('goods_id,is_spec,goods_stock')->find();
		if(empty($goods))return FIReturn("添加失败，无效的商品信息", -1);
		$goods_stock = (int)$goods['goods_stock'];
		//有规格的话查询规格是否正确
		if($goods['is_spec']==1){
			$specs = Db::name('goods_specs')->where(['goods_id'=>$goods_id,'status'=>1])->field('id,is_default,spec_stock')->select();
			if(count($specs)==0){
				return FIReturn("添加失败，无效的商品信息", -1);
			}
			$defaultGoodsSpecId = 0;
			$defaultGoodsSpecStock = 0;
			$isFindSpecId = false;
			foreach ($specs as $key => $v){
				if($v['is_default']==1){
					$defaultGoodsSpecId = $v['id'];
					$defaultGoodsSpecStock = (int)$v['spec_stock'];
				}
				if($v['id']==$goods_spec_id){
					$goods_stock = (int)$v['spec_stock'];
					$isFindSpecId = true;
				}
			}
			
			if($defaultGoodsSpecId==0)return FIReturn("添加失败，无效的商品信息", -1);//有规格却找不到规格的话就报错
			if(!$isFindSpecId)return FIReturn("", 1,['goods_spec_id'=>$defaultGoodsSpecId,'stock'=>$defaultGoodsSpecStock]);//如果没有找到的话就取默认的规格
			return FIReturn("", 1,['goods_spec_id'=>$goods_spec_id,'stock'=>$goods_stock]);
		}else{
			return FIReturn("", 1,['goods_spec_id'=>0,'stock'=>$goods_stock]);
		}
	}
	/**
	 * 删除购物车里的商品
	 */
	public function delCart(){
		$user_id = (int)session('FI_USER.user_id');
		$id = (int)input('post.id');
		$this->where(['user_id'=>$user_id,'cart_id'=>$id])->delete();
		return FIReturn("删除成功", 1);
	}
	/**
	 * 取消购物车商品选中状态
	 */
	public function disChkGoods($goods_id,$goods_spec_id,$user_id){
		$this->save(['is_check'=>0],['user_id'=>$user_id,'goods_id'=>$goods_id,'goods_spec_id'=>$goods_spec_id]);
	}
	
	/**
	 * 获取购物车列表
	 */
	public function getCarts($isSettlement = false){
		$user_id = (int)session('FI_USER.user_id');
		$where = [];
		$where['c.user_id'] = $user_id;
		if($isSettlement)$where['c.is_check'] = 1;
		$rs = $this->alias('c')->join('__GOODS__ g','c.goods_id=g.goods_id','inner')
		           ->join('__SHOPS__ s','s.shop_id=g.shop_id','left')
		           ->join('__GOODS_SPECS__ gs','c.goods_spec_id=gs.id','left')
		           ->where($where)
		           ->field('c.goods_spec_id,c.cart_id,s.user_id,s.shop_id,s.shop_name,g.goods_id,s.shop_qq,shop_wangwang,g.goods_name,g.shop_price,g.goods_stock,g.is_spec,gs.spec_price,gs.spec_stock,g.goods_img,c.is_check,gs.spec_ids,c.cart_num')
		           ->select();
		$carts = [];
		$goods_ids = [];
		$goodsTotalNum = 0;
		$goodsTotalMoney = 0;
		foreach ($rs as $key =>$v){
			if(!isset($carts[$v['shop_id']]['goods_money']))$carts[$v['shop_id']]['goods_money'] = 0;
			$carts[$v['shop_id']]['shop_id'] = $v['shop_id'];
			$carts[$v['shop_id']]['shop_name'] = $v['shop_name'];
			$carts[$v['shop_id']]['shop_qq'] = $v['shop_qq'];
			$carts[$v['shop_id']]['user_id'] = $v['user_id'];
			$carts[$v['shop_id']]['shop_wangwang'] = $v['shop_wangwang'];
			if($v['is_spec']==1){
				$v['shop_price'] = $v['spec_price'];
				$v['goods_stock'] = $v['spec_stock'];
			}
			//判断能否购买，预设allowBuy值为10，为将来的各种情况预留10个情况值，从0到9
			$v['allowBuy'] = 10;
			if($v['goods_stock']<0){
				$v['allowBuy'] = 0;//库存不足
			}else if($v['goods_stock']<$v['cart_num']){
				$v['allowBuy'] = 1;//库存比购买数小
			}
			//如果是结算的话，则要过滤了不符合条件的商品
			if($isSettlement && $v['allowBuy']!=10){
				$this->disChkGoods($v['goods_id'],(int)$v['goods_spec_id'],(int)session('FI_USER.user_id'));
				continue;
			}
			if($v['is_check']==1){
				$carts[$v['shop_id']]['goods_money'] = $carts[$v['shop_id']]['goods_money'] + $v['shop_price'] * $v['cart_num'];
				$goodsTotalMoney = $goodsTotalMoney + $v['shop_price'] * $v['cart_num'];
				$goodsTotalNum++;
			}
			$v['specNames'] = [];
			unset($v['shop_name'],$v['is_spec']);
			$carts[$v['shop_id']]['list'][] = $v;
			if(!in_array($v['goods_id'],$goods_ids))$goods_ids[] = $v['goods_id'];
		}
		//加载规格值
		if(count($goods_ids)>0){
		    $specs = DB::table('__SPEC_ITEMS__')->alias('s')->join('__SPEC_CATS__ sc','s.cat_id=sc.cat_id','left')
		        ->where(['s.goods_id'=>['in',$goods_ids],'s.status'=>1])->field('cat_name,item_id,item_name')->select();
		    if(count($specs)>0){ 
		    	$specMap = [];
		    	foreach ($specs as $key =>$v){
		    		$specMap[$v['item_id']] = $v;
		    	}
			    foreach ($carts as $key =>$shop){
			    	foreach ($shop['list'] as $skey =>$v){
			    		$strName = [];
			    		if($v['spec_ids']!=''){
			    			$str = explode(':',$v['spec_ids']);
			    			foreach ($str as $vv){
			    				if(isset($specMap[$vv]))$strName[] = $specMap[$vv];
			    			}
			    		}
			    		$carts[$key]['list'][$skey]['specNames'] = $strName;
			    	}
			    }
		    }
		}
		return ['carts'=>$carts,'goodsTotalMoney'=>$goodsTotalMoney,'goodsTotalNum'=>$goodsTotalNum];     
	}
	
	/**
	 * 获取购物车商品列表
	 */
	public function getCartInfo($isSettlement = false){
		$user_id = (int)session('FI_USER.user_id');
		$where = [];
		$where['c.user_id'] = $user_id;
		if($isSettlement)$where['c.is_check'] = 1;
		$rs = $this->alias('c')->join('__GOODS__ g','c.goods_id=g.goods_id','inner')
		           ->join('__GOODS_SPECS__ gs','c.goods_spec_id=gs.id','left')
		           ->where($where)
		           ->field('c.goods_spec_id,c.cart_id,g.goods_id,g.goods_name,g.shop_price,g.goods_stock,g.is_spec,gs.spec_price,gs.spec_stock,g.goods_img,c.is_check,gs.spec_ids,c.cart_num')
		           ->select();
		$goods_ids = []; 
		$goodsTotalMoney = 0;
		$goodsTotalNum = 0;
		foreach ($rs as $key =>$v){
			if(!in_array($v['goods_id'],$goods_ids))$goods_ids[] = $v['goods_id'];
			$goodsTotalMoney = $goodsTotalMoney + $v['shop_price'] * $v['cart_num'];
			$rs[$key]['goods_img'] = FIImg($v['goods_img']);
		}
	    //加载规格值
		if(count($goods_ids)>0){
		    $specs = DB::table('__SPEC_ITEMS__')->alias('s')->join('__SPEC_CATS__ sc','s.cat_id=sc.cat_id','left')
		        ->where(['s.goods_id'=>['in',$goods_ids],'s.status'=>1])->field('item_id,item_name')->select();
		    if(count($specs)>0){ 
		    	$specMap = [];
		    	foreach ($specs as $key =>$v){
		    		$specMap[$v['item_id']] = $v;
		    	}
			    foreach ($rs as $key =>$v){
			    	$strName = [];
			    	if($v['spec_ids']!=''){
			    		$str = explode(':',$v['spec_ids']);
			    		foreach ($str as $vv){
			    			if(isset($specMap[$vv]))$strName[] = $specMap[$vv]['item_name'];
			    		}
			    	}
			    	$rs[$key]['specNames'] = $strName;
			    }
		    }
		}
		$goodsTotalNum = count($rs);
		return ['list'=>$rs,'goodsTotalMoney'=>sprintf("%.2f", $goodsTotalMoney),'goodsTotalNum'=>$goodsTotalNum];
	}
	
	/**
	 * 修改购物车商品状态
	 */
	public function changeCartGoods(){
		$is_check = Input('post.is_check/d',-1);
		$buyNum = Input('post.buyNum/d',1);
		if($buyNum<1)$buyNum = 1;
		$id = Input('post.id/d');
		$user_id = (int)session('FI_USER.user_id');
		$data = [];
		if($is_check!=-1)$data['is_check'] = $is_check;
		$data['cart_num'] = $buyNum;
		$this->where(['user_id'=>$user_id,'cart_id'=>$id])->update($data);
		return FIReturn("操作成功", 1);
	}
}
