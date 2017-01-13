<?php
namespace fi\home\controller;

use fi\home\model\Goods as M;
use fi\home\model\SearchGoods;

/**
 * 商品控制器
 */
class Goods extends Base
{
    /**
     * 批量删除商品
     */
    public function batchDel()
    {
        $m = new M();
        return $m->batchDel();
    }
    /**
     * 修改商品库存/价格
     */
    public function editGoodsBase()
    {
        $m = new M();
        return $m->editGoodsBase();
    }

    /**
     * 修改商品状态
     */
    public function changSaleStatus()
    {
        $m = new M();
        return $m->changSaleStatus();
    }
    /**
     * 批量修改商品状态 新品/精品/热销/推荐
     */
    public function changeGoodsStatus()
    {
        $m = new M();
        return $m->changeGoodsStatus();
    }
    /**
     *   批量上(下)架
     *   说明：一个方法只能做一件事，这里既包含了上架，又包含下架，不太好，不利于权限管理
     */
    public function changeSale()
    {
        $m = new M();
        return $m->changeSale();
    }
    /**
     *  上架商品列表
     */
    public function sale()
    {
        return $this->fetch('default/shops/goods/list_sale');
    }
    /**
     * 获取上架商品列表
     */
    public function saleByPage()
    {
        $m            = new M();
        $rs           = $m->saleByPage();
        $rs['status'] = 1;
        return $rs;
    }
    /**
     * 仓库中商品
     */
    public function store()
    {
        return $this->fetch('default/shops/goods/list_store');
    }
    /**
     * 审核中的商品
     */
    public function audit()
    {
        return $this->fetch('default/shops/goods/list_audit');
    }
    /**
     * 获取审核中的商品
     */
    public function auditByPage()
    {
        $m            = new M();
        $rs           = $m->auditByPage();
        $rs['status'] = 1;
        return $rs;
    }
    /**
     * 获取仓库中的商品
     */
    public function storeByPage()
    {
        $m            = new M();
        $rs           = $m->storeByPage();
        $rs['status'] = 1;
        return $rs;
    }
    /**
     * 违规商品
     */
    public function illegal()
    {
        return $this->fetch('default/shops/goods/list_illegal');
    }
    /**
     * 获取违规的商品
     */
    public function illegalByPage()
    {
        $m            = new M();
        $rs           = $m->illegalByPage();
        $rs['status'] = 1;
        return $rs;
    }

    /**
     * 跳去新增页面
     */
    public function add()
    {
        $m = new M();
        //获取goods表格字段的默认值，键为字段名
        $object              = $m->getEModel('goods');
        $object['goods_sn']   = FIGoodsNo();
        $object['product_no'] = FIGoodsNo();
        $object['goods_img']  = FIConf('CONF.goodsLogo');
        $data                = ['object' => $object, 'src' => 'add'];
        return $this->fetch('default/shops/goods/edit', $data);
    }

    /**
     * 新增商品
     */
    public function toAdd()
    {
        $m = new M();
        return $m->add();
    }

    /**
     * 跳去编辑页面
     */
    public function edit()
    {
        $m      = new M();
        $object = $m->getById(input('get.id'));
        if ($object['goods_img'] == '') {
            $object['goods_img'] = FIConf('CONF.goodsLogo');
        }

        $data = ['object' => $object, 'src' => input('src')];
        return $this->fetch('default/shops/goods/edit', $data);
    }

    /**
     * 编辑商品
     */
    public function toEdit()
    {
        $m = new M();
        return $m->edit();
    }
    /**
     * 删除商品
     */
    public function del()
    {
        $m = new M();
        return $m->del();
    }
    /**
     * 获取商品规格属性
     */
    public function getSpecAttrs()
    {
        $m = new M();
        return $m->getSpecAttrs();
    }
    /**
     * 进行商品搜索
     */
    public function search()
    {
        
        //获取商品记录
        $condition = [
            'keyword'   => input('keyword/s'),
            'orderBy'   => input('orderBy/s', 'default'),
            'upOrDown'  => input('upOrDown/s'),
            'brand_name' => input('brand_name/s'),
            'p'         => input('p', 1, 'int'),
        ];
        // $condition = [
        //     'orderBy'   => input('orderBy/s', 'default'),
        //     'upOrDown'  => 'up',
        //     'keyword'   => '',
        //     'brand_name' => '',
        //     'p'         => input('p', 1, 'int'),
        // ];
        $result = (new SearchGoods())->findAll($condition) ?: array();
        if ($result) {
            $result['page'] = (new \page\Page($result['numFound'], '2', $condition))->show();
        }

        // var_dump($result['facetFields']);die;
        // die($result['page']);
        $result = array_merge($result, $condition);
        return $this->fetch("default/goods_search", $result);
    }

