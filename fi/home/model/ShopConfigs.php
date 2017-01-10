<?php
namespace fi\home\model;
/**
 * 门店配置类
 */
use think\Db;
class ShopConfigs extends Base{
    /**
    * 店铺设置
    */
     public function getShopCfg($id){
        $rs = $this->where("shop_id=".$id)->find();
        if($rs != ''){
            //图片
            $rs['shop_ads'] = ($rs['shop_ads']!='')?explode(',',$rs['shop_ads']):null;
            //图片的广告地址
            $rs['shop_ads_url'] = ($rs['shop_ads_url']!='')?explode(',',$rs['shop_ads_url']):null;
            return $rs;
        }
     }

     /**
      * 修改店铺设置
      */
     public function editShopCfg($shop_id){
        $data = input('post.');
        //加载商店信息
        Db::startTrans();
		try{
	        $shopcg = $this->where('shop_id='.$shop_id)->find(); 
	        $scdata = array();
	        $scdata["shop_id"] =  $shop_id;
	        $scdata["shop_keywords"] =  Input("shop_keywords");
	        $scdata["shop_banner"] =  Input("shop_banner");
	        $scdata["shop_desc"] =  Input("shop_desc");
	        $scdata["shop_ads"] =  Input("shop_ads");
	        $scdata["shop_ads_url"] =  Input("shop_ads_url");
	        $scdata["shop_hot_words"] =  Input("shop_hot_words");
	        FIUseImages(0, $shopcg['config_id'], $scdata['shop_banner'],'shop_configs','shop_banner');
	        FIUseImages(0, $shopcg['config_id'], $scdata['shop_ads'],'shop_configs','shop_ads');
	        $rs = $this->where("shop_id=".$shop_id)->update($scdata);	
	        if($rs!==false){
	        	Db::commit();
	            return FIReturn('操作成功',1);
	        }
		}catch (\Exception $e) {
			print_r($e);
            Db::rollback();
        }
        return FIReturn('操作失败',-1);
     }
     /**
      * 获取商城搜索关键字
      */
     public function searchShopkey($shop_id){
     	$rs = $this->where('shop_id='.$shop_id)->field('config_id,shop_hot_words')->find();
     	$keys = [];
     	if($rs['shop_hot_words']!='')$keys = explode(',',$rs['shop_hot_words']);
     	return $keys;
     }
}
