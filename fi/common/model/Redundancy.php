<?php
namespace fi\common\model;

use fi\common\cron\Solr;
use fi\common\helper\Tree;
use fi\common\model\Areas;
use think\Db;

/**
 * 冗余表模型
 */
class Redundancy extends Base
{

    /**
     * [del 删除商品触发的冗余表删除记录]
     * @param  [int|array] $goods_id [商品goods_id， 可以执行批量操作]
     * @return [type]     [description]
     */
    public function del($goods_id)
    {
        $goods_id = (array) $goods_id;
        return $this->where(['goods_id' => ['IN', $goods_id]])
            ->update([
                'update_time' => date('Y-m-d H:i:s'),
                'is_delete'   => 1,
            ]);
    }

    /**
     * [add 添加记录]
     *  说明：商家可以操作多个，即$goods_id为数组，而后台只能操作一个，即$goods_id为int
     * @param [int|array] $goods_id [description]
     * @param  [type] $is_manager [判断是否是管理员操作，这项决定地址从哪里取]
     */
    public function add($goods_id, $is_manager = false)
    {
        $goods_id = (array) $goods_id;
        $result  = Db::name('Goods')
            ->field('goods_id, goods_name, shop_id, goods_cat_id, brand_id, goods_img, shop_price, goods_stock, goods_tips, goods_unit, goods_desc, appraise_num, sale_num')
            ->where(['goods_id' => ['IN', $goods_id]])
            ->limit(count($goods_id))
            ->toArray()
            ->select();
        if ($result) {
            //放置商品多时，连接查询耗时
            foreach ($result as $key => $res) {
                $shop_ids[]     = $res['shop_id'];
                $goods_cat_ids[] = $res['goods_cat_id'];
                $brand_ids[]    = $res['brand_id'];
            }
            //分开获取店铺名
            $shops = Db::name('shops')
                ->field('shop_id, shop_name, area_id_path')
                ->where(['shop_id' => ['IN', $shop_ids]])
                ->limit(count($shop_ids))
                ->toArray()
                ->select();
            $data['shop_name'] = Tree::setKey($shops, 'shop_id');

            //分开获取商品分类
            $data['cat_name'] = Tree::setKey(
                Db::name('goods_cats')
                    ->field('cat_id, cat_name')
                    ->where(['cat_id' => ['IN', $goods_cat_ids]])
                    ->limit(count($goods_cat_ids))
                    ->toArray()
                    ->select(),
                'cat_id'
            );
            //分开获取商品品牌
            $data['brand_name'] = Tree::setKey(
                Db::name('brands')
                    ->field('brand_id, brand_name')
                    ->where(['brand_id' => ['IN', $brand_ids]])
                    ->limit(count($brand_ids))
                    ->toArray()
                    ->select(),
                'brand_id'
            );

            //获取商品分数
            $data['avg_score'] = Tree::setKey(
                Db::name('goods_scores')
                    ->field('goods_id, total_score, total_users')
                    ->where(['goods_id' => ['IN', $goods_id]])
                    ->limit(count($goods_id))
                    ->toArray()
                    ->select(),
                'goods_id'
            );

            //根据$is_manager获取地址 从session中获取地址
            if ($is_manager) {
                $userInfo = (new Areas)->getLocationByAreaIdPath($shops[0]['area_id_path']);
            } else {
                $userInfo = session('FI_USER');
            }
            $timeStamp = date('Y-m-d H:i:s');

            //组装最后的数据
            foreach ($result as $key => $res) {
                @$res['shop_name']   = $data['shop_name'][$res['shop_id']]['shop_name'];
                @$res['cat_name']    = $data['cat_name'][$res['goods_cat_id']]['cat_name']; //因为可能没有分类
                @$res['brand_name']  = $data['brand_name'][$res['brand_id']]['brand_name']; //因为可能没有品牌
                @$res['avg_score']   = ($data['avg_score'][$res['goods_id']]['total_score'] == 0) ? 5 : FIScore($data['avg_score'][$res['goods_id']]['total_score'], $data['avg_score'][$res['goods_id']]['total_users'], 5, 0, 3);
                $res['province']    = $userInfo['province'];
                $res['city']        = $userInfo['city'];
                $res['district']    = $userInfo['district'];
                $res['update_time'] = $timeStamp;
                unset( $res['goods_cat_id'], $res['brand_id']);
                $result[$key] = $res;
            }
            // var_dump($result);die;//debug
            return $this->insertAll($result);
        }
    }

    /**
     * [edit 更改冗余记录，采用先删除后更改]
     * @param  [type] $goods_id [description]
     * @param  [type] $is_manager [判断是否是管理员操作，这项决定地址从哪里取]
     * @return [type]          [description]
     */
    public function edit($goods_id, $is_manager = false)
    {
        $this->del($goods_id);
        $this->add($goods_id, $is_manager);
    }

    /**
     * [__destruct 执行完操作后，调用solr执行增量操作]
     */
    public function __destruct()
    {
        (new Solr)->deltaImport();
    }

}
