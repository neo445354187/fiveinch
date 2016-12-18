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
		$shopId = session('FI_USER.shopId');
		$listCity = Db::table('__AREAS__')->where(['isShow'=>1,'dataFlag'=>1,'areaType'=>0])->field('areaId,areaName')->order('areaKey desc')->select();
		for ($i = 0; $i < count($listCity); $i++) {
			$parentId = $listCity[$i]["areaId"];
			$listPro = Db::table('__AREAS__')->alias('a')
			->join('__SHOP_FREIGHTS__ s','a.areaId= s.areaId2 and s.shopId='.$shopId,'left')
			->where(['a.isShow'=>1,'a.dataFlag'=>1,'a.areaType'=>1,'a.parentId'=>$parentId])
			->field('a.areaId,a.areaName,a.parentId,s.freightId,s.freight')
			->order('a.areaKey desc')
			->select();
			$listCity[$i]['listProvince'] = $listPro;
		}	
		return $listCity;
	}
	
	/**
	 * 编辑
	 */
	public function edit(){
		$shopId = session('FI_USER.shopId');
		$info = input("post.");
		$shop = new Shops;
		$shFreight = $shop->getShopsFreight($shopId);
		$sh = $this->where(['shopId'=>$shopId])->count();
		Db::startTrans();
		try{
		if($sh==0){
			$list = [];
			foreach($info as $k => $v){
				$data = [];
				$data['shopId'] = $shopId;
				$data['areaId2'] = $k;
				if($v=='')$v=$shFreight['freight'];
				$data['freight'] = $v;
				$data['createTime'] = date('Y-m-d H:i:s');
				$list[] = $data;
			}
			$result = $this->insertAll($list);
		}else{
			foreach($info as $k => $v){
				if($v=='')$v=$shFreight['freight'];
				$data['freight'] = $v;
				$result = $this->where(['shopId'=>$shopId,'areaId2'=>$k])->update($data);
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
