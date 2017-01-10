<?php
namespace fi\common\model;

use think\Db;

/**
 * 标签业务处理类
 */
class Tags extends Base
{
    /**
     * 获取指定商品
     */
    public function listGoods($type, $cat_id = 0, $num, $cache = 0)
    {
        $type = strtolower($type);
        if (strtolower($type) == 'history') {
            return $this->historyByGoods($num);
        } else {
            return $this->listByGoods($type, $cat_id, $num, $cache);
        }
    }
    /**
     * 浏览商品
     */
    public function historyByGoods($num)
    {
        $hids = $ids = cookie("history_goods");
        if (empty($ids)) {
            return [];
        }

        $where                = [];
        $where['is_sale']      = 1;
        $where['goods_status'] = 1;
        $where['g.status']  = 1;
        $where['goods_id']     = ['in', $ids];
        $goods                = Db::table('__GOODS__')->alias('g')->join('__SHOPS__ s', 'g.shop_id=s.shop_id')
            ->where($where)->field('s.shop_name,s.shop_id,goods_id,goods_name,goods_img,goods_sn,goods_stock,sale_num,shop_price,market_price,is_spec,appraise_num,visit_num')
            ->limit($num)
            ->select();
        $ids = [];
        foreach ($goods as $key => $v) {
            if ($v['is_spec'] == 1) {
                $ids[] = $v['goods_id'];
            }

        }
        if (!empty($ids)) {
            $specs = [];
            $rs    = Db::table('__GOODS_SPECS__ gs ')->where(['goods_id' => ['in', $ids], 'status' => 1])->order('id asc')->select();
            foreach ($rs as $key => $v) {
                $specs[$v['goods_id']] = $v;
            }
            foreach ($goods as $key => $v) {
                if (isset($specs[$v['goods_id']])) {
                    $goods[$key]['specs'] = $specs[$v['goods_id']];
                }

            }
        }
        $hGoods = [];
        foreach ($hids as $k => $v) {
            foreach ($goods as $k1 => $v1) {
                if ($v1['goods_id'] == $v) {
                    $hGoods[] = $v1;
                }

            }
        }
        return $hGoods;
    }
    /**
     * 推荐商品
     */
    public function listByGoods($type, $cat_id, $num, $cache = 0)
    {
        if (!in_array($type, [0, 1, 2, 3])) {
            return [];
        }

        $cacheData = cache('TAG_GOODS_' . $type . "_" . $cat_id . "_" . $num);
        if ($cacheData) {
            return $cacheData;
        }

        //检测是否有数据
        $types                  = ['recom' => 0, 'new' => 3, 'hot' => 1, 'best' => 2];
        $where                  = [];
        $where['r.data_src']     = 0;
        $where['g.is_sale']      = 1;
        $where['g.goods_status'] = 1;
        $where['g.status']    = 1;
        $goods                  = [];
        if ($type != 'visit') {
            $where['r.data_type']   = $types[$type];
            $where['r.goods_cat_id'] = $cat_id;
            $goods                 = Db::table('__GOODS__')->alias('g')->join('__RECOMMENDS__ r', 'g.goods_id=r.data_id')
                ->join('__SHOPS__ s', 'g.shop_id=s.shop_id')
                ->where($where)->field('s.shop_name,s.shop_id,g.goods_id,goods_name,goods_img,goods_sn,goods_stock,sale_num,shop_price,market_price,is_spec,appraise_num,visit_num')
                ->order('r.data_sort asc')->limit($num)->select();
        }
        //判断有没有设置，如果没有设置的话则获取实际的数据
        if (empty($goods)) {
            $goods_cat_ids = FIGoodsCatPath($cat_id);
            $types       = ['recom' => 'is_recom', 'new' => 'is_new', 'hot' => 'is_hot', 'best' => 'is_best'];
            $order       = ['recom' => 'sale_num desc,goods_id asc',
                'new'                   => 'sale_time desc,goods_id asc',
                'hot'                   => 'sale_num desc,goods_id asc',
                'best'                  => 'sale_num desc,goods_id asc',
                'visit'                 => 'visit_num desc',
            ];

            $where                = [];
            $where['is_sale']      = 1;
            $where['goods_status'] = 1;
            $where['g.status']  = 1;

            if ($type != 'visit') {
                $where[$types[$type]] = 1;
            }

            if (!empty($goods_cat_ids)) {
                $where['g.goods_cat_id_path'] = ['like', implode('_', $goods_cat_ids) . '_%'];
            }

            $goods = Db::table('__GOODS__')->alias('g')->join('__SHOPS__ s', 'g.shop_id=s.shop_id')
                ->where($where)->field('s.shop_name,s.shop_id,goods_id,goods_name,goods_img,goods_sn,goods_stock,sale_num,shop_price,market_price,is_spec,appraise_num,visit_num')
                ->order($order[$type])->limit($num)->select();
        }
        $ids = [];
        foreach ($goods as $key => $v) {
            if ($v['is_spec'] == 1) {
                $ids[] = $v['goods_id'];
            }

        }
        if (!empty($ids)) {
            $specs = [];
            $rs    = Db::table('__GOODS_SPECS__ gs ')->where(['goods_id' => ['in', $ids], 'status' => 1])->order('id asc')->select();
            foreach ($rs as $key => $v) {
                $specs[$v['goods_id']] = $v;
            }
            foreach ($goods as $key => $v) {
                if (isset($specs[$v['goods_id']])) {
                    $goods[$key]['specs'] = $specs[$v['goods_id']];
                }

            }
        }
        cache('TAG_GOODS_' . $type . "_" . $cat_id . "_" . $num, $goods, $cache);
        return $goods;
    }

