<?php
namespace fi\home\model;
/**
 * 门店类
 */
use think\Db;
class Shops extends Base{
    /**
     *  获取店铺的默认运费
     */
    public function getShopsFreight($shopId){
    	return $this->where(["dataFlag"=>1,"shopId"=>$shopId])->field('freight')->find();
    }
    
    /**
     * 店铺街列表
     */
    public function pageQuery($pagesize){
    	$catId = input("get.id/d");
    	$keyword = input("keyword");
    	$userId = (int)session('FI_USER.userId');
    	$rs = $this->alias('s');
    	$where = [];
    	$where['s.dataFlag'] = 1;
    	$where['s.shopStatus'] = 1;
    	if($keyword!='')$where['s.shopName'] = ['like','%'.$keyword.'%'];
    	if($catId>0){
    		$rs->join('__CAT_SHOPS__ cs','cs.shopId = s.shopId','left');
    		$where['cs.catId'] = $catId;
    	}
    	$page = $rs->join('__SHOP_SCORES__ ss','ss.shopId = s.shopId','left')
    	->join('__USERS__ u','u.userId = s.userId','left')
    	->join('__FAVORITES__ f','f.userId = '.$userId.' and f.favoriteType=1 and f.targetId=s.shopId','left')
    	->where($where)
    	->order('s.shopId asc')
    	->field('s.shopId,s.shopImg,s.shopName,s.shopTel,s.shopQQ,s.shopCompany,ss.totalScore,ss.totalUsers,ss.goodsScore,ss.goodsUsers,ss.serviceScore,ss.serviceUsers,ss.timeScore,ss.timeUsers,.u.loginName,f.favoriteId,s.areaIdPath')
    	->paginate($pagesize)->toArray();
    	if(empty($page['Rows']))return $page;
    	$shopIds = [];
    	$areaIds = [];
    	foreach ($page['Rows'] as $key =>$v){
    		$shopIds[] = $v['shopId'];
    		$tmp = explode('_',$v['areaIdPath']);
    		$areaIds[] = $tmp[1];
    		$page['Rows'][$key]['areaId'] = $tmp[1];
    		//总评分
    		$page['Rows'][$key]['totalScore'] = FIScore($v["totalScore"], $v["totalUsers"]);
    		$page['Rows'][$key]['goodsScore'] = FIScore($v['goodsScore'],$v['goodsUsers']);
    		$page['Rows'][$key]['serviceScore'] = FIScore($v['serviceScore'],$v['serviceUsers']);
    		$page['Rows'][$key]['timeScore'] = FIScore($v['timeScore'],$v['timeUsers']);
    		//商品列表
    		$goods = Db::table('__GOODS__')->where(['dataFlag'=> 1,'goodsStatus'=>1,'isSale'=>1,'shopId'=> $v["shopId"]])->field('goodsId,goodsName,shopPrice,goodsImg')->limit(10)->order('saleTime desc')->select();
    		$page['Rows'][$key]['goods'] = $goods;
    		//店铺商品总数
    		$page['Rows'][$key]['goodsTotal'] = count($goods);
		}
		$rccredMap = [];
		$goodsCatMap = [];
		$areaMap = [];
		//认证、地址、分类
		if(!empty($shopIds)){
			$rccreds = Db::table('__SHOP_ACCREDS__')->alias('sac')->join('__ACCREDS__ a','a.accredId=sac.accredId and a.dataFlag=1','left')
			             ->where('shopId','in',$shopIds)->field('sac.shopId,accredName,accredImg')->select();
			foreach ($rccreds as $v){
				$rccredMap[$v['shopId']][] = $v;
			}
			$goodsCats = Db::table('__CAT_SHOPS__')->alias('cs')->join('__GOODS_CATS__ gc','cs.catId=gc.catId and gc.dataFlag=1','left')
			               ->where('shopId','in',$shopIds)->field('cs.shopId,gc.catName')->select();
		    foreach ($goodsCats as $v){
				$goodsCatMap[$v['shopId']][] = $v['catName'];
			}
			$areas = Db::table('__AREAS__')->alias('a')->join('__AREAS__ a1','a1.areaId=a.parentId','left')
			           ->where('a.areaId','in',$areaIds)->field('a.areaId,a.areaName areaName2,a1.areaName areaName1')->select();
		    foreach ($areas as $v){
				$areaMap[$v['areaId']] = $v;
			}         
		}
		foreach ($page['Rows'] as $key =>$v){
			$page['Rows'][$key]['accreds'] = (isset($rccredMap[$v['shopId']]))?$rccredMap[$v['shopId']]:[];
			$page['Rows'][$key]['catshops'] = (isset($goodsCatMap[$v['shopId']]))?implode(',',$goodsCatMap[$v['shopId']]):'';
			$page['Rows'][$key]['areas'] = ['areaName1'=>$areaMap[$v['areaId']]['areaName1'],'areaName2'=>$areaMap[$v['areaId']]['areaName2']];
		}
    	return $page;
    }
    /**
     * 获取商家认证
     */
    public function shopAccreds($shopId){
    	$accreds= Db::table("__SHOP_ACCREDS__")->alias('sa')
    	->join('__ACCREDS__ a','a.accredId=sa.accredId','left')
    	->field('a.accredName,a.accredImg')
    	->where(['sa.shopId'=> $shopId])
    	->select();
    	return $accreds;
    }
    /**
     * 获取店铺评分
     */
    public function getBriefShop($shopId){
    	$shop = $this->alias('s')->join('__SHOP_SCORES__ cs','cs.shopId = s.shopId','left')
    	            ->where(['s.shopId'=>$shopId,'s.shopStatus'=>1,'s.dataFlag'=>1])->field('s.shopImg,s.shopId,s.shopName,cs.*')->find()->toArray();
    	$shop['totalScore'] = FIScore($shop['totalScore']/3,$shop['totalUsers']);
    	$shop['goodsScore'] = FIScore($shop['goodsScore'],$shop['goodsUsers']);
    	$shop['serviceScore'] = FIScore($shop['serviceScore'],$shop['serviceUsers']);
    	$shop['timeScore'] = FIScore($shop['timeScore'],$shop['timeUsers']);
    	FIUnset($shop, 'totalUsers,goodsUsers,serviceUsers,timeUsers');
    	return $shop;
    }
    /**
     * 获取卖家中心信息
     */
    public function getShopSummary($shopId){
    	$shop = $this->alias('s')->join('__SHOP_SCORES__ cs','cs.shopId = s.shopId','left')
    	           ->where(['s.shopId'=>$shopId,'dataFlag'=>1])
    	->field('s.shopId,shopImg,shopName,shopAddress,shopQQ,shopTel,serviceStartTime,serviceEndTime,cs.*')
    	->find();
    	//评分
    	$scores['totalScore'] = FIScore($shop['totalScore'],$shop['totalUsers']);
    	$scores['goodsScore'] = FIScore($shop['goodsScore'],$shop['goodsUsers']);
    	$scores['serviceScore'] = FIScore($shop['serviceScore'],$shop['serviceUsers']);
    	$scores['timeScore'] = FIScore($shop['timeScore'],$shop['timeUsers']);
    	FIUnset($shop, 'totalUsers,goodsUsers,serviceUsers,timeUsers');
    	$shop['scores'] = $scores;
    	//认证
    	$accreds = $this->shopAccreds($shopId);
    	$shop['accreds'] = $accreds;
    	return ['shop'=>$shop];
    }
    /**
     * 获取店铺首页信息
     */
    public function getShopInfo($shopId){
    	$rs = $this->where(['shopId'=>$shopId,'shopStatus'=>1,'dataFlag'=>1])
    	->field('shopId,shopImg,shopName,shopAddress,shopQQ,shopWangWang,shopTel,serviceStartTime,serviceEndTime')
    	->find();
    	if(empty($rs)){
    		//如果没有传id就获取自营店铺
    		$rs = $this->where(['shopStatus'=>1,'dataFlag'=>1,'isSelf'=>1])
    	               ->field('shopId,shopImg,shopName,shopAddress,shopQQ,shopWangWang,shopTel,serviceStartTime,serviceEndTime')
    	               ->find();
    	    if(empty($rs))return [];
    	    $shopId = $rs['shopId'];
    	}
    	//评分
    	$score = $this->getBriefShop($rs['shopId']);
    	$rs['scores'] = $score;
    	//认证
    	$accreds = $this->shopAccreds($rs['shopId']);
    	$rs['accreds'] = $accreds;

        $shopAds = array();
        $config = Db::table('__SHOP_CONFIGS__')->where("shopId=".$rs['shopId'])->find();
        $isAds = input('param.');
        $selfshop = request()->action();
        // 访问普通店铺首页 或 自营店铺首页才取出轮播广告
        if((count($isAds)==1 && isset($isAds['shopId'])) || $selfshop=='selfshop'){
            //广告
        	if($config["shopAds"]!=''){
        		$shopAdsImg = explode(',',$config["shopAds"]);
        		$shopAdsUrl = explode(',',$config["shopAdsUrl"]);
        		for($i=0;$i<count($shopAdsImg);$i++){
        			$adsImg = $shopAdsImg[$i];
        			$shopAds[$i]["adImg"] = $adsImg;
        			$imgpaths= explode('.',$adsImg);
        			$shopAds[$i]["adImg_thumb"] = $imgpaths[0]."_thumb.".$imgpaths[1];
        			$shopAds[$i]["adUrl"] = $shopAdsUrl[$i];
        		}
        	}
        }
    	$rs['shopAds'] = $shopAds;
    	$rs['shopTitle'] = $config["shopTitle"];
    	$rs['shopDesc'] = $config["shopDesc"];
    	$rs['shopKeywords'] = $config["shopKeywords"];
    	$rs['shopBanner'] = $config["shopBanner"];
    	//关注
    	$f = model('Favorites');
    	$rs['favShop'] = $f->checkFavorite($shopId,1);
    	//热搜关键词
    	$sc = new ShopConfigs();
    	$rs['shopHotWords'] = $sc->searchShopkey($shopId);
    	return $rs;
    }
    
