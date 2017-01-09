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
     * @param  [int|array] $goodsId [商品goodsId， 可以执行批量操作]
     * @return [type]     [description]
     */
    public function del($goodsId)
    {
        $goodsId = (array) $goodsId;
        return $this->where(['goodsId' => ['IN', $goodsId]])
            ->update([
                'update_time' => date('Y-m-d H:i:s'),
                'is_delete'   => 1,
            ]);
    }

    /**
     * [add 添加记录]
     *  说明：商家可以操作多个，即$goodsId为数组，而后台只能操作一个，即$goodsId为int
     * @param [int|array] $goodsId [description]
     * @param  [type] $is_manager [判断是否是管理员操作，这项决定地址从哪里取]
     */
    public function add($goodsId, $is_manager = false)
    {
        $goodsId = (array) $goodsId;
        $result  = Db::name('Goods')
            ->field('goodsId, goodsName, shopId, goodsCatId, brandId, goodsImg, shopPrice, goodsStock, goodsTips, goodsUnit, goodsDesc, appraiseNum, saleNum')
            ->where(['goodsId' => ['IN', $goodsId]])
            ->limit(count($goodsId))
            ->toArray()
            ->select();
        if ($result) {
            //放置商品多时，连接查询耗时
            foreach ($result as $key => $res) {
                $shopIds[]     = $res['shopId'];
                $goodsCatIds[] = $res['goodsCatId'];
                $brandIds[]    = $res['brandId'];
            }
            //分开获取店铺名
            $shops = Db::name('shops')
                ->field('shopId, shopName, areaIdPath')
                ->where(['shopId' => ['IN', $shopIds]])
                ->limit(count($shopIds))
                ->toArray()
                ->select();
            $data['shopName'] = Tree::setKey($shops, 'shopId');

            //分开获取商品分类
            $data['catName'] = Tree::setKey(
                Db::name('goods_cats')
                    ->field('catId, catName')
                    ->where(['catId' => ['IN', $goodsCatIds]])
                    ->limit(count($goodsCatIds))
                    ->toArray()
                    ->select(),
                'catId'
            );
            //分开获取商品品牌
            $data['brandName'] = Tree::setKey(
                Db::name('brands')
                    ->field('brandId, brandName')
                    ->where(['brandId' => ['IN', $brandIds]])
                    ->limit(count($brandIds))
                    ->toArray()
                    ->select(),
                'brandId'
            );

            //获取商品分数
            $data['avgScore'] = Tree::setKey(
                Db::name('goods_scores')
                    ->field('goodsId, totalScore, totalUsers')
                    ->where(['goodsId' => ['IN', $goodsId]])
                    ->limit(count($goodsId))
                    ->toArray()
                    ->select(),
                'goodsId'
            );

            //根据$is_manager获取地址 从session中获取地址
            if ($is_manager) {
                $userInfo = (new Areas)->getLocationByAreaIdPath($shops[0]['areaIdPath']);
            } else {
                $userInfo = session('FI_USER');
            }
            $timeStamp = date('Y-m-d H:i:s');

            //组装最后的数据
            foreach ($result as $key => $res) {
                @$res['shopName']   = $data['shopName'][$res['shopId']]['shopName'];
                @$res['catName']    = $data['catName'][$res['goodsCatId']]['catName']; //因为可能没有分类
                @$res['brandName']  = $data['brandName'][$res['brandId']]['brandName']; //因为可能没有品牌
                @$res['avgScore']   = ($data['avgScore'][$res['goodsId']]['totalScore'] == 0) ? 5 : FIScore($data['avgScore'][$res['goodsId']]['totalScore'], $data['avgScore'][$res['goodsId']]['totalUsers'], 5, 0, 3);
                $res['province']    = $userInfo['province'];
                $res['city']        = $userInfo['city'];
                $res['district']    = $userInfo['district'];
                $res['update_time'] = $timeStamp;
                unset( $res['goodsCatId'], $res['brandId']);
                $result[$key] = $res;
            }
            // var_dump($result);die;//debug
            return $this->insertAll($result);
        }
    }

    /**
     * [edit 更改冗余记录，采用先删除后更改]
     * @param  [type] $goodsId [description]
     * @param  [type] $is_manager [判断是否是管理员操作，这项决定地址从哪里取]
     * @return [type]          [description]
     */
    public function edit($goodsId, $is_manager = false)
    {
        $this->del($goodsId);
        $this->add($goodsId, $is_manager);
    }

    /**
     * [__destruct 执行完操作后，调用solr执行增量操作]
     */
    public function __destruct()
    {
        (new Solr)->deltaImport();
    }

}