    /**
     * 获取广告位置
     */
    public function listAds($position_code, $num, $cache = 0)
    {
        $cacheData = cache('TAG_ADS' . $position_code);
        if ($cacheData) {
            return $cacheData;
        }

        $today = date('Y-m-d');
        $rs    = Db::table("__ADS__")->alias('a')
            ->join('__AD_POSITIONS__ ap', 'a.ad_position_id= ap.position_id and ap.status=1', 'left')
            ->where("a.status=1 and ap.position_code='" . $position_code . "' and ad_start_date<= '$today' and ad_end_date>='$today'")
            ->field('ad_id,ad_name,ad_url,ad_file,position_width,position_height')
            ->order('ad_sort asc')->limit($num)->select();
        cache('TAG_ADS' . $position_code, $rs, $cache);
        return $rs;
    }

    /**
     * 获取友情链接
     */
    public function listFriendlink($num, $cache = 0)
    {
        $cacheData = cache('TAG_FRIENDLINK');
        if ($cacheData) {
            return $cacheData;
        }

        $rs = Db::table("__FRIENDLINKS__")->where(["status" => 1])->order("friend_link_sort asc")->select();
        cache('TAG_FRIENDLINK', $rs, $cache);
        return $rs;
    }

    /**
     * 获取文章列表
     */
    public function listArticle($cat_id, $num, $cache = 0)
    {
        $cacheData = cache('TAG_ARTICLES_' . $cat_id . "_" . $num);
        if ($cacheData) {
            return $cacheData;
        }

        $rs = [];
        if ($cat_id == 'new') {
            $rs = $this->listByNewArticle($num, $cache);
        } else {
            $rs = $this->listByArticle($cat_id, $num, $cache);
        }
        cache('TAG_ARTICLES_' . $cat_id . "_" . $num, $rs, $cache);
        return $rs;
    }
    /**
     * 获取最新文章
     */
    public function listByNewArticle($num, $cache)
    {
        $cacheData = cache('TAG_NEW_ARTICLES');
        if ($cacheData) {
            return $cacheData;
        }

        $rs = Db::table('__ARTICLES__')->alias('a')->field('a.article_id,a.article_title')->join('__ARTICLE_CATS__ ac', 'a.cat_id=ac.cat_id', 'inner')
            ->where('a.cat_id<>7 and ac.parent_id<>7 and a.status=1')->order('a.create_time', 'desc')->limit($num)->select();
        cache('TAG_NEW_ARTICLES', $rs, $cache);
        return $rs;
    }
    /**
     * 获取指定分类的文章
     */
    public function listByArticle($cat_id, $num, $cache)
    {
        $where             = [];
        $where['status'] = 1;
        $where['is_show']   = 1;
        if (is_array($cat_id)) {
            $where['cat_id'] = ['in', $cat_id];
        } else {
            $where['cat_id'] = $cat_id;
        }
        return Db::table('__ARTICLES__')->where($where)
            ->field("article_id, cat_id, article_title")->order('create_time desc')->limit($num)->select();
    }