    /**
     * 获取商品列表
     */
    public function lists()
    {
        $cat_id       = Input('cat/d');
        $goods_cat_ids = model('GoodsCats')->getParentIs($cat_id);
        reset($goods_cat_ids);
        //填充参数
        $data            = [];
        $data['cat_id']   = $cat_id;
        $data['isStock'] = Input('isStock/d');
        $data['is_new']   = Input('is_new/d');
        $data['orderBy'] = Input('orderBy/d');
        $data['order']   = Input('order/d', 1);
        $data['sprice']  = Input('sprice');
        $data['eprice']  = Input('eprice');
        $data['attrs']   = [];

        $data['area_id'] = (int) Input('area_id');
        $aModel         = model('home/areas');

        // 获取地区
        $data['area1'] = $aModel->listQuery(); // 省级
        // 默认地区信息
        $data['area2'] = $aModel->listQuery(440000); // 广东的下级
        $data['area3'] = $aModel->listQuery(440100); // 广州的下级

        // 如果有筛选地区 获取上级地区信息
        if ($data['area_id'] !== 0) {
            $area_ids = $aModel->getParentIs($data['area_id']);
            /*
            2 => int 440000
            1 => int 440100
            0 => int 440106
             */
            $selectArea = [];
            $area_name   = '';
            foreach ($area_ids as $k => $v) {
                $a = $aModel->getById($v);
                $area_name .= $a['area_name'];
                $selectArea[] = $a;
            }
            // 地区完整名称
            $selectArea['area_name'] = $area_name;
            // 当前选择的地区
            $data['areaInfo'] = $selectArea;

            $data['area2'] = $aModel->listQuery($area_ids[2]); // 广东的下级

            $data['area3'] = $aModel->listQuery($area_ids[1]); // 广州的下级
        }

        $vs = input('vs');
        $vs = ($vs != '') ? explode(',', $vs) : [];
        foreach ($vs as $key => $v) {
            if ($v == '' || $v == 0) {
                continue;
            }

            $v                        = (int) $v;
            $data['attrs']['v_' . $v] = input('v_' . $v);
        }
        $data['vs']          = $vs;
        $data['brandFilter'] = model('Brands')->listQuery((int) current($goods_cat_ids));
        $data['brand_id']     = Input('brand/d');
        $data['price']       = Input('price');
        //封装当前选中的值
        $selector = [];
        //处理品牌
        if ($data['brand_id'] > 0) {
            foreach ($data['brandFilter'] as $key => $v) {
                if ($v['brand_id'] == $data['brand_id']) {
                    $selector[] = ['id' => $v['brand_id'], 'type' => 'brand', 'label' => "品牌", "val" => $v['brand_name']];
                }

            }
            unset($data['brandFilter']);
        }
        //处理价格
        if ($data['sprice'] != '' && $data['eprice'] != '') {
            $selector[] = ['id' => 0, 'type' => 'price', 'label' => "价格", "val" => $data['sprice'] . "-" . $data['eprice']];
        }
        if ($data['sprice'] != '' && $data['eprice'] == '') {
            $selector[] = ['id' => 0, 'type' => 'price', 'label' => "价格", "val" => $data['sprice'] . "以上"];
        }
        if ($data['sprice'] == '' && $data['eprice'] != '') {
            $selector[] = ['id' => 0, 'type' => 'price', 'label' => "价格", "val" => "0-" . $data['eprice']];
        }
        //处理已选属性
        $goodsFilter  = model('Attributes')->listQueryByFilter($cat_id);
        $ngoodsFilter = [];
        foreach ($goodsFilter as $key => $v) {
            if (!in_array($v['attr_id'], $vs)) {
                $ngoodsFilter[] = $v;
            }

        }
        if (count($vs) > 0) {
            foreach ($goodsFilter as $key => $v) {
                if (in_array($v['attr_id'], $vs)) {
                    foreach ($v['attr_val'] as $key2 => $vv) {
                        if ($vv == input('v_' . $v['attr_id'])) {
                            $selector[] = ['id' => $v['attr_id'], 'type' => 'v_' . $v['attr_id'], 'label' => $v['attr_name'], "val" => $vv];
                        }

                    }
                }
            }
        }
        $data['selector']    = $selector;
        $data['goodsFilter'] = $ngoodsFilter;
        //获取商品记录
        $m                  = new M();
        $data['priceGrade'] = $m->getPriceGrade($goods_cat_ids);
        $data['goodsPage']  = $m->pageQuery($goods_cat_ids);
        return $this->fetch("default/goods_list", $data);
    }

    /**
     * 查看商品详情
     */
    public function detail()
    {
        $m     = new M();
        $goods = $m->getBySale(input('id/d', 0));
        if (!empty($goods)) {
            //暂时屏蔽掉历史查看商品记录
            // $history = cookie("history_goods");
            // $history = is_array($history) ? $history : [];
            // array_unshift($history, (string) $goods['goods_id']);
            // $history = array_values(array_unique($history));

            // if (!empty($history)) {
            //     cookie("history_goods", $history, 25920000);
            // }
            // var_dump($goods);die;//debug
            $this->assign('goods', $goods);
            $this->assign('shop', $goods['shop']);
            return $this->fetch("default/goods_detail");
        } else {
            return $this->fetch("default/error_lost");
        }
    }
    /**
     * 预警库存
     */
    public function stockwarnbypage()
    {
        return $this->fetch("default/shops/stockwarn/list");
    }
    /**
     * 获取预警库存列表
     */
    public function stockByPage()
    {
        $m            = new M();
        $rs           = $m->stockByPage();
        $rs['status'] = 1;
        return $rs;
    }
    /**
     * 修改预警库存
     */
    public function editwarn_stock()
    {
        $m = new M();
        return $m->editwarn_stock();
    }

    /**
     * 获取商品浏览记录
     */
    public function historyByGoods()
    {
        $rs = model('Tags')->historyByGoods(8);
        return FIReturn('', 1, $rs);
    }
}
