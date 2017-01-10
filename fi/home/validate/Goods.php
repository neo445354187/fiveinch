<?php

namespace fi\home\validate;

use think\Validate;

/**
 * 商品验证器
 */
class Goods extends Validate {

    protected $rule = [
        ['goods_name', 'require|max:300', '请输入商品编号|商品名称不能超过100个字符'],
        ['goods_img', 'require', '请上传商品图片'],
        ['goods_sn', 'checkGoodsSn:1', '请输入商品编号'],
        ['product_no', 'checkProductNo:1', '请输入商品货号'],
        ['market_price', 'require|float', '请输入市场价格|市场价格只能为数字'],
        ['shop_price', 'require|float', '请输入店铺价格|店铺价格只能为数字'],
        ['goods_unit', 'require', '请输入商品单位'],
        ['is_sale', 'in:,0,1', '无效的上架状态'],
        ['is_recom', 'in:,0,1', '无效的推荐状态'],
        ['is_best', 'in:,0,1', '无效的精品状态'],
        ['is_new', 'in:,0,1', '无效的新品状态'],
        ['is_hot', 'in:,0,1', '无效的热销状态'],
        ['goods_cat_id', 'require', '请选择完整商品分类'],
        ['goods_desc', 'require', '请输入商品描述'],
        ['specsIds', 'checkSpecsIds:1', '请填写完整商品规格信息']
    ];

    /**
     * 检测商品编号
     */
    protected function checkGoodsSn($value) {
        $goods_id = Input('post.goods_id/d', 0);
        $key = Input('post.goods_sn');
        if ($key == '')
            return '请输入商品编号';
        $isChk = model('Goods')->checkExistGoodsKey('goods_sn', $key, $goods_id);
        if ($isChk)
            return '对不起，该商品编号已存在';
        return true;
    }

    /**
     * 检测商品货号
     */
    protected function checkProductNo($value) {
        $goods_id = Input('post.goods_id/d', 0);
        $key = Input('post.product_no');
        if ($key == '')
            return '请输入商品货号';
        $isChk = model('Goods')->checkExistGoodsKey('product_no', $key, $goods_id);
        if ($isChk)
            return '对不起，该商品货号已存在';
        return true;
    }

    /**
     * 检测商品规格是否填写完整
     */
    public function checkSpecsIds() {
        $specsIds = input('post.specsIds');
        if ($specsIds != '') {
            $str = explode(',', $specsIds);
            $specsIds = [];
            foreach ($str as $v) {
                $vs = explode('-', $v);
                foreach ($vs as $vv) {
                    if (!in_array($vv, $specsIds))
                        $specsIds[] = $vv;
                }
            }
            //检测规格名称是否填写完整
            foreach ($specsIds as $v) {
                if (input('post.specName_' . $v) == '')
                    return '请填写完整商品规格值sn' . 'specName_' . $v;
            }
            //检测销售规格是否完整	
            foreach ($str as $v) {
                if (input('post.product_no_' . $v) == '')
                    return '请填写完整商品销售规格信息1';
                if (input('post.market_price_' . $v) == '')
                    return '请填写完整商品销售规格信息2';
                if (input('post.spec_price_' . $v) == '')
                    return '请填写完整商品销售规格信息3';
                if (input('post.spec_stock_' . $v) == '')
                    return '请填写完整商品销售规格信息4';
                if (input('post.warn_stock_' . $v) == '')
                    return '请填写完整商品销售规格信息5';
            }
            if (input('post.defaultSpec') == '')
                return '请选择推荐规格';
        }
        return true;
    }

}