    /**
     * 获取指定店铺商品
     */
    public function listShopGoods($type, $shop_id, $num, $cache = 0)
    {
        $cacheData = cache('TAG_SHOP_GOODS_' . $type . "_" . $shop_id);
        if ($cacheData) {
            return $cacheData;
        }

        if (!in_array($type, [0, 1, 2, 3])) {
            return [];
        }

        $types                = ['recom' => 'is_recom', 'new' => 'is_new', 'hot' => 'is_hot', 'best' => 'is_best'];
        $order                = ['recom' => 'sale_num desc,goods_id asc', 'new' => 'sale_time desc,goods_id asc', 'hot' => 'sale_num desc,goods_id asc', 'best' => 'sale_num desc,goods_id asc'];
        $where                = [];
        $where['shop_id']      = $shop_id;
        $where['is_sale']      = 1;
        $where['goods_status'] = 1;
        $where['status']    = 1;
        $where[$types[$type]] = 1;
        $goods                = Db::table('__GOODS__')
            ->where($where)->field('goods_id,goods_name,goods_img,goods_sn,goods_stock,sale_num,shop_price,market_price,is_spec,appraise_num,visit_num')
            ->order($order[$type])->limit($num)->select();
        $ids = [];
        foreach ($goods as $key => $v) {
            if ($v['is_spec'] == 1) {
                $ids[] = $v['goods_id'];
            }

        }
        if (!empty($ids)) {
            $specs = [];
            $rs    = Db::table('__GOODS_SPECS__ gs ')->where(['goods_id' => ['in', $ids], 'status' => 1])->order('id asc')->select();
            foreach ($rs as $key => $v) {
                $specs[$v['goods_id']] = $v;
            }
            foreach ($goods as $key => $v) {
                if (isset($specs[$v['goods_id']])) {
                    $goods[$key]['specs'] = $specs[$v['goods_id']];
                }

            }
        }
        cache('TAG_SHOP_GOODS_' . $type . "_" . $shop_id, $goods, $cache);
        return $goods;
    }
    /**
     * 获取店铺分类下的商品
     */
    public function listShopFloorGoods($cat_id, $shop_id, $num, $cache = 0)
    {
        $cacheData = cache('TAG_SHOP_CAT_GOODS_' . $cat_id . "_" . $shop_id);
        if ($cacheData) {
            return $cacheData;
        }

        $where                = [];
        $where['shop_id']      = $shop_id;
        $where['is_sale']      = 1;
        $where['goods_status'] = 1;
        $where['status']    = 1;
        $where['shop_cat_id2']  = $cat_id;
        $goods                = Db::table('__GOODS__')
            ->where($where)->field('goods_id,goods_name,goods_img,goods_sn,goods_stock,sale_num,shop_price,market_price,is_spec,appraise_num,visit_num')
            ->limit($num)->select();
        cache('TAG_SHOP_CAT_GOODS_' . $cat_id . "_" . $shop_id, $goods, $cache);
        return $goods;
    }
}