    /**
     * 获取店铺信息
     */
	public function getByView($id){
		$shop = $this->alias('s')->join('__BANKS__ b','b.bankId=s.bankId','left')
		             ->where(['s.dataFlag'=>1,'shopId'=>$id])
		             ->field('s.*,b.bankName')->find();
	     $areaIds = [];
        $areaMaps = [];
        $tmp = explode('_',$shop['areaIdPath']);
        foreach ($tmp as $vv){
         	if($vv=='')continue;
         	if(!in_array($vv,$areaIds))$areaIds[] = $vv;
        }
        if(!empty($areaIds)){
	         $areas = Db::table('__AREAS__')->where(['dataFlag'=>1,'areaId'=>['in',$areaIds]])->field('areaId,areaName')->select();
	         foreach ($areas as $v){
	         	 $areaMaps[$v['areaId']] = $v['areaName'];
	         }
	         $tmp = explode('_',$shop['areaIdPath']);
	         $areaNames = [];
		     foreach ($tmp as $vv){
	         	 if($vv=='')continue;
	         	 $areaNames[] = $areaMaps[$vv];
	         	 $shop['areaName'] = implode('',$areaNames);
	         }
         }             
		                          
		//获取经营范围
		$goodsCats = Db::table('__GOODS_CATS__')->where(['parentId'=>0,'isShow'=>1,'dataFlag'=>1])->field('catId,catName')->select();
		$catshops = Db::table('__CAT_SHOPS__')->where('shopId',$id)->select();
		$catshopMaps = [];
		foreach ($goodsCats as $v){
			$catshopMaps[$v['catId']] = $v['catName'];
		}
		$catshopNames = [];
		foreach ($catshops as $key =>$v){
			if(isset($catshopMaps[$v['catId']]))$catshopNames[] = $catshopMaps[$v['catId']];
		}
		$shop['catshopNames'] = implode('、',$catshopNames);
		//获取认证类型
	    $shop['accreds'] =Db::table('__SHOP_ACCREDS__')->alias('sac')->join('__ACCREDS__ a','sac.accredId=a.accredId and a.dataFlag=1','inner')
	                    ->where('sac.shopId',$id)->field('accredName,accredImg')->select();
	    //店铺地址
		return $shop;
	}

    /**
    * 获取自营店铺 店长推荐 热卖商品
    */
    public function getRecGoods($type){
        $arr = ['rec'=>'isRecom','hot'=>'isHot'];
        $order='';
        $where[$arr[$type]]=1;
        if($type=='hot')$order='saleNum desc';
        $rs = $this->alias('s')
                   ->join('__GOODS__ g','s.shopId=g.shopId','inner')
                   ->field('g.goodsName,g.goodsImg,g.shopPrice,g.goodsId')
                   ->where($where)
                   ->limit(5)
                   ->order($order)
                   ->select();
        return $rs;
    }
}
