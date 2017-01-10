<?php
namespace fi\home\controller;

/**
 * 门店配置控制器
 */
class Shopconfigs extends Base
{
    /**
     * 店铺设置
     */
    public function toShopCfg()
    {
        //获取商品信息
        $m = model('ShopConfigs');
        $this->assign('object', $m->getShopCfg((int) session('FI_USER.shop_id')));
        return $this->fetch('default/shops/shopconfigs/shop_cfg');
    }

    /**
     * 新增/修改 店铺设置
     */
    public function editShopCfg()
    {
        $shop_id = (int) session('FI_USER.shop_id');
        $m      = model('ShopConfigs');
        if ($shop_id > 0) {
            $rs = $m->editShopCfg($shop_id);
        }
        return $rs;
    }

}
