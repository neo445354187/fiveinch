<?php
namespace fi\admin\model;

use think\Db;
use fi\common\model\Redundancy;
/**
 * 商品类
 */
class Goods extends Base
{
    /**
     *  上架商品列表
     */
    public function saleByPage()
    {
        $where                  = [];
        $where['g.goods_status'] = 1;
        $where['g.status']    = 1;
        $where['g.is_sale']      = 1;
        $area_id_path             = input('area_id_path');
        $goods_cat_id_path         = input('goods_cat_id_path');
        $goods_name              = input('goods_name');
        $shop_name               = input('shop_name');
        if ($area_id_path != '') {
            $where['area_id_path'] = ['like', $area_id_path . "%"];
        }

        if ($goods_cat_id_path != '') {
            $where['goods_cat_id_path'] = ['like', $goods_cat_id_path . "%"];
        }

        if ($goods_name != '') {
            $where['goods_name|goods_sn'] = ['like', "%$goods_name%"];
        }

        if ($shop_name != '') {
            $where['shop_name|shop_sn'] = ['like', "%$shop_name%"];
        }

        $keyCats = model('GoodsCats')->listKeyAll();
        $rs      = $this->alias('g')->join('__SHOPS__ s', 'g.shop_id=s.shop_id', 'left')
            ->where($where)
            ->field('goods_id,goods_name,goods_sn,sale_num,shop_price,g.shop_id,goods_img,s.shop_name,goods_cat_id_path')
            ->order('sale_time', 'desc')
            ->paginate(input('pagesize/d'))->toArray();
        foreach ($rs['Rows'] as $key => $v) {
            $rs['Rows'][$key]['verfiycode']   = FIShopEncrypt($v['shop_id']);
            $rs['Rows'][$key]['goodsCatName'] = self::getGoodsCatNames($v['goods_cat_id_path'], $keyCats);
        }
        return $rs;
    }

    public function getGoodsCatNames($goods_cat_path, $keyCats)
    {
        $cat_ids   = explode("_", $goods_cat_path);
        $cat_names = array();
        for ($i = 0, $k = count($cat_ids); $i < $k; $i++) {
            if ($cat_ids[$i] == '') {
                continue;
            }

            if (isset($keyCats[$cat_ids[$i]])) {
                $cat_names[] = $keyCats[$cat_ids[$i]];
            }

        }
        return implode("→", $cat_names);
    }
    /**
     * 审核中的商品
     */
    public function auditByPage()
    {
        $where['goods_status'] = 0;
        $where['g.status']  = 1;
        $where['is_sale']      = 1;
        $area_id_path           = input('area_id_path');
        $goods_cat_id_path       = input('goods_cat_id_path');
        $goods_name            = input('goods_name');
        $shop_name             = input('shop_name');
        if ($area_id_path != '') {
            $where['area_id_path'] = ['like', $area_id_path . "%"];
        }

        if ($goods_cat_id_path != '') {
            $where['goods_cat_id_path'] = ['like', $goods_cat_id_path . "%"];
        }

        if ($goods_name != '') {
            $where['goods_name|goods_sn'] = ['like', "%$goods_name%"];
        }

        if ($shop_name != '') {
            $where['shop_name|shop_sn'] = ['like', "%$shop_name%"];
        }

        $keyCats = model('GoodsCats')->listKeyAll();
        $rs      = $this->alias('g')->join('__SHOPS__ s', 'g.shop_id=s.shop_id', 'left')
            ->where($where)
            ->field('goods_id,goods_name,goods_sn,sale_num,shop_price,goods_img,s.shop_name,s.shop_id,goods_cat_id_path')
            ->order('sale_time', 'desc')
            ->paginate(input('pagesize/d'))->toArray();
        foreach ($rs['Rows'] as $key => $v) {
            $rs['Rows'][$key]['verfiycode']   = FIShopEncrypt($v['shop_id']);
            $rs['Rows'][$key]['goodsCatName'] = self::getGoodsCatNames($v['goods_cat_id_path'], $keyCats);
        }
        return $rs;
    }
    /**
     * 违规的商品
     */
    public function illegalByPage()
    {
        $where['goods_status'] = -1;
        $where['g.status']  = 1;
        $where['is_sale']      = 1;
        $area_id_path           = input('area_id_path');
        $goods_cat_id_path       = input('goods_cat_id_path');
        $goods_name            = input('goods_name');
        $shop_name             = input('shop_name');
        if ($area_id_path != '') {
            $where['area_id_path'] = ['like', $area_id_path . "%"];
        }

        if ($goods_cat_id_path != '') {
            $where['goods_cat_id_path'] = ['like', $goods_cat_id_path . "%"];
        }

        if ($goods_name != '') {
            $where['goods_name|goods_sn'] = ['like', "%$goods_name%"];
        }

        if ($shop_name != '') {
            $where['shop_name|shop_sn'] = ['like', "%$shop_name%"];
        }

        $keyCats = model('GoodsCats')->listKeyAll();
        $rs      = $this->alias('g')->join('__SHOPS__ s', 'g.shop_id=s.shop_id', 'left')
            ->where($where)
            ->field('goods_id,goods_name,goods_sn,goods_img,s.shop_name,s.shop_id,illegal_remarks,goods_cat_id_path')
            ->order('sale_time', 'desc')
            ->paginate(input('pagesize/d'))->toArray();
        foreach ($rs['Rows'] as $key => $v) {
            $rs['Rows'][$key]['verfiycode']   = FIShopEncrypt($v['shop_id']);
            $rs['Rows'][$key]['goodsCatName'] = self::getGoodsCatNames($v['goods_cat_id_path'], $keyCats);
        }
        return $rs;
    }

