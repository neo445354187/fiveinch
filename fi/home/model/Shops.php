<?php
namespace fi\home\model;

/**
 * 门店类
 */
use fi\common\model\Areas;
use think\Db;

class Shops extends Base
{

    /**
     *  获取店铺的默认运费
     */
    public function getShopsFreight($shop_id)
    {
        return $this->where(["status" => 1, "shop_id" => $shop_id])->field('freight')->find();
    }

    /**
     * 店铺街列表
     */
    public function pageQuery($pagesize)
    {
        $cat_id                 = input("get.id/d");
        $keyword                = input("keyword");
        $user_id                = (int) session('FI_USER.user_id');
        $rs                     = $this->alias('s');
        $where                  = [];
        $where['s.status']      = 1;
        $where['s.shop_status'] = 1;
        if ($keyword != '') {
            $where['s.shop_name'] = ['like', '%' . $keyword . '%'];
        }

        if ($cat_id > 0) {
            $rs->join('__CAT_SHOPS__ cs', 'cs.shop_id = s.shop_id', 'left');
            $where['cs.cat_id'] = $cat_id;
        }
        $page = $rs->join('__SHOP_SCORES__ ss', 'ss.shop_id = s.shop_id', 'left')
            ->join('__USERS__ u', 'u.user_id = s.user_id', 'left')
            ->join('__FAVORITES__ f', 'f.user_id = ' . $user_id . ' and f.favorite_type=1 and f.target_id=s.shop_id', 'left')
            ->where($where)
            ->order('s.shop_id asc')
            ->field('s.shop_id,s.shop_img,s.shop_name,s.shop_tel,s.shop_qq,s.shop_company,ss.total_score,ss.total_users,ss.goods_score,ss.goods_users,ss.service_score,ss.service_users,ss.time_score,ss.time_users,.u.login_name,f.favorite_id,s.area_id_path')
            ->paginate($pagesize)->toArray();
        if (empty($page['Rows'])) {
            return $page;
        }

        $shop_ids = [];
        $area_ids = [];
        foreach ($page['Rows'] as $key => $v) {
            $shop_ids[]                    = $v['shop_id'];
            $tmp                           = explode('_', $v['area_id_path']);
            $area_ids[]                    = $tmp[1];
            $page['Rows'][$key]['area_id'] = $tmp[1];
            //总评分
            $page['Rows'][$key]['total_score']   = FIScore($v["total_score"], $v["total_users"]);
            $page['Rows'][$key]['goods_score']   = FIScore($v['goods_score'], $v['goods_users']);
            $page['Rows'][$key]['service_score'] = FIScore($v['service_score'], $v['service_users']);
            $page['Rows'][$key]['time_score']    = FIScore($v['time_score'], $v['time_users']);
            //商品列表
            $goods                       = Db::table('__GOODS__')->where(['status' => 1, 'goods_status' => 1, 'is_sale' => 1, 'shop_id' => $v["shop_id"]])->field('goods_id,goods_name,shop_price,goods_img')->limit(10)->order('sale_time desc')->select();
            $page['Rows'][$key]['goods'] = $goods;
            //店铺商品总数
            $page['Rows'][$key]['goodsTotal'] = count($goods);
        }
        $rccredMap   = [];
        $goodsCatMap = [];
        $areaMap     = [];
        //认证、地址、分类
        if (!empty($shop_ids)) {
            $rccreds = Db::table('__SHOP_ACCREDS__')->alias('sac')->join('__ACCREDS__ a', 'a.accred_id=sac.accred_id and a.status=1', 'left')
                ->where('shop_id', 'in', $shop_ids)->field('sac.shop_id,accred_name,accred_img')->select();
            foreach ($rccreds as $v) {
                $rccredMap[$v['shop_id']][] = $v;
            }
            $goodsCats = Db::table('__CAT_SHOPS__')->alias('cs')->join('__GOODS_CATS__ gc', 'cs.cat_id=gc.cat_id and gc.status=1', 'left')
                ->where('shop_id', 'in', $shop_ids)->field('cs.shop_id,gc.cat_name')->select();
            foreach ($goodsCats as $v) {
                $goodsCatMap[$v['shop_id']][] = $v['cat_name'];
            }
            $areas = Db::table('__AREAS__')->alias('a')->join('__AREAS__ a1', 'a1.area_id=a.parent_id', 'left')
                ->where('a.area_id', 'in', $area_ids)->field('a.area_id,a.area_name area_name2,a1.area_name area_name1')->select();
            foreach ($areas as $v) {
                $areaMap[$v['area_id']] = $v;
            }
        }
        foreach ($page['Rows'] as $key => $v) {
            $page['Rows'][$key]['accreds']  = (isset($rccredMap[$v['shop_id']])) ? $rccredMap[$v['shop_id']] : [];
            $page['Rows'][$key]['catshops'] = (isset($goodsCatMap[$v['shop_id']])) ? implode(',', $goodsCatMap[$v['shop_id']]) : '';
            $page['Rows'][$key]['areas']    = ['area_name1' => $areaMap[$v['area_id']]['area_name1'], 'area_name2' => $areaMap[$v['area_id']]['area_name2']];
        }
        return $page;
    }
    /**
     * 获取商家认证
     */
    public function shopAccreds($shop_id)
    {
        $accreds = Db::table("__SHOP_ACCREDS__")->alias('sa')
            ->join('__ACCREDS__ a', 'a.accred_id=sa.accred_id', 'left')
            ->field('a.accred_name,a.accred_img')
            ->where(['sa.shop_id' => $shop_id])
            ->select();
        return $accreds;
    }
    /**
     * 获取店铺评分
     */
    public function getBriefShop($shop_id)
    {
        $shop = $this->alias('s')
            ->join('__SHOP_SCORES__ cs', 'cs.shop_id = s.shop_id', 'left')
            ->where(['s.shop_id' => $shop_id, 's.shop_status' => 1, 's.status' => 1])
            ->field('s.shop_img,s.shop_id,s.shop_name,s.shop_lnglat,s.area_id,s.shop_address,s.telephone,s.shop_tel,cs.*')
            ->find()
            ->toArray();
        $area_info = Db::name('areas')
            ->field('area_name')
            ->where(['status' => 1, 'area_id' => $shop['area_id']])
            ->find();
        $shop                  = $area_info ? array_merge($shop, $area_info) : $shop;
        //获取经营范围
        $goodsCats   = Db::table('__GOODS_CATS__')->where(['parent_id' => 0, 'is_show' => 1, 'status' => 1])->field('cat_id,cat_name')->select();
        $catshops    = Db::table('__CAT_SHOPS__')->where('shop_id', $shop_id)->select();
        $catshopMaps = [];
        foreach ($goodsCats as $v) {
            $catshopMaps[$v['cat_id']] = $v['cat_name'];
        }
        $catshop_names = [];
        foreach ($catshops as $key => $v) {
            if (isset($catshopMaps[$v['cat_id']])) {
                $catshop_names[] = $catshopMaps[$v['cat_id']];
            }

        }
        $shop['catshop_names'] = implode('、', $catshop_names);
        $shop['total_score']   = FIScore($shop['total_score'] / 3, $shop['total_users']);
        $shop['goods_score']   = FIScore($shop['goods_score'], $shop['goods_users']);
        $shop['service_score'] = FIScore($shop['service_score'], $shop['service_users']);
        $shop['time_score']    = FIScore($shop['time_score'], $shop['time_users']);
        FIUnset($shop, 'total_users,goods_users,service_users,time_users');
        return $shop;
    }
    /**
     * 获取卖家中心信息
     */
    public function getShopSummary($shop_id)
    {
        $shop = $this->alias('s')->join('__SHOP_SCORES__ cs', 'cs.shop_id = s.shop_id', 'left')
            ->where(['s.shop_id' => $shop_id, 'status' => 1])
            ->field('s.shop_id,shop_img,shop_name,shop_address,shop_qq,shop_tel,service_start_time,service_end_time,cs.*')
            ->find();
        //评分
        $scores['total_score']   = FIScore($shop['total_score'], $shop['total_users']);
        $scores['goods_score']   = FIScore($shop['goods_score'], $shop['goods_users']);
        $scores['service_score'] = FIScore($shop['service_score'], $shop['service_users']);
        $scores['time_score']    = FIScore($shop['time_score'], $shop['time_users']);
        FIUnset($shop, 'total_users,goods_users,service_users,time_users');
        $shop['scores'] = $scores;
        //认证
        $accreds         = $this->shopAccreds($shop_id);
        $shop['accreds'] = $accreds;
        return ['shop' => $shop];
    }
    /**
     * 获取店铺首页信息
     */
    public function getShopInfo($shop_id)
    {
        $rs = $this->where(['shop_id' => $shop_id, 'shop_status' => 1, 'status' => 1])
            ->field('shop_id,shop_img,shop_name,shop_address,shop_qq,shop_wangwang,shop_tel,service_start_time,service_end_time')
            ->find();
        if (empty($rs)) {
            //如果没有传id就获取自营店铺
            $rs = $this->where(['shop_status' => 1, 'status' => 1, 'is_self' => 1])
                ->field('shop_id,shop_img,shop_name,shop_address,shop_qq,shop_wangwang,shop_tel,service_start_time,service_end_time')
                ->find();
            if (empty($rs)) {
                return [];
            }

            $shop_id = $rs['shop_id'];
        }
        //评分
        $score        = $this->getBriefShop($rs['shop_id']);
        $rs['scores'] = $score;
        //认证
        $accreds       = $this->shopAccreds($rs['shop_id']);
        $rs['accreds'] = $accreds;

        $shop_ads = array();
        $config   = Db::table('__SHOP_CONFIGS__')->where("shop_id=" . $rs['shop_id'])->find();
        $isAds    = input('param.');
        $selfshop = request()->action();
        // 访问普通店铺首页 或 自营店铺首页才取出轮播广告
        if ((count($isAds) == 1 && isset($isAds['shop_id'])) || $selfshop == 'selfshop') {
            //广告
            if ($config["shop_ads"] != '') {
                $shop_adsImg  = explode(',', $config["shop_ads"]);
                $shop_ads_url = explode(',', $config["shop_ads_url"]);
                for ($i = 0; $i < count($shop_adsImg); $i++) {
                    $adsImg                      = $shop_adsImg[$i];
                    $shop_ads[$i]["adImg"]       = $adsImg;
                    $imgpaths                    = explode('.', $adsImg);
                    $shop_ads[$i]["adImg_thumb"] = $imgpaths[0] . "_thumb." . $imgpaths[1];
                    $shop_ads[$i]["adUrl"]       = $shop_ads_url[$i];
                }
            }
        }
        $rs['shop_ads']      = $shop_ads;
        $rs['shop_title']    = $config["shop_title"];
        $rs['shop_desc']     = $config["shop_desc"];
        $rs['shop_keywords'] = $config["shop_keywords"];
        $rs['shop_banner']   = $config["shop_banner"];
        //关注
        $f             = model('Favorites');
        $rs['favShop'] = $f->checkFavorite($shop_id, 1);
        //热搜关键词
        $sc                   = new ShopConfigs();
        $rs['shop_hot_words'] = $sc->searchShopkey($shop_id);
        return $rs;
    }

