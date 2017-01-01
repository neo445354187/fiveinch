<?php
namespace fi\home\controller;

use fi\home\model\Goods as M;

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
        $m                   = new M();
        $object              = $m->getEModel('goods');
        $object['goodsSn']   = FIGoodsNo();
        $object['productNo'] = FIGoodsNo();
        $object['goodsImg']  = FIConf('CONF.goodsLogo');
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
        if ($object['goodsImg'] == '') {
            $object['goodsImg'] = FIConf('CONF.goodsLogo');
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
        $m               = new M();
        // $data['areaId'] = (int) Input('areaId');
        $data = [
            'isStock' => Input('isStock/d'),
            'isNew' => Input('isNew/d'),
            'orderBy' => Input('orderBy/d'),
            'order' => Input('order/d', 1),
            'keyword' => Input('keyword'),
            'sprice' => Input('sprice/d'),
            'eprice' => Input('eprice/d'),
            'areaId' => (int)Input('areaId')
        ];

        $data         = array_merge(
            $data,
            model('Areas')->getAddr($data['areaId'])
        );
        $data['goodsPage'] = $m->pageQuery();
        return $this->fetch("default/goods_search", $data);
    }

    /**
     * 获取商品列表
     */
    public function lists()
    {
        $catId       = Input('cat/d');
        $goodsCatIds = model('GoodsCats')->getParentIs($catId);
        reset($goodsCatIds);
        //填充参数
        $data            = [];
        $data['catId']   = $catId;
        $data['isStock'] = Input('isStock/d');
        $data['isNew']   = Input('isNew/d');
        $data['orderBy'] = Input('orderBy/d');
        $data['order']   = Input('order/d', 1);
        $data['sprice']  = Input('sprice');
        $data['eprice']  = Input('eprice');
        $data['attrs']   = [];

        $data['areaId'] = (int) Input('areaId');
        $aModel         = model('home/areas');

        // 获取地区
        $data['area1'] = $aModel->listQuery(); // 省级
        // 默认地区信息
        $data['area2'] = $aModel->listQuery(440000); // 广东的下级
        $data['area3'] = $aModel->listQuery(440100); // 广州的下级

        // 如果有筛选地区 获取上级地区信息
        if ($data['areaId'] !== 0) {
            $areaIds = $aModel->getParentIs($data['areaId']);
            /*
            2 => int 440000
            1 => int 440100
            0 => int 440106
             */
            $selectArea = [];
            $areaName   = '';
            foreach ($areaIds as $k => $v) {
                $a = $aModel->getById($v);
                $areaName .= $a['areaName'];
                $selectArea[] = $a;
            }
            // 地区完整名称
            $selectArea['areaName'] = $areaName;
            // 当前选择的地区
            $data['areaInfo'] = $selectArea;

            $data['area2'] = $aModel->listQuery($areaIds[2]); // 广东的下级

            $data['area3'] = $aModel->listQuery($areaIds[1]); // 广州的下级
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
        $data['brandFilter'] = model('Brands')->listQuery((int) current($goodsCatIds));
        $data['brandId']     = Input('brand/d');
        $data['price']       = Input('price');
        //封装当前选中的值
        $selector = [];
        //处理品牌
        if ($data['brandId'] > 0) {
            foreach ($data['brandFilter'] as $key => $v) {
                if ($v['brandId'] == $data['brandId']) {
                    $selector[] = ['id' => $v['brandId'], 'type' => 'brand', 'label' => "品牌", "val" => $v['brandName']];
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
        $goodsFilter  = model('Attributes')->listQueryByFilter($catId);
        $ngoodsFilter = [];
        foreach ($goodsFilter as $key => $v) {
            if (!in_array($v['attrId'], $vs)) {
                $ngoodsFilter[] = $v;
            }

        }
        if (count($vs) > 0) {
            foreach ($goodsFilter as $key => $v) {
                if (in_array($v['attrId'], $vs)) {
                    foreach ($v['attrVal'] as $key2 => $vv) {
                        if ($vv == input('v_' . $v['attrId'])) {
                            $selector[] = ['id' => $v['attrId'], 'type' => 'v_' . $v['attrId'], 'label' => $v['attrName'], "val" => $vv];
                        }

                    }
                }
            }
        }
        $data['selector']    = $selector;
        $data['goodsFilter'] = $ngoodsFilter;
        //获取商品记录
        $m                  = new M();
        $data['priceGrade'] = $m->getPriceGrade($goodsCatIds);
        $data['goodsPage']  = $m->pageQuery($goodsCatIds);
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
            $history = cookie("history_goods");
            $history = is_array($history) ? $history : [];
            array_unshift($history, (string) $goods['goodsId']);
            $history = array_values(array_unique($history));

            if (!empty($history)) {
                cookie("history_goods", $history, 25920000);
            }
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
    public function editwarnStock()
    {
        $m = new M();
        return $m->editwarnStock();
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
