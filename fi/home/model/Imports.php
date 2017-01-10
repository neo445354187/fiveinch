<?php

namespace fi\home\model;

use think\Db;
use think\Loader;

class Imports {

    /**
     * 上传商品数据
     */
    public function importGoods($data) {
        Loader::import('phpexcel.phpexcel.PHPExcel.IOFactory');
        $objReader = \PHPExcel_IOFactory::load(FIRootPath() . json_decode($data)->route . json_decode($data)->name);
        $objReader->setActiveSheetIndex(0);
        $sheet = $objReader->getActiveSheet();
        $rows = $sheet->getHighestRow();
        $cells = $sheet->getHighestColumn();
        //数据集合
        $readData = [];
        $shop_id = (int) session('FI_USER.shop_id');
        $importNum = 0;
        $goodsCatMap = []; //记录最后一级商品分类
        $goods_cat_pathMap = []; //记录商品分类路径
        $shopCatMap = []; //记录店铺分类
        $goodsCat1Map = []; //记录最后一级商品分类对应的一级分类
        $tmpGoodsCatId = 0;
        $goodsCatBrandMap = []; //商品分类和品牌的对应关系
        //生成订单
        Db::startTrans();
        try {
            //循环读取每个单元格的数据
            for ($row = 3; $row <= $rows; $row++) {//行数是以第3行开始
                $tmpGoodsCatId = 0;
                $goods = [];
                $goods['shop_id'] = $shop_id;
                $goods['goods_name'] = trim($sheet->getCell("A" . $row)->getValue());
                if ($goods['goods_name'] == '')
                    break; //如果某一行第一列为空则停止导入
                $goods['goods_sn'] = trim($sheet->getCell("B" . $row)->getValue());
                $goods['product_no'] = trim($sheet->getCell("C" . $row)->getValue());
                $goods['market_price'] = trim($sheet->getCell("D" . $row)->getValue());
                $goods['shop_price'] = trim($sheet->getCell("E" . $row)->getValue());
                $goods['goods_stock'] = trim($sheet->getCell("F" . $row)->getValue());
                $goods['warn_stock'] = trim($sheet->getCell("G" . $row)->getValue());
                $goods['goods_unit'] = trim($sheet->getCell("H" . $row)->getValue());
                $goods['goods_seo_keywords'] = trim($sheet->getCell("I" . $row)->getValue());
                $goods['goods_tips'] = trim($sheet->getCell("J" . $row)->getValue());
                $goods['is_recom'] = (trim($sheet->getCell("K" . $row)->getValue()) != '') ? 1 : 0;
                $goods['is_best'] = (trim($sheet->getCell("L" . $row)->getValue()) != '') ? 1 : 0;
                $goods['is_new'] = (trim($sheet->getCell("M" . $row)->getValue()) != '') ? 1 : 0;
                $goods['is_hot'] = (trim($sheet->getCell("N" . $row)->getValue()) != '') ? 1 : 0;
                //查询商城分类
                $goodsCat = trim($sheet->getCell("O" . $row)->getValue());
                if (!empty($goodsCat)) {
                    //先判断集合是否存在，不存在的时候才查数据库
                    if (isset($goodsCatMap[$goodsCat])) {
                        $goods['goods_cat_id'] = $goodsCatMap[$goodsCat];
                        $goods['goods_cat_id_path'] = $goods_cat_pathMap[$goodsCat];
                        $tmpGoodsCatId = $goodsCat1Map[$goodsCat];
                    } else {
                        $goods_cat_id = Db::table('__GOODS_CATS__')->where(['cat_name' => $goodsCat, 'status' => 1])->field('cat_id')->find();
                        if (!empty($goods_cat_id['cat_id'])) {
                            $goodsCats = model('GoodsCats')->getParentIs($goods_cat_id['cat_id']);
                            $goods['goods_cat_id'] = $goods_cat_id['cat_id'];
                            $goods['goods_cat_id_path'] = implode('_', $goodsCats) . "_";
                            //放入集合
                            $goodsCatMap[$goodsCat] = $goods_cat_id['cat_id'];
                            $goods_cat_pathMap[$goodsCat] = implode('_', $goodsCats) . "_";
                            $goodsCat1Map[$goodsCat] = $goodsCats[0];
                            $tmpGoodsCatId = $goodsCats[0];
                        }
                    }
                }
                //查询商城分类
                $shopGoodsCat = trim($sheet->getCell("P" . $row)->getValue());
                if (!empty($shopGoodsCat)) {
                    //先判断集合是否存在，不存在的时候才查数据库
                    if (isset($shopCatMap[$shopGoodsCat])) {
                        $goods['shop_cat_id1'] = $shopCatMap[$shopGoodsCat]['s1'];
                        $goods['shop_cat_id2'] = $shopCatMap[$shopGoodsCat]['s2'];
                    } else {
                        $shopCat = Db::table("__SHOP_CATS__")->alias('sc1')
                                ->join('__SHOP_CATS__ sc2', 'sc2.parent_id=sc1.cat_id', 'left')
                                ->field('sc1.cat_id cat_id1,sc2.cat_id cat_id2,sc2.cat_name')
                                ->where(['sc1.shop_id' => $shop_id, 'sc1.status' => 1, 'sc2.cat_name' => $shopGoodsCat])
                                ->find();
                        if (!empty($shopCat)) {
                            $goods['shop_cat_id1'] = $shopCat['cat_id1'];
                            $goods['shop_cat_id2'] = $shopCat['cat_id2'];
                            //放入集合
                            $shopCatMap[$shopGoodsCat] = [];
                            $shopCatMap[$shopGoodsCat]['s1'] = $goods['shop_cat_id1'];
                            $shopCatMap[$shopGoodsCat]['s2'] = $goods['shop_cat_id2'];
                        }
                    }
                }
                //查询品牌
                $brand = trim($sheet->getCell("Q" . $row)->getValue());
                if (!empty($brand)) {
                    if (isset($goodsCatBrandMap[$brand])) {
                        $goods['brand_id'] = $goodsCatBrandMap[$brand];
                    } else {
                        $brands = Db::table('__BRANDS__')->alias('a')->join('__CAT_BRANDS__ cb', 'a.brand_id=cb.brand_id', 'inner')
                                        ->where(['cat_id' => $tmpGoodsCatId, 'brand_name' => $brand, 'status' => 1])->field('a.brand_id')->find();
                        if (!empty($brands)) {
                            $goods['brand_id'] = $brands['brand_id'];
                            $goodsCatBrandMap[$brand] = $brands['brand_id'];
                        }
                    }
                }
                $goods['goods_desc'] = trim($sheet->getCell("R" . $row)->getValue());
                $goods['is_sale'] = 0;
                $goods['goods_status'] = (FIConf("CONF.isGoodsVerify") == 1) ? 0 : 1;
                $goods['status'] = 1;
                $goods['sale_time'] = date('Y-m-d H:i:s');
                $goods['create_time'] = date('Y-m-d H:i:s');
                $readData[] = $goods;
                $importNum++;
            }
            if (count($readData) > 0) {
                $list = model('Goods')->saveAll($readData);
                //建立商品评分记录
                $goods_scores = [];
                foreach ($list as $key => $v) {
                    $gs = [];
                    $gs['goods_id'] = $v['goods_id'];
                    $gs['shop_id'] = $shop_id;
                    $goods_scores[] = $gs;
                }
                if (count($goods_scores) > 0)
                    Db::name('goods_scores')->insertAll($goods_scores);
            }
            Db::commit();
            return json_encode(['status' => 1, 'importNum' => $importNum]);
        } catch (\Exception $e) {
            Db::rollback();
            return json_encode(FIReturn('导入商品失败', -1));
        }
    }

}
