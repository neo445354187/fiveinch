<?php
use think\Db;
use think\Session;
/**
 */
/**
 * 查询网站帮助
 * @param $pnum 父级记录数
 * @param $cnum 子记录数
 */
function FIHelps($pnum = 5, $cnum = 5)
{
    $cats = Db::table('__ARTICLE_CATS__')->where(['cat_type' => 1, 'status' => 1, 'is_show' => 1])
        ->field("cat_name,cat_id")->order('cat_sort asc')->limit($pnum)->select();
    if (!empty($cats)) {
        foreach ($cats as $key => $v) {
            $cats[$key]['articlecats'] = Db::table('__ARTICLES__')->where(['status' => 1, 'is_show' => 1, 'cat_id' => $v['cat_id']])
                ->field("article_id, cat_id, article_title")->order('create_time asc')->limit($cnum)->select();
        }
    }
    return $cats;
}

/**
 * 获取前台菜单
 */
function FIHomeMenus($menu_type)
{
    $m1             = model('HomeMenus')->getMenus();
    $menu_id1        = (int) input("get.id");
    $menus          = [];
    $menus['menus'] = $m1[$menu_type];
    //判断menu_id1是否有效
    if ($menu_id1 == 0) {
        $menu_id1 = (int) session('FI_MENID' . $menu_type);
    } else {
        session('FI_MENID' . $menu_type, $menu_id1);
    }
    //检测第一级菜单是否有效
    $tmpMenuId1 = 0;
    $isFind     = false;
    foreach ($menus['menus'] as $key => $v) {
        if ($tmpMenuId1 == 0) {
            $tmpMenuId1 = $key;
        }

        if ($key == $menu_id1) {
            $isFind = true;
        }

    }
    if ($isFind) {
        $menus['menu_id1'] = $menu_id1;
    } else {
        $menus['menu_id1'] = $tmpMenuId1;
    }
    $menus['menu_id3'] = session('FI_MENUID3' . $menu_type);
    return $menus;
}
/**
 * 获取指定父级的商家店铺分类
 */
function FIShopCats($parent_id)
{
    $shop_id = (int) session('FI_USER.shop_id');
    $dbo    = Db::table('__SHOP_CATS__')->where(['status' => 1, 'is_show' => 1, 'parent_id' => $parent_id, 'shop_id' => $shop_id]);
    return $dbo->field("cat_name,cat_id")->order('cat_sort asc')->select();
}
/**
 * 获取商城搜索关键字
 */
function FISearchKeys()
{
    $keys = FIConf("CONF.hotWordsSearch");
    if ($keys != '') {
        $keys = explode(',', $keys);
    }

    return $keys;
}
/**
 * 获取首页左侧分类、推荐品牌和广告
 */
function FISideCategorys()
{
    $data = cache('FI_SIDE_CATS');
    if (!$data) {
        $cats1 = Db::table('__GOODS_CATS__')->where(['status' => 1, 'is_show' => 1, 'parent_id' => 0])->field("cat_name,cat_id")->order('cat_sort asc')->select();
        if (count($cats1) > 0) {
            $ids1   = [];
            $ids2   = [];
            $cats2  = [];
            $cats3  = [];
            $mcats3 = [];
            $mcats2 = [];
            foreach ($cats1 as $key => $v) {
                $ids1[] = $v['cat_id'];
            }
            $tmp2 = Db::table('__GOODS_CATS__')->where(['status' => 1, 'is_show' => 1, 'parent_id' => ['in', $ids1]])->field("cat_name,cat_id,parent_id")->order('cat_sort asc')->select();
            if (count($tmp2) > 0) {
                foreach ($tmp2 as $key => $v) {
                    $ids2[] = $v['cat_id'];
                }
                $tmp3 = Db::table('__GOODS_CATS__')->where(['status' => 1, 'is_show' => 1, 'parent_id' => ['in', $ids2]])->field("cat_name,cat_id,parent_id")->order('cat_sort asc')->select();
                if (count($tmp3) > 0) {
                    //组装第三级
                    foreach ($tmp3 as $key => $v) {
                        $mcats3[$v['parent_id']][] = $v;
                    }
                }
                //组装第二级
                foreach ($tmp2 as $key => $v) {
                    if (isset($mcats3[$v['cat_id']])) {
                        $v['list'] = $mcats3[$v['cat_id']];
                    }

                    $mcats2[$v['parent_id']][] = $v;
                }
            }
            //组装第一级
            foreach ($cats1 as $key => $v) {
                //获取一级分类下的品牌
                $cats1[$key]['brand'] = Db::table('__GOODS_CATS__')->alias('gc')
                    ->join('__CAT_BRANDS__ gcb', 'gc.cat_id=gcb.cat_id', 'inner')
                    ->join('__BRANDS__ b', 'gcb.brand_id=b.brand_id')
                    ->field('b.brand_id,b.brand_img,b.brand_name')
                    ->where('gc.cat_id', $v['cat_id'])
                    ->limit(16)
                    ->select();
                if (isset($mcats2[$v['cat_id']])) {
                    $cats1[$key]['list'] = $mcats2[$v['cat_id']];
                }

            }
            unset($ids1, $ids2, $cats2, $cats3, $mcats3, $mcats2);
        }
        cache('FI_SIDE_CATS', $cats1);
        return $cats1;
    }
    return $data;
}

/**
 * 获取导航菜单，ps:人为的去掉了index.php
 */
function FINavigations($nav_type)
{
    $data = cache('FI_NAVS');
    if (!$data) {
        $rs = Db::table('__NAVS__')->where(['is_show' => 1, 'nav_type' => $nav_type])->order('nav_sort asc')->select();
        foreach ($rs as $key => $v) {
            if (stripos($v['nav_url'], 'https://') === false && stripos($v['nav_url'], 'http://') === false) {
                $rs[$key]['nav_url'] = str_replace('/index.php', '', \think\Request::instance()->root()) . "/" . $v['nav_url'];
            }
        }
        cache('FI_NAVS', $data);
        return $rs;
    }
    return $data;
}
/**
 * 根据指定的商品分类获取其路径
 */
function FIPathGoodsCat($cat_id, $data = array())
{
    $rs     = Db::table('__GOODS_CATS__')->where(['is_show' => 1, 'status' => 1, 'cat_id' => $cat_id])->field("parent_id,cat_name,cat_id")->find();
    $data[] = $rs;
    if ($rs['parent_id'] == 0) {
        ;
        krsort($data);
        return $data;
    } else {
        return FIPathGoodsCat($rs['parent_id'], $data);
    }
}

/**
 * [solr_escape solr过滤特殊字符]
 *
 * @param [type]  $value [description]
 * @return [type]        [description]
 */
function solr_escape( $value ) {
    //list taken from http://lucene.apache.org/java/docs/queryparsersyntax.html#Escaping%20Special%20Characters
    $pattern = '/(\+|-|&|\||!|\(|\)|\{|}|\[|]|\^|"|~|\*|\?|:|;|~|\/)/';
    $replace = '\\\$1';

    return preg_replace( $pattern, $replace, $value );
}