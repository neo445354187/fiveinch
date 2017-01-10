<?php
namespace fi\home\model;
use think\Db;
use fi\home\model\Shops;
/**
 * 商城消息
 */
class ShopFreights extends Base{
	/**
	 *  运费列表
	 */
	public function listProvince(){
		$shop_id = session('FI_USER.shop_id');
		$listCity = Db::table('__AREAS__')->where(['is_show'=>1,'status'=>1,'area_type'=>0])->field('area_id,area_name')->order('area_key desc')->select();
		for ($i = 0; $i < count($listCity); $i++) {
			$parent_id = $listCity[$i]["area_id"];
			$listPro = Db::table('__AREAS__')->alias('a')
			->join('__SHOP_FREIGHTS__ s','a.area_id= s.area_id2 and s.shop_id='.$shop_id,'left')
			->where(['a.is_show'=>1,'a.status'=>1,'a.area_type'=>1,'a.parent_id'=>$parent_id])
			->field('a.area_id,a.area_name,a.parent_id,s.freight_id,s.freight')
			->order('a.area_key desc')
			->select();
			$listCity[$i]['listProvince'] = $listPro;
		}	
		return $listCity;
	}
	
	/**
	 * 编辑
	 */
	public function edit(){
		$shop_id = session('FI_USER.shop_id');
		$info = input("post.");
		$shop = new Shops;
		$shFreight = $shop->getShopsFreight($shop_id);
		$sh = $this->where(['shop_id'=>$shop_id])->count();
		Db::startTrans();
		try{
		if($sh==0){
			$list = [];
			foreach($info as $k => $v){
				$data = [];
				$data['shop_id'] = $shop_id;
				$data['area_id2'] = $k;
				if($v=='')$v=$shFreight['freight'];
				$data['freight'] = $v;
				$data['create_time'] = date('Y-m-d H:i:s');
				$list[] = $data;
			}
			$result = $this->insertAll($list);
		}else{
			foreach($info as $k => $v){
				if($v=='')$v=$shFreight['freight'];
				$data['freight'] = $v;
				$result = $this->where(['shop_id'=>$shop_id,'area_id2'=>$k])->update($data);
			}
		}
		Db::commit();
		return FIReturn("修改成功", 1);
		}catch (\Exception $e) {
			print_r($e);
			Db::rollback();
			return FIReturn('修改失败',-1);
		}
	}
}