    /**
     * 获取商品资料方便编辑
     */
    public function getById($goods_id)
    {
        $rs = $this->where(['shop_id' => (int) session('FI_USER.shop_id'), 'goods_id' => $goods_id])->find();
        if (!empty($rs)) {
            if ($rs['gallery'] != '') {
                $rs['gallery'] = explode(',', $rs['gallery']);
            }

            //获取规格值
            $specs = Db::table('__SPEC_CATS__')->alias('gc')->join('__SPEC_ITEMS__ sit', 'gc.cat_id=sit.cat_id', 'inner')
                ->where(['sit.goods_id' => $goods_id, 'gc.is_show' => 1, 'sit.status' => 1])
                ->field('gc.is_allow_img,sit.cat_id,sit.item_id,sit.item_name,sit.item_img')
                ->order('gc.is_allow_img desc,gc.cat_sort asc,gc.cat_id asc')->select();
            $spec0 = [];
            $spec1 = [];
            foreach ($specs as $key => $v) {
                if ($v['is_allow_img'] == 1) {
                    $spec0[] = $v;
                } else {
                    $spec1[] = $v;
                }
            }
            $rs['spec0'] = $spec0;
            $rs['spec1'] = $spec1;
            //获取销售规格
            $rs['saleSpec'] = model('goodsSpecs')->where('goods_id', $goods_id)->field('id,is_default,product_no,spec_ids,market_price,spec_price,spec_stock,warn_stock,sale_num')->select();
            //获取属性值
            $rs['attrs'] = model('goodsAttributes')->where('goods_id', $goods_id)->field('attr_id,attr_val')->select();
        }
        return $rs;
    }

    /**
     * 获取商品资料在前台展示
     */
    public function getBySale($goods_id)
    {
        $key = input('key');
        $rs  = $this->where(['goods_id' => $goods_id, 'status' => 1])->find()->toArray();
        if (!empty($rs)) {
            $rs['read'] = false;
            //判断是否可以公开查看
            $viKey = FIShopEncrypt($rs['shop_id']);
            if (($rs['is_sale'] == 0 || $rs['goods_status'] == 0) && $viKey != $key) {
                return [];
            }

            if ($key != '') {
                $rs['read'] = true;
            }

            //获取店铺信息
            $rs['shop'] = model('shops')->getBriefShop((int) $rs['shop_id']);
            if (empty($rs['shop'])) {
                return [];
            }

            $gallery   = [];
            $gallery[] = $rs['goods_img'];
            if ($rs['gallery'] != '') {
                $tmp     = explode(',', $rs['gallery']);
                $gallery = array_merge($gallery, $tmp);
            }
            $rs['gallery'] = $gallery;
            //获取规格值
            $specs = Db::table('__SPEC_CATS__')->alias('gc')->join('__SPEC_ITEMS__ sit', 'gc.cat_id=sit.cat_id', 'inner')
                ->where(['sit.goods_id' => $goods_id, 'gc.is_show' => 1, 'sit.status' => 1])
                ->field('gc.is_allow_img,gc.cat_name,sit.cat_id,sit.item_id,sit.item_name,sit.item_img')
                ->order('gc.is_allow_img desc,gc.cat_sort asc,gc.cat_id asc')->select();
            foreach ($specs as $key => $v) {
                $rs['spec'][$v['cat_id']]['name']   = $v['cat_name'];
                $rs['spec'][$v['cat_id']]['list'][] = $v;
            }
            //获取销售规格
            $sales = model('goodsSpecs')->where('goods_id', $goods_id)->field('id,is_default,product_no,spec_ids,market_price,spec_price,spec_stock')->select();
            if (!empty($sales)) {
                foreach ($sales as $key => $v) {
                    $str = explode(':', $v['spec_ids']);
                    sort($str);
                    unset($v['spec_ids']);
                    $rs['saleSpec'][implode(':', $str)] = $v;
                }
            }
            //获取商品属性
            $rs['attrs'] = Db::table('__ATTRIBUTES__')->alias('a')->join('__GOODS_ATTRIBUTES__ ga', 'a.attr_id=ga.attr_id', 'inner')
                ->where(['a.is_show' => 1, 'status' => 1, 'goods_id' => $goods_id])->field('a.attr_name,ga.attr_val')
                ->order('attr_sort asc')->select();
        }
        return $rs;
    }

