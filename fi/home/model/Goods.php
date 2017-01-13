<?php

namespace fi\home\model;

use fi\common\model\Redundancy;
use think\Db;

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
        $shop_id               = SID;
        $where                = [];
        $where['shop_id']      = $shop_id;
        $where['goods_status'] = 1;
        $where['status']    = 1;
        $where['is_sale']      = 1;
        $c1Id                 = (int) input('cat1');
        $c2Id                 = (int) input('cat2');
        $goods_name            = input('goods_name');
        if ($goods_name != '') {
            $where['goods_name'] = ['like', "%$goods_name%"];
        }
        if ($c2Id != 0 && $c1Id != 0) {
            $where['shop_cat_id2'] = $c2Id;
        } else if ($c1Id != 0) {
            $where['shop_cat_id1'] = $c1Id;
        }
        $shop_id            = (int) session('FI_USER.shop_id');
        $where['m.shop_id'] = $shop_id;
        $rs                = $this->alias('m')
            ->where($where)
            ->field('goods_id,goods_name,goods_img,goods_sn,is_sale,is_best,is_hot,is_new,is_recom,goods_stock,sale_num,shop_price,is_spec')
            ->order('sale_time', 'desc')
            ->paginate(input('pagesize/d'))->toArray();
        foreach ($rs['Rows'] as $key => $v) {
            $rs['Rows'][$key]['verfiycode'] = FIShopEncrypt($shop_id);
        }
        return $rs;
    }

    /**
     * 审核中的商品
     */
    public function auditByPage()
    {
        $where['shop_id']      = SID;
        $where['goods_status'] = 0;
        $where['status']    = 1;
        $where['is_sale']      = 1;
        $c1Id                 = (int) input('cat1');
        $c2Id                 = (int) input('cat2');
        $goods_name            = input('goods_name');
        if ($goods_name != '') {
            $where['goods_name'] = ['like', "%$goods_name%"];
        }
        if ($c2Id != 0 && $c1Id != 0) {
            $where['shop_cat_id2'] = $c2Id;
        } else if ($c1Id != 0) {
            $where['shop_cat_id1'] = $c1Id;
        }

        $rs = $this->alias('m')
            ->where($where)
            ->field('goods_id,goods_name,goods_img,goods_sn,is_sale,is_best,is_hot,is_new,is_recom,goods_stock,sale_num,shop_price,is_spec')
            ->order('sale_time', 'desc')
            ->paginate(input('pagesize/d'))->toArray();
        foreach ($rs['Rows'] as $key => $v) {
            $rs['Rows'][$key]['verfiycode'] = FIShopEncrypt($shop_id);
        }
        return $rs;
    }

    /**
     * 仓库中的商品
     */
    public function storeByPage()
    {
        $shop_id            = SID;
        $where['shop_id']   = $shop_id;
        $where['status'] = 1;
        $where['is_sale']   = 0;
        $c1Id              = (int) input('cat1');
        $c2Id              = (int) input('cat2');
        $goods_name         = input('goods_name');
        if ($goods_name != '') {
            $where['goods_name'] = ['like', "%$goods_name%"];
        }
        if ($c2Id != 0 && $c1Id != 0) {
            $where['shop_cat_id2'] = $c2Id;
        } else if ($c1Id != 0) {
            $where['shop_cat_id1'] = $c1Id;
        }
        $rs = $this->alias('m')
            ->where($where)
            ->where('goods_status', '<>', -1)
            ->field('goods_id,goods_name,goods_img,goods_sn,is_sale,is_best,is_hot,is_new,is_recom,goods_stock,sale_num,shop_price,is_spec')
            ->order('sale_time', 'desc')
            ->paginate(input('pagesize/d'))->toArray();
        foreach ($rs['Rows'] as $key => $v) {
            $rs['Rows'][$key]['verfiycode'] = FIShopEncrypt($shop_id);
        }
        return $rs;
    }

    /**
     * 违规的商品
     */
    public function illegalByPage()
    {
        $shop_id               = SID;
        $where['shop_id']      = $shop_id;
        $where['goods_status'] = -1;
        $where['status']    = 1;
        $where['is_sale']      = 1;
        $c1Id                 = (int) input('cat1');
        $c2Id                 = (int) input('cat2');
        $goods_name            = input('goods_name');
        if ($goods_name != '') {
            $where['goods_name'] = ['like', "%$goods_name%"];
        }
        if ($c2Id != 0 && $c1Id != 0) {
            $where['shop_cat_id2'] = $c2Id;
        } else if ($c1Id != 0) {
            $where['shop_cat_id1'] = $c1Id;
        }

        $rs = $this->alias('m')
            ->where($where)
            ->field('goods_id,goods_name,goods_img,goods_sn,is_sale,is_best,is_hot,is_new,is_recom,illegal_remarks,goods_stock,sale_num,shop_price,is_spec')
            ->order('sale_time', 'desc')
            ->paginate(input('pagesize/d'))->toArray();
        foreach ($rs['Rows'] as $key => $v) {
            $rs['Rows'][$key]['verfiycode'] = FIShopEncrypt($shop_id);
        }
        return $rs;
    }

    /**
     *
     * 新增商品
     */
    public function add()
    {
        $shop_id   = SID;
        $data     = input('post.');
        $specsIds = input('post.specsIds');
        FIUnset($data, 'goods_id,statusRemarks,goods_status,status');
        //判断是否需要后台审核
        if (FIConf("CONF.isGoodsVerify") == 1) {
            $data['goods_status'] = 0;
        } else {
            $data['goods_status'] = 1;
        }
        $data['shop_id']         = $shop_id;
        $data['sale_time']       = date('Y-m-d H:i:s');
        $data['create_time']     = date('Y-m-d H:i:s');
        $goodsCats              = model('GoodsCats')->getParentIs($data['goods_cat_id']);
        $data['goods_cat_id_path'] = implode('_', $goodsCats) . "_";
        $data['is_spec']         = ($specsIds != '') ? 1 : 0;
        Db::startTrans();
        try {
            //保存表中有的字段的值到该模型实例上，并插入表
            $result = $this->validate(true)->allowField(true)->save($data);
            if (false !== $result) {
                $goods_id = $this->goods_id;
                //商品图片
                FIUseImages(0, $goods_id, $data['goods_img']);
                //商品相册
                FIUseImages(0, $goods_id, $data['gallery']);
                //商品描述图片
                FIEditorImageRocord(0, $goods_id, '', $data['goods_desc']);

                //建立商品评分记录
                $gs            = [];
                $gs['goods_id'] = $goods_id;
                $gs['shop_id']  = $shop_id;
                Db::name('goods_scores')->insert($gs);
                //如果有销售规格则保存销售和规格值
                if ($specsIds != '') {
                    $specsIds   = explode(',', $specsIds);
                    $specsArray = [];
                    foreach ($specsIds as $v) {
                        $vs = explode('-', $v);
                        foreach ($vs as $vv) {
                            if (!in_array($vv, $specsArray)) {
                                $specsArray[] = $vv;
                            }
                        }
                    }
                    //保存规格名称
                    $specMap = [];
                    foreach ($specsArray as $v) {
                        $vv                  = explode('_', $v);
                        $sitem               = [];
                        $sitem['shop_id']     = $shop_id;
                        $sitem['cat_id']      = (int) $vv[0];
                        $sitem['goods_id']    = $goods_id;
                        $sitem['item_name']   = input('post.specName_' . $vv[0] . "_" . $vv[1]);
                        $sitem['item_img']    = input('post.specImg_' . $vv[0] . "_" . $vv[1]);
                        $sitem['status']   = 1;
                        $sitem['create_time'] = date('Y-m-d H:i:s');
                        $item_id              = Db::name('spec_items')->insertGetId($sitem);
                        if ($sitem['item_img'] != '') {
                            FIUseImages(0, $item_id, $sitem['item_img']);
                        }

                        $specMap[$v] = $item_id;
                    }
                    //保存销售规格
                    $defaultPrice      = 0; //最低价
                    $totalStock        = 0; //总库存
                    $gspecArray        = [];
                    $isFindDefaultSpec = false;
                    $defaultSpec       = Input('post.defaultSpec');
                    foreach ($specsIds as $v) {
                        $vs           = explode('-', $v);
                        $goods_spec_ids = [];
                        foreach ($vs as $gvs) {
                            $goods_spec_ids[] = $specMap[$gvs];
                        }
                        $gspec                = [];
                        $gspec['spec_ids']     = implode(':', $goods_spec_ids);
                        $gspec['shop_id']      = $shop_id;
                        $gspec['goods_id']     = $goods_id;
                        $gspec['product_no']   = Input('product_no_' . $v);
                        $gspec['market_price'] = (float) Input('market_price_' . $v);
                        $gspec['spec_price']   = (float) Input('spec_price_' . $v);
                        $gspec['spec_stock']   = (int) Input('spec_stock_' . $v);
                        $gspec['warn_stock']   = (int) Input('warn_stock_' . $v);
                        //设置默认规格
                        if ($defaultSpec == $v) {
                            $isFindDefaultSpec  = true;
                            $defaultPrice       = $gspec['spec_price'];
                            $gspec['is_default'] = 1;
                        } else {
                            $gspec['is_default'] = 0;
                        }
                        $gspecArray[] = $gspec;
                        //获取总库存
                        $totalStock = $totalStock + $gspec['spec_stock'];
                    }
                    if (!$isFindDefaultSpec) {
                        return FIReturn("请选择推荐规格");
                    }

                    if (count($gspecArray) > 0) {
                        Db::name('goods_specs')->insertAll($gspecArray);
                        //更新默认价格和总库存
                        $this->where('goods_id', $goods_id)->update(['is_spec' => 1, 'shop_price' => $defaultPrice, 'goods_stock' => $totalStock]);
                    }
                }
                //保存商品属性
                $attrsArray = [];
                $attrRs     = Db::name('attributes')->where(['goods_cat_id' => ['in', $goodsCats], 'is_show' => 1, 'status' => 1, 'attr_type' => ['<>', 0]])
                    ->field('attr_id')->select();
                foreach ($attrRs as $key => $v) {
                    $attrs            = [];
                    $attrs['attr_val'] = input('attr_' . $v['attr_id']);
                    if ($attrs['attr_val'] == '') {
                        continue;
                    }

                    $attrs['shop_id']     = $shop_id;
                    $attrs['goods_id']    = $goods_id;
                    $attrs['attr_id']     = $v['attr_id'];
                    $attrs['create_time'] = date('Y-m-d H:i:s');
                    $attrsArray[]        = $attrs;
                }
                if (count($attrsArray) > 0) {
                    Db::name('goods_attributes')->insertAll($attrsArray);
                }
                //插入冗余数据，取决于店铺是否可以跳过审核
                if ($data['goods_status']) {
                    (new Redundancy())->add($goods_id);
                }

                Db::commit();
                return FIReturn("新增成功", 1);
            } else {
                return FIReturn($this->getError(), -1);
            }
        } catch (\Exception $e) {
            Db::rollback();
            return FIReturn('新增失败', -1);
        }
    }

    /**
     * need
     * 编辑商品资料
     */
    public function edit()
    {
        $shop_id   = SID;
        $goods_id  = input('post.goods_id/d');
        $specsIds = input('post.specsIds');
        $data     = input('post.');
        FIUnset($data, 'goods_id,status,statusRemarks,goods_status,create_time');
        $ogoods = $this->where(['goods_id' => $goods_id, 'shop_id' => SID])->field('goods_status')->find();
        if (!$ogoods) {
            return FIReturn('编辑失败', -1);
        }

        //违规商品不能直接上架
        if ($ogoods['goods_status'] != 1) {
            $data['goods_status'] = 0;
        }
        $data['sale_time']       = date('Y-m-d H:i:s');
        $goodsCats              = model('GoodsCats')->getParentIs($data['goods_cat_id']);
        $data['goods_cat_id_path'] = implode('_', $goodsCats) . "_";
        $data['is_spec']         = ($specsIds != '') ? 1 : 0;
        Db::startTrans();
        try {
            //商品图片
            FIUseImages(0, $goods_id, $data['goods_img'], 'goods', 'goods_img');
            //商品相册
            FIUseImages(0, $goods_id, $data['gallery'], 'goods', 'gallery');
            // 商品描述图片
            $desc = $this->where('goods_id', $goods_id)->value('goods_desc');
            FIEditorImageRocord(0, $goods_id, $desc, $data['goods_desc']);

            $result = $this->validate(true)->allowField(true)->save($data, ['goods_id' => $goods_id]);
            if (false !== $result) {
                /**
                 * 编辑的时候如果不想影响商品销售规格的销量，那么就要在保存的时候区别对待已经存在的规格和销售规格记录。
                 * $specNameMap的保存关系是：array('页面上生成的规格值ID'=>数据库里规则值的ID)
                 * $specIdMap的保存关系是:array('页面上生成的销售规格ID'=>数据库里销售规格ID)
                 */
                $specNameMapTmp = explode(',', input('post.specmap'));
                $specIdMapTmp   = explode(',', input('post.specidsmap'));
                $specNameMap    = []; //规格值对应关系
                $specIdMap      = []; //规格和表对应关系
                foreach ($specNameMapTmp as $key => $v) {
                    if ($v == '') {
                        continue;
                    }

                    $v                  = explode(':', $v);
                    $specNameMap[$v[1]] = $v[0]; //array('页面上的规则值ID'=>数据库里规则值的ID)
                }
                foreach ($specIdMapTmp as $key => $v) {
                    if ($v == '') {
                        continue;
                    }

                    $v                = explode(':', $v);
                    $specIdMap[$v[1]] = $v[0]; //array('页面上的销售规则ID'=>数据库里销售规格ID)
                }
                //如果有销售规格则保存销售和规格值
                if ($specsIds != '') {
                    //把之前之前的销售规格
                    $specsIds   = explode(',', $specsIds);
                    $specsArray = [];
                    foreach ($specsIds as $v) {
                        $vs = explode('-', $v);
                        foreach ($vs as $vv) {
                            if (!in_array($vv, $specsArray)) {
                                $specsArray[] = $vv;
                            }
                            //过滤出不重复的规格值
                        }
                    }
                    //先标记作废之前的规格值
                    Db::name('spec_items')->where(['shop_id' => $shop_id, 'goods_id' => $goods_id])->update(['status' => -1]);
                    //保存规格名称
                    $specMap = [];
                    foreach ($specsArray as $v) {
                        $vv                = explode('_', $v);
                        $specNumId         = $vv[0] . "_" . $vv[1];
                        $sitem             = [];
                        $sitem['item_name'] = input('post.specName_' . $specNumId);
                        $sitem['item_img']  = input('post.specImg_' . $specNumId);
                        //如果已经存在的规格值则修改，否则新增
                        if (isset($specNameMap[$specNumId]) && (int) $specNameMap[$specNumId] != 0) {
                            $sitem['status'] = 1;
                            FIUseImages(0, (int) $specNameMap[$specNumId], $sitem['item_img'], 'spec_items', 'item_img');
                            Db::name('spec_items')->where(['shop_id' => $shop_id, 'item_id' => (int) $specNameMap[$specNumId]])->update($sitem);
                            $specMap[$v] = (int) $specNameMap[$specNumId];
                        } else {
                            $sitem['goods_id']    = $goods_id;
                            $sitem['shop_id']     = $shop_id;
                            $sitem['cat_id']      = (int) $vv[0];
                            $sitem['status']   = 1;
                            $sitem['create_time'] = date('Y-m-d H:i:s');
                            $item_id              = Db::name('spec_items')->insertGetId($sitem);
                            if ($sitem['item_img'] != '') {
                                FIUseImages(0, $item_id, $sitem['item_img']);
                            }

                            $specMap[$v] = $item_id;
                        }
                    }
                    //删除已经作废的规格值
                    Db::name('spec_items')->where(['shop_id' => $shop_id, 'goods_id' => $goods_id, 'status' => -1])->delete();
                    //保存销售规格
                    $defaultPrice = 0; //默认价格
                    $totalStock   = 0; //总库存
                    $gspecArray   = [];
                    //把之前的销售规格值标记删除
                    Db::name('goods_specs')->where(['goods_id' => $goods_id, 'shop_id' => $shop_id])->update(['status' => -1, 'is_default' => 0]);
                    $isFindDefaultSpec = false;
                    $defaultSpec       = Input('post.defaultSpec');
                    foreach ($specsIds as $v) {
                        $vs           = explode('-', $v);
                        $goods_spec_ids = [];
                        foreach ($vs as $gvs) {
                            $goods_spec_ids[] = $specMap[$gvs];
                        }
                        $gspec                = [];
                        $gspec['spec_ids']     = implode(':', $goods_spec_ids);
                        $gspec['product_no']   = Input('product_no_' . $v);
                        $gspec['market_price'] = (float) Input('market_price_' . $v);
                        $gspec['spec_price']   = (float) Input('spec_price_' . $v);
                        $gspec['spec_stock']   = (int) Input('spec_stock_' . $v);
                        $gspec['warn_stock']   = (int) Input('warn_stock_' . $v);
                        //设置默认规格
                        if ($defaultSpec == $v) {
                            $gspec['is_default'] = 1;
                            $isFindDefaultSpec  = true;
                            $defaultPrice       = $gspec['spec_price'];
                        } else {
                            $gspec['is_default'] = 0;
                        }
                        //如果是已经存在的值就修改内容，否则新增
                        if (isset($specIdMap[$v]) && $specIdMap[$v] != '') {
                            $gspec['status'] = 1;
                            Db::name('goods_specs')->where(['shop_id' => $shop_id, 'id' => (int) $specIdMap[$v]])->update($gspec);
                        } else {
                            $gspec['shop_id']  = $shop_id;
                            $gspec['goods_id'] = $goods_id;
                            $gspecArray[]     = $gspec;
                        }
                        //获取总库存
                        $totalStock = $totalStock + $gspec['spec_stock'];
                    }
                    if (!$isFindDefaultSpec) {
                        return FIReturn("请选择推荐规格");
                    }

                    //删除作废的销售规格值
                    Db::name('goods_specs')->where(['goods_id' => $goods_id, 'shop_id' => $shop_id, 'status' => -1])->delete();
                    if (count($gspecArray) > 0) {
                        Db::name('goods_specs')->insertAll($gspecArray);
                    }
                    //更新推荐规格和总库存
                    $this->where('goods_id', $goods_id)->update(['is_spec' => 1, 'shop_price' => $defaultPrice, 'goods_stock' => $totalStock]);
                }
                //保存商品属性
                //删除之前的商品属性
                Db::name('goods_attributes')->where(['goods_id' => $goods_id, 'shop_id' => $shop_id])->delete();
                //新增商品属性
                $attrsArray = [];
                $attrRs     = Db::name('attributes')->where(['goods_cat_id' => ['in', $goodsCats], 'is_show' => 1, 'status' => 1])
                    ->field('attr_id')->select();
                foreach ($attrRs as $key => $v) {
                    $attrs            = [];
                    $attrs['attr_val'] = input('attr_' . $v['attr_id']);
                    if ($attrs['attr_val'] == '') {
                        continue;
                    }

                    $attrs['shop_id']     = $shop_id;
                    $attrs['goods_id']    = $goods_id;
                    $attrs['attr_id']     = $v['attr_id'];
                    $attrs['create_time'] = date('Y-m-d H:i:s');
                    $attrsArray[]        = $attrs;
                }
                if (count($attrsArray) > 0) {
                    Db::name('goods_attributes')->insertAll($attrsArray);
                }

                //更改冗余数据
                (new Redundancy())->edit($goods_id);

                Db::commit();
                return FIReturn("编辑成功", 1);
            } else {
                return FIReturn($this->getError(), -1);
            }
        } catch (\Exception $e) {
            Db::rollback();
            return FIReturn('编辑失败', -1);
        }
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
            $rs['saleSpec'] = Db::name('goods_specs')->where('goods_id', $goods_id)->field('id,is_default,product_no,spec_ids,market_price,spec_price,spec_stock,warn_stock,sale_num')->select();
            //获取属性值
            $rs['attrs'] = Db::table('__GOODS_ATTRIBUTES__')->alias('ga')->join('__ATTRIBUTES__ a', 'ga.attr_id=a.attr_id', 'inner')
                ->where('goods_id', $goods_id)->field('ga.attr_id,a.attr_type,ga.attr_val')->select();
        }
        return $rs;
    }

    /**
     * 获取商品资料在前台展示
     */
    public function getBySale($goods_id)
    {
        $key = input('key');
        // 浏览量
        $this->where('goods_id', $goods_id)->setInc('visit_num', 1);
        $rs = Db::name('goods')->where(['goods_id' => $goods_id, 'status' => 1])->find();
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
            $sales = Db::name('goods_specs')->where('goods_id', $goods_id)->field('id,is_default,product_no,spec_ids,market_price,spec_price,spec_stock')->select();
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
            //获取商品评分
            $rs['scores']                = Db::name('goods_scores')->where('goods_id', $goods_id)->field('total_score,total_users')->find();
            $rs['scores']['total_scores'] = ($rs['scores']['total_score'] == 0) ? 5 : FIScore($rs['scores']['total_score'], $rs['scores']['total_users'], 5, 0, 3);
            FIUnset($rs, 'total_users');
            //关注
            $f             = model('Favorites');
            $rs['favShop'] = $f->checkFavorite($rs['shop_id'], 1);
            $rs['favGood'] = $f->checkFavorite($goods_id, 0);
        }
        return $rs;
    }

    /**
     * need
     * 删除商品
     */
    public function del()
    {
        $id               = input('post.id/d');
        $data             = [];
        $data['status'] = -1;
        Db::startTrans();
        try {
            $result = $this->update($data, ['goods_id' => $id, 'shop_id' => SID]);
            if ($result) {
                FIUnuseImage('goods', 'goods_img', $id);
                FIUnuseImage('goods', 'gallery', $id);
                // 商品描述图片
                $desc = $this->where(['goods_id' => $id, 'shop_id' => SID])->value('goods_desc');
                FIEditorImageRocord(0, $id, $desc, '');

                //插入冗余数据
                (new Redundancy())->del($id);

                Db::commit();
                //标记删除购物车
                return FIReturn("删除成功", 1);
            }
            Db::rollback();
        } catch (\Exception $e) {
            Db::rollback();
        }
        return FIReturn('删除失败', -1);
    }

    /**
     * need
     * 批量删除商品
     */
    public function batchDel()
    {
        $ids = input('post.ids/a');
        Db::startTrans();
        try {
            $rs = $this->where([
                'goods_id' => ['in', $ids],
                'shop_id'  => SID,
            ])->setField('status', -1);
            if ($rs) {
                //标记删除购物车
                foreach ($ids as $v) {
                    FIUnuseImage('goods', 'goods_img', (int) $v);
                    FIUnuseImage('goods', 'gallery', (int) $v);
                    // 商品描述图片
                    $desc = $this->where('goods_id', (int) $v)->value('goods_desc');
                    FIEditorImageRocord(0, (int) $v, $desc, '');
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
     * need
     * 批量上架商品
     *
     */
    public function changeSale()
    {
        $ids    = input('post.ids/a');
        $is_sale = (int) input('post.is_sale', 1);
        //先判断是否是店铺
        //0.核对店铺状态
        $shopRs = model('shops')->find(SID);
        if ($shopRs['shop_status'] != 1) {
            return FIReturn('上架商品失败!您的店铺权限不能出售商品，如有疑问请与商城管理员联系。', -3);
        }
        //is_sale是前台传过来的值，作为判断应该进行上架还是下架操作
        if ($is_sale == 1) {

            //直接设置上架 返回受影响条数
            $where                = [];
            $where['g.goods_id']   = ['in', $ids];
            $where['g.shop_id']    = SID;
            $where['gc.status'] = 1;
            $where['gc.is_show']   = 1;
            $where['g.goods_img']  = ['<>', ""];
            //second 因为这里必需要全部插入或全部回滚，因为不然无法确定Redundancy表如何更新数据
            Db::startTrans();
            try {
                $rs = $this->alias('g')
                    ->join('__GOODS_CATS__ gc', 'g.goods_cat_id=gc.CatId', 'inner')
                    ->where($where)->setField('is_sale', 1);
                if ($rs) {
                    (new Redundancy())->edit($ids);
                    Db::commit();
                    return FIReturn('商品上架成功', 1, ['num' => $rs]);
                } else {
                    Db::rollback();
                }

            } catch (\Exception $e) {
                Db::rollback();
            }
            return FIReturn('上架失败，请核对商品信息是否完整!', -2);

        } else {
            Db::startTrans();
            try {
                $rs = $this->where(['goods_id' => ['in', $ids], 'shop_id' => SID])->setField('is_sale', $is_sale);
                if ($rs) {
                    //更新冗余数据
                    (new Redundancy())->del($ids);
                    Db::commit();
                    return FIReturn('商品下架成功', 1);
                } else {
                    Db::rollback();
                }
            } catch (\Exception $e) {
                Db::rollback();
            }
            return FIReturn($this->getError(), -1);
        }
    }

    /**
     * 修改商品状态
     * $is前端传递的值：is_recom、is_best、is_new、is_hot
     */
    public function changSaleStatus()
    {
        $is     = input('post.is');
        $status = (input('post.status', 1) == 1) ? 0 : 1;
        $id     = (int) input('post.id');
        $rs     = $this->where(['goods_id' => $id, 'shop_id' => SID])->update([$is => $status]);

        if ($rs) {
            return FIReturn('设置成功', 1);
        } else {
            return FIReturn($this->getError(), -1);
        }
    }

    /**
     * 批量修改商品状态
     */
    public function changeGoodsStatus()
    {
        //设置为什么 hot new best rec
        $allowArr = ['is_hot', 'is_new', 'is_best', 'is_recom'];
        $is       = input('post.is');
        if (!in_array($is, $allowArr)) {
            return FIReturn('非法操作', -1);
        }

        //设置哪一个状态
        $status = input('post.status', 1);
        $ids    = input('post.ids/a');
        $rs     = $this->where(['goods_id' => ['in', $ids], 'shop_id' => SID])->setField($is, $status);
        if ($rs !== false) {
            return FIReturn('设置成功', 1);
        } else {
            return FIReturn($this->getError(), -1);
        }
    }

    /**
     * 获取商品规格属性
     */
    public function getSpecAttrs()
    {
        $goods_cat_id  = Input('post.goods_cat_id/d');
        $goods_cat_ids = model('GoodsCats')->getParentIs($goods_cat_id);
        $data        = [];
        $specs       = Db::name('spec_cats')->where(['status' => 1, 'is_show' => 1, 'goods_cat_id' => ['in', $goods_cat_ids]])->field('cat_id,cat_name,is_allow_img')->order('is_allow_img desc,cat_sort asc,cat_id asc')->select();
        $spec0       = null;
        $spec1       = [];
        foreach ($specs as $key => $v) {
            if ($v['is_allow_img'] == 1) {
                $spec0 = $v;
            } else {
                $spec1[] = $v;
            }
        }
        $data['spec0'] = $spec0;
        $data['spec1'] = $spec1;
        $data['attrs'] = Db::name('attributes')->where(['status' => 1, 'is_show' => 1, 'goods_cat_id' => ['in', $goods_cat_ids]])->field('attr_id,attr_name,attr_type,attr_val')->order('attr_sort asc,attr_id asc')->select();
        return FIReturn("", 1, $data);
    }

    /**
     * 检测商品主表的货号或者商品编号
     */
    public function checkExistGoodsKey($key, $val, $id = 0)
    {
        if (!in_array($key, array('goods_sn', 'product_no'))) {
            return FIReturn("非法的查询字段");
        }

        $conditon = [$key => $val];
        if ($id > 0) {
            $conditon['goods_id'] = ['<>', $id];
        }

        $rs = $dbo = $this->where($conditon)->count();
        return ($rs == 0) ? false : true;
    }

    /**
     * 获取符合筛选条件的商品ID
     */
    public function filterByAttributes()
    {
        $vs = input('vs');
        if ($vs == '') {
            return [];
        }

        $vs       = explode(',', $vs);
        $goods_ids = [];
        $prefix   = config('database.prefix');
        //循环遍历每个属性相关的商品ID
        foreach ($vs as $v) {
            $goods_ids2 = [];
            $attr_val   = input('v_' . (int) $v);
            if ($attr_val == '') {
                continue;
            }

            $sql = "select goods_id goods_id from " . $prefix . "goods_attributes
            where attr_id=" . (int) $v . " and find_in_set('" . $attr_val . "',attr_val) ";
            $rs = Db::query($sql);
            if (!empty($rs)) {
                foreach ($rs as $vg) {
                    $goods_ids2[] = $vg['goods_id'];
                }
            }
            //如果有一个属性是没有商品的话就不需要查了
            if (empty($goods_ids2)) {
                return [-1];
            }

            //第一次比较就先过滤，第二次以后的就找集合
            if (empty($goods_ids)) {
                $goods_ids = $goods_ids2;
            } else {
                $goods_ids = array_intersect($goods_ids, $goods_ids2);
            }
        }
        return $goods_ids;
    }

    /**
     * 获取分页商品记录
     */
    public function pageQuery($goods_cat_ids = [])
    {
        //查询条件
        $isStock              = input('isStock/d');
        $is_new                = input('is_new/d');
        $keyword              = input('keyword');
        $where                = $where2                = $where3                = [];
        $where['goods_status'] = 1;
        $where['g.status']  = 1;
        $where['is_sale']      = 1;
        if ($keyword != '') {
            $where['goods_name'] = ['like', '%' . $keyword . '%'];
        }

        //属性筛选
        $goods_ids = $this->filterByAttributes();
        if (!empty($goods_ids)) {
            $where['goods_id'] = ['in', $goods_ids];
        }

        // 发货地
        $area_id = (int) input('area_id');
        if ($area_id > 0) {
            $where['area_id'] = $area_id;
        }

        //排序条件
        $orderBy   = input('orderBy/d', 0);
        $orderBy   = ($orderBy >= 0 && $orderBy <= 4) ? $orderBy : 0;
        $order     = (input('order/d', 0) == 1) ? 1 : 0;
        $pageBy    = ['sale_num', 'shop_price', 'appraise_num', 'visit_num', 'sale_time'];
        $pageOrder = ['asc', 'desc'];
        if ($isStock == 1) {
            $where['goods_stock'] = ['>', 0];
        }

        if ($is_new == 1) {
            $where['is_new'] = ['=', 1];
        }

        if (!empty($goods_cat_ids)) {
            $where['goods_cat_id_path'] = ['like', implode('_', $goods_cat_ids) . '_%'];
        }

        $sprice = input("param.sprice"); //开始价格
        $eprice = input("param.eprice"); //结束价格
        if ($sprice != '') {
            $where['g.shop_price'] = [">=", (int) $sprice];
        }

        if ($eprice != '') {
            $where['g.shop_price'] = ["<=", (int) $eprice];
        }
        $list = Db::table("__GOODS__")->alias('g')->join("__SHOPS__ s", "g.shop_id = s.shop_id")
            ->where($where)
            ->field('goods_id,goods_name,goods_sn,goods_stock,sale_num,shop_price,market_price,is_spec,goods_img,appraise_num,visit_num,s.shop_id,shop_name')
            ->order($pageBy[$orderBy] . " " . $pageOrder[$order] . ",goods_id asc")
            ->paginate(input('pagesize/d'))->toArray();
        // var_dump($list);die;//debug
        return $list;
    }

    /**
     * 获取价格范围
     */
    public function getPriceGrade($goods_cat_ids = [])
    {
        $isStock              = input('isStock/d');
        $is_new                = input('is_new/d');
        $keyword              = input('keyword');
        $where                = $where2                = $where3                = [];
        $where['goods_status'] = 1;
        $where['g.status']  = 1;
        $where['is_sale']      = 1;
        if ($keyword != '') {
            $where['goods_name'] = ['like', '%' . $keyword . '%'];
        }

        $area_id = (int) input('area_id');
        if ($area_id > 0) {
            $where['area_id'] = $area_id;
        }

        //属性筛选
        $goods_ids = $this->filterByAttributes();
        if (!empty($goods_ids)) {
            $where['goods_id'] = ['in', $goods_ids];
        }

        //排序条件
        $orderBy   = input('orderBy/d', 0);
        $orderBy   = ($orderBy >= 0 && $orderBy <= 4) ? $orderBy : 0;
        $order     = (input('order/d', 0) == 1) ? 1 : 0;
        $pageBy    = ['sale_num', 'shop_price', 'appraise_num', 'visit_num', 'sale_time'];
        $pageOrder = ['asc', 'desc'];
        if ($isStock == 1) {
            $where['goods_stock'] = ['>', 0];
        }

        if ($is_new == 1) {
            $where['is_new'] = ['=', 1];
        }

        if (!empty($goods_cat_ids)) {
            $where['goods_cat_id_path'] = ['like', implode('_', $goods_cat_ids) . '_%'];
        }

        $sprice = input("param.sprice"); //开始价格
        $eprice = input("param.eprice"); //结束价格
        if ($sprice != '') {
            $where['g.shop_price'] = [">=", (int) $sprice];
        }

        if ($eprice != '') {
            $where['g.shop_price'] = ["<=", (int) $eprice];
        }

        $rs = Db::table("__GOODS__")->alias('g')->join("__SHOPS__ s", "g.shop_id = s.shop_id", 'inner')
            ->where($where)
            ->field('min(shop_price) minPrice,max(shop_price) maxPrice')->find();

        if ($rs['maxPrice'] == '') {
            return;
        }

        $minPrice    = 0;
        $maxPrice    = $rs['maxPrice'];
        $pavg5       = ($maxPrice / 5);
        $prices      = array();
        $price_grade = 0.0001;
        for ($i = -2; $i <= log10($maxPrice); $i++) {
            $price_grade *= 10;
        }
        //区间跨度
        $span = ceil(($maxPrice - $minPrice) / 8 / $price_grade) * $price_grade;
        if ($span == 0) {
            $span = $price_grade;
        }
        for ($i = 1; $i <= 8; $i++) {
            $prices[($i - 1) * $span . "_" . ($span * $i)] = ($i - 1) * $span . "-" . ($span * $i);
            if (($span * $i) > $maxPrice) {
                break;
            }
        }

        return $prices;
    }

    /**
     * need
     * 修改商品库存/价格
     */
    public function editGoodsBase()
    {
        $goods_id = (int) Input("goods_id");
        $post    = input('post.');
        $data    = [];
        if (isset($post['goods_stock'])) {
            $data['goods_stock'] = (int) input('post.goods_stock', 0);
        } elseif (isset($post['shop_price'])) {
            $data['shop_price'] = (int) input('post.shop_price', 0);
        } else {
            return FIReturn('操作失败', -1);
        }
        $rs = $this->where(['goods_id' => $goods_id, 'shop_id' => SID])->update($data);
        if ($rs !== false) {
            //更新冗余数据
            (new Redundancy())->edit($goods_id);
            return FIReturn('操作成功', 1);
        } else {
            return FIReturn('操作失败', -1);
        }
    }

    /**
     * 获取店铺商品列表
     */
    public function shopGoods($shop_id)
    {
        $msort     = input("param.msort/d");
        $mdesc     = input("param.mdesc/d");
        $order     = array('g.sale_time' => 'desc');
        $orderFile = array('1' => 'g.is_hot', '2' => 'g.sale_num', '3' => 'g.shop_price', '4' => 'g.shop_price', '5' => '(gs.total_score/gs.total_users)', '6' => 'g.sale_time');
        $orderSort = array('0' => 'asc', '1' => 'desc');
        if ($msort > 0) {
            $order = array($orderFile[$msort] => $orderSort[$mdesc]);
        }
        $goods_name = input("param.goods_name"); //搜索店鋪名
        $words     = $where     = $where2     = $where3     = [];
        if ($goods_name != "") {
            $words = explode(" ", $goods_name);
        }
        if (!empty($words)) {
            $sarr = array();
            foreach ($words as $key => $word) {
                if ($word != "") {
                    $sarr[] = "g.goods_name like '%$word%'";
                }
            }
            $where = implode(" or ", $sarr);
        }
        $sprice = input("param.sprice"); //开始价格
        $eprice = input("param.eprice"); //结束价格
        if ($sprice != "") {
            $where2 = "g.shop_price >= " . (float) $sprice;
        }

        if ($eprice != "") {
            $where3 = "g.shop_price <= " . (float) $eprice;
        }

        $ct1 = input("param.ct1/d");
        $ct2 = input("param.ct2/d");
        if ($ct1 > 0) {
            $where['shop_cat_id1'] = $ct1;
        }

        if ($ct2 > 0) {
            $where['shop_cat_id2'] = $ct2;
        }

        $goods = Db::table('__GOODS__')->alias('g')
            ->join('__GOODS_SCORES__ gs', 'gs.goods_id = g.goods_id', 'left')
            ->where(['g.shop_id' => $shop_id, 'g.is_sale' => 1, 'g.goods_status' => 1, 'g.status' => 1])
            ->where($where)->where($where2)->where($where3)
            ->field('g.goods_id,g.goods_name,g.goods_img,g.shop_price,g.market_price,g.sale_num,g.appraise_num,g.goods_stock')
            ->order($order)
            ->paginate(20)->toArray();
        return $goods;
    }

    /**
     *  预警库存列表
     */
    public function stockByPage()
    {
        $where  = [];
        $c1Id   = (int) input('cat1');
        $c2Id   = (int) input('cat2');
        $shop_id = UID;
        if ($c1Id != 0) {
            $where[] = " shop_cat_id1=" . $c1Id;
        }

        if ($c2Id != 0) {
            $where[] = " shop_cat_id2=" . $c2Id;
        }

        $where[] = " g.shop_id = " . $shop_id;
        $prefix  = config('database.prefix');
        $sql1    = 'SELECT g.goods_id,g.goods_name,g.goods_img,gs.spec_stock goods_stock ,gs.warn_stock warn_stock,g.is_spec,gs.product_no,gs.id,gs.spec_ids,g.is_sale
                    FROM ' . $prefix . 'goods g inner JOIN ' . $prefix . 'goods_specs gs ON gs.goods_id=g.goods_id and gs.spec_stock <= gs.warn_stock and gs.warn_stock>0
                    WHERE g.status = 1 and ' . implode(' and ', $where);

        $sql2 = 'SELECT g.goods_id,g.goods_name,g.goods_img,g.goods_stock,g.warn_stock,g.is_spec,g.product_no,0 as id,"" as spec_ids,g.is_sale
                    FROM ' . $prefix . 'goods g
                    WHERE g.status = 1  and is_spec=0 and g.goods_stock<=g.warn_stock
                    and g.warn_stock>0 and ' . implode(' and ', $where);
        $page     = (int) input('post.' . config('paginate.var_page'));
        $page     = ($page <= 0) ? 1 : $page;
        $pageSize = 15;
        $start    = ($page - 1) * $pageSize;
        $sql      = $sql1 . " union " . $sql2;
        $sqlNum   = 'select count(*) fiNum from (' . $sql . ") as c";
        $sql      = 'select * from (' . $sql . ') as c order by is_sale desc limit ' . $start . ',' . $pageSize;
        $rsNum    = Db::query($sqlNum);
        $rsRows   = Db::query($sql);
        $rs       = FIPager((int) $rsNum[0]['fiNum'], $rsRows, $page, $pageSize);
        if (empty($rs['Rows'])) {
            return $rs;
        }

        $spec_ids = [];
        foreach ($rs['Rows'] as $key => $v) {
            $spec_ids[$key] = explode(':', $v['spec_ids']);
            $rss           = Db::table('__SPEC_ITEMS__')->alias('si')
                ->join('__SPEC_CATS__ sc', 'sc.cat_id=si.cat_id', 'left')
                ->where('si.shop_id = ' . $shop_id . ' and si.goods_id = ' . $v['goods_id'])
                ->where('si.item_id', 'in', $spec_ids[$key])
                ->field('si.item_id,si.item_name,sc.cat_id,sc.cat_name')
                ->select();
            $rs['Rows'][$key]['spec'] = $rss;
        }
        return $rs;
    }

    /**
     *  预警修改预警库存
     */
    public function editwarn_stock()
    {
        $id             = input('post.id/d');
        $type           = input('post.type/d');
        $number         = (int) input('post.number');
        $shop_id         = SID;
        $data           = $data2           = [];
        $data['shop_id'] = $data2['shop_id'] = $shop_id;
        $datat          = array('1' => 'spec_stock', '2' => 'warn_stock', '3' => 'goods_stock', '4' => 'warn_stock');
        if (!empty($type)) {
            $data[$datat[$type]] = $number;
            if ($type == 1 || $type == 2) {
                $data['goods_id'] = $goods_id = input('post.goods_id/d');
                $gs              = new GoodsSpecs();
                $rss             = $gs->update($data, ['id' => $id]);
                //更新商品库存
                $goods_stock = 0;
                if ($rss !== false) {
                    $spec_stocks = $gs->where(['shop_id' => $shop_id, 'goods_id' => $goods_id, 'status' => 1])->field('spec_stock')->select();
                    foreach ($spec_stocks as $key => $v) {
                        $goods_stock = $goods_stock + $v['spec_stock'];
                    }
                    $data2['goods_stock'] = $goods_stock;
                    $rs                  = $this->update($data2, ['goods_id' => $goods_id]);
                } else {
                    return FIReturn('操作失败', -1);
                }
            }
            if ($type == 3 || $type == 4) {
                $rs = $this->update($data, ['goods_id' => $id]);
            }
            if ($rs !== false) {
                return FIReturn('操作成功', 1);
            } else {
                return FIReturn('操作失败', -1);
            }
        }
        return FIReturn('操作失败', -1);
    }

}