    /**
     * 获取店铺信息
     */
    public function getByView($id)
    {
        $shop = $this->alias('s')->join('__BANKS__ b', 'b.bank_id=s.bank_id', 'left')
            ->where(['s.status' => 1, 'shop_id' => $id])
            ->field('s.*,b.bank_name')->find();
        $area_ids = [];
        $areaMaps = [];
        $tmp      = explode('_', $shop['area_id_path']);
        foreach ($tmp as $vv) {
            if ($vv == '') {
                continue;
            }

            if (!in_array($vv, $area_ids)) {
                $area_ids[] = $vv;
            }

        }
        if (!empty($area_ids)) {
            $areas = Db::table('__AREAS__')->where(['status' => 1, 'area_id' => ['in', $area_ids]])->field('area_id,area_name')->select();
            foreach ($areas as $v) {
                $areaMaps[$v['area_id']] = $v['area_name'];
            }
            $tmp        = explode('_', $shop['area_id_path']);
            $area_names = [];
            foreach ($tmp as $vv) {
                if ($vv == '') {
                    continue;
                }

                $area_names[]      = $areaMaps[$vv];
                $shop['area_name'] = implode('', $area_names);
            }
        }

        //获取经营范围
        $goodsCats   = Db::table('__GOODS_CATS__')->where(['parent_id' => 0, 'is_show' => 1, 'status' => 1])->field('cat_id,cat_name')->select();
        $catshops    = Db::table('__CAT_SHOPS__')->where('shop_id', $id)->select();
        $catshopMaps = [];
        foreach ($goodsCats as $v) {
            $catshopMaps[$v['cat_id']] = $v['cat_name'];
        }
        $catshop_names = [];
        foreach ($catshops as $key => $v) {
            if (isset($catshopMaps[$v['cat_id']])) {
                $catshop_names[] = $catshopMaps[$v['cat_id']];
            }

        }
        $shop['catshop_names'] = implode('、', $catshop_names);
        //获取认证类型
        $shop['accreds'] = Db::table('__SHOP_ACCREDS__')->alias('sac')->join('__ACCREDS__ a', 'sac.accred_id=a.accred_id and a.status=1', 'inner')
            ->where('sac.shop_id', $id)->field('accred_name,accred_img')->select();
        // var_dump($shop);die;//debug
        //店铺地址
        return $shop;
    }

    /**
     * 获取自营店铺 店长推荐 热卖商品
     */
    public function getRecGoods($type)
    {
        $arr                = ['rec' => 'is_recom', 'hot' => 'is_hot'];
        $order              = '';
        $where[$arr[$type]] = 1;
        if ($type == 'hot') {
            $order = 'sale_num desc';
        }

        $rs = $this->alias('s')
            ->join('__GOODS__ g', 's.shop_id=g.shop_id', 'inner')
            ->field('g.goods_name,g.goods_img,g.shop_price,g.goods_id')
            ->where($where)
            ->limit(5)
            ->order($order)
            ->select();
        return $rs;
    }

    /**
     * [getShopInfoAndAddress 获取店铺信息和地址]
     * @param  [type] $shop_id [description]
     * @return [type]         [description]
     */
    public function getShopInfoAndAddress($user_id)
    {
        $shopInfo = $this->where(["user_id" => $user_id, "status" => 1])->find();
        $location = array();
        if ($shopInfo) {
            $location = (new Areas)->getLocationByAreaIdPath($shopInfo['area_id_path']);
        }
        return array_merge($shopInfo->toArray(), $location);
    }

}