    /**
     * 删除商品
     */
    public function del()
    {
        $id               = input('post.id/d');
        $data             = [];
        $data['status'] = -1;
        Db::startTrans();
        try {
            $result = $this->update($data, ['goods_id' => $id]);
            if (false !== $result) {
                Db::table('__CARTS__')->where('goods_id', $id)->delete();
                FIUnuseImage('goods', 'goods_img', $id);
                FIUnuseImage('goods', 'gallery', $id);
                //删除冗余数据
                (new Redundancy())->del($id);

                Db::commit();
                //标记删除购物车
                return FIReturn("删除成功", 1);
            }
        } catch (\Exception $e) {
            Db::rollback();
        }
        return FIReturn('删除失败', -1);
    }
    /**
     * 批量删除商品
     */
    public function batchDel()
    {
        $shop_id = (int) session('FI_USER.shop_id');
        $ids    = input('post.ids/a');
        Db::startTrans();
        try {
            $rs = $this->where(['goods_id' => ['in', $ids],
                'shop_id'                      => $shop_id])->setField('status', -1);
            if (false !== $rs) {
                Db::table('__CARTS__')->where(['goods_id' => ['in', $ids]])->delete();
                //标记删除购物车
                foreach ($ids as $v) {
                    FIUnuseImage('goods', 'goods_img', (int) $v);
                    FIUnuseImage('goods', 'gallery', (int) $v);
                }
                //删除冗余数据
                (new Redundancy())->del($ids);

                Db::commit();
                return FIReturn("删除成功", 1);
            }
        } catch (\Exception $e) {
            Db::rollback();
        }
        return FIReturn('删除失败', -1);
    }

    /**
     * 设置商品违规状态
     */
    public function illegal()
    {
        $illegal_remarks = input('post.illegal_remarks');
        $id             = (int) input('post.id');
        if ($illegal_remarks == '') {
            return FIReturn("请输入违规原因");
        }

        //判断商品状态
        $rs = $this->alias('g')->join('__SHOPS__ s', 'g.shop_id=s.shop_id', 'left')->where('goods_id', $id)
            ->field('s.user_id,g.goods_name,g.goods_sn,g.goods_status,g.goods_id')->find();

        if ((int) $rs['goods_id'] == 0) {
            return FIReturn("无效的商品");
        }

        if ((int) $rs['goods_status'] != 1) {
            return FIReturn("操作失败，商品状态已发生改变，请刷新后再尝试");
        }

        Db::startTrans();
        try {
            $res = $this->setField(['goods_id' => $id, 'goods_status' => -1, 'illegal_remarks' => $illegal_remarks]);
            if ($res !== false) {
                Db::table('__CARTS__')->where(['goods_id' => $id])->delete();
                //发送一条商家信息
                FISendMsg($rs['user_id'], "您的商品" . $rs['goods_name'] . "【" . $rs['goods_sn'] . "】因【" . $illegal_remarks . "】被下架处理。", ['from' => 2, 'data_id' => $id]);
                //删除冗余数据
                (new Redundancy())->del($id);

                Db::commit();
                return FIReturn('操作成功', 1);
            }
        } catch (\Exception $e) {
            Db::rollback();
        }
        return FIReturn('删除失败', -1);
    }
    /**
     * 通过商品审核
     */
    public function allow()
    {
        $id = (int) input('post.id');
        //判断商品状态
        $rs = $this->alias('g')->join('__SHOPS__ s', 'g.shop_id=s.shop_id', 'left')->where('goods_id', $id)
            ->field('s.user_id,g.goods_name,g.goods_sn,g.goods_status,g.goods_id')->find();

        if ((int) $rs['goods_id'] == 0) {
            return FIReturn("无效的商品");
        }

        if ((int) $rs['goods_status'] == 1) {
            return FIReturn("操作失败，商品状态已发生改变，请刷新后再尝试");
        }
        Db::startTrans();
        try {
            $res = $this->setField(['goods_id' => $id, 'goods_status' => 1]);
            if ($res !== false) {
                //发送一条商家信息
                FISendMsg($rs['user_id'], "您的商品" . $rs['goods_name'] . "【" . $rs['goods_sn'] . "】已审核通过。", ['from' => 2, 'data_id' => $id]);
                //删除冗余数据
                (new Redundancy())->add($id, true);

                Db::commit();
                return FIReturn('操作成功', 1);
            }
        } catch (\Exception $e) {
            Db::rollback();
        }
        return FIReturn('操作失败', -1);
    }

    /**
     * 查询商品
     */
    public function searchQuery()
    {
        $goodsCatatId = (int) input('post.goods_cat_id');
        if ($goodsCatatId <= 0) {
            return [];
        }

        $goods_cat_ids             = FIGoodsCatPath($goodsCatatId);
        $key                     = input('post.key');
        $where                   = [];
        $where['g.status']     = 1;
        $where['g.is_sale']       = 1;
        $where['g.goods_status']  = 1;
        $where['goods_cat_id_path'] = ['like', implode('_', $goods_cat_ids) . '_%'];
        if ($key != '') {
            $where['goods_name|shop_name'] = ['like', '%' . $key . '%'];
        }

        return $this->alias('g')->join('__SHOPS__ s', 'g.shop_id=s.shop_id', 'inner')
            ->where($where)->field('g.goods_name,s.shop_name,g.goods_id')->limit(50)->select();
    }
}
