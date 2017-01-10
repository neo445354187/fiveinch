<?php
/**
 */
namespace fi\common\taglib;

use think\template\TagLib;

class Fi extends TagLib
{
    /**
     * 定义标签列表
     */
    protected $tags = [
        'friendlink'     => ['attr' => 'num,key,id,cache'],
        'ads'            => ['attr' => 'code,num,key,id,cache'],
        'article'        => ['attr' => 'cat,num,key,id,cache'],
        'goods'          => ['attr' => 'type,cat,num,key,id,cache'],
        'shopgoods'      => ['attr' => 'type,shop,num,key,id,cache'],
        'shopfloorgoods' => ['attr' => 'cat,shop,num,key,id,cache'],
    ];

    /**
     * 商品数据调用
     *  type:推荐/新品/热销/精品/浏览历史/看了又看  - recom/new/hot/best/history/visit
     *   cat:商品分类
     *   num:获取记录数量
     * cache:缓存时间
     *   key:序号
     *    id:循环中定义的元素变量
     * {fi:goods type='hot' cat='1' num='6'}{/fi:goods}
     */
    public function tagGoods($tag, $content)
    {
        $type  = $tag['type'];
        $cat_id = isset($tag['cat']) ? $tag['cat'] : 0;
        $flag  = substr($cat_id, 0, 1);
        if (':' == $flag) {
            $cat_id = $this->autoBuildVar($cat_id);
            $parseStr .= '$_result=' . $cat_id . ';';
            $cat_id = '$_result';
        } else {
            $cat_id = $this->autoBuildVar($cat_id);
        }

        $id    = isset($tag['id']) ? $tag['id'] : 'vo';
        $num   = isset($tag['num']) ? (int) $tag['num'] : 0;
        $cache = isset($tag['cache']) ? $tag['cache'] : 0;
        $key   = isset($tag['key']) ? $tag['key'] : 'key';
        $parse = '<?php ';
        $parse .= '$fiTagGoods =  model("Tags")->listGoods("' . $type . '",' . $cat_id . ',' . $num . ',' . $cache . '); ';
        $parse .= 'foreach($fiTagGoods as $' . $key . '=>$' . $id . '){';
        $parse .= '?>';
        $parse .= $content;
        $parse .= '<?php } ?>';
        return $parse;
    }
    /**
     * 广告数据调用
     *   num:获取记录数量
     * cache:缓存时间
     *   key:序号
     *    id:循环中定义的元素变量
     * {fi:friendlink num='6'}{/fi:ads}
     */
    public function tagFriendlink($tag, $content)
    {
        $id    = isset($tag['id']) ? $tag['id'] : 'vo';
        $num   = isset($tag['num']) ? (int) $tag['num'] : 99;
        $cache = isset($tag['cache']) ? $tag['cache'] : 0;
        $key   = isset($tag['key']) ? $tag['key'] : 'key';
        $parse = '<?php ';
        $parse .= '$fiTagFriendlink =  model("Tags")->listFriendlink(' . $num . ',' . $cache . '); ';
        $parse .= 'foreach($fiTagFriendlink as $' . $key . '=>$' . $id . '){';
        $parse .= '?>';
        $parse .= $content;
        $parse .= '<?php } ?>';
        return $parse;
    }

    /**
     * 广告数据调用
     *  code:广告代码
     *   num:获取记录数量
     * cache:缓存时间
     *   key:序号
     *    id:循环中定义的元素变量
     * {fi:ads code='1' cat='1' num='6'}{/fi:ads}
     */
    public function tagAds($tag, $content)
    {
        $code  = $tag['code'];
        $id    = isset($tag['id']) ? $tag['id'] : 'vo';
        $num   = isset($tag['num']) ? (int) $tag['num'] : 99;
        $cache = isset($tag['cache']) ? $tag['cache'] : 0;
        $key   = isset($tag['key']) ? $tag['key'] : 'key';
        $parse = '<?php ';
        $parse .= '$fiTagAds =  model("Tags")->listAds("' . $code . '",' . $num . ',' . $cache . '); ';
        $parse .= 'foreach($fiTagAds as $' . $key . '=>$' . $id . '){';
        $parse .= '?>';
        $parse .= $content;
        $parse .= '<?php } ?>';
        return $parse;
    }

    /**
     * 文章数据调用
     *   cat:文章分类ID 或者 'new'
     *   num:获取记录数量
     * cache:缓存时间
     *   key:序号
     *    id:循环中定义的元素变量
     * {fi:article cat='1' num='6'}{/fi:article}
     */
    public function tagArticle($tag, $content)
    {
        $cat   = $tag['cat'];
        $id    = isset($tag['id']) ? $tag['id'] : 'vo';
        $num   = isset($tag['num']) ? (int) $tag['num'] : 99;
        $cache = isset($tag['cache']) ? $tag['cache'] : 0;
        $key   = isset($tag['key']) ? $tag['key'] : 'key';
        $parse = '<?php ';
        $parse .= '$fiTagArticle =  model("Tags")->listArticle("' . $cat . '",' . $num . ',' . $cache . '); ';
        $parse .= 'foreach($fiTagArticle as $' . $key . '=>$' . $id . '){';
        $parse .= '?>';
        $parse .= $content;
        $parse .= '<?php } ?>';
        return $parse;
    }

    /**
     * 店铺商品数据调用
     *  type:推荐/新品/热销/精品  - recom/new/hot/best
     *   shop:店铺ID
     *   num:获取记录数量
     * cache:缓存时间
     *   key:序号
     *    id:循环中定义的元素变量
     * {fi:shopgoods name='hot' cat='1' num='6'}{/fi:goods}
     */
    public function tagShopGoods($tag, $content)
    {
        $type   = $tag['type'];
        $shop_id = isset($tag['shop']) ? $tag['shop'] : 0;
        $flag   = substr($shop_id, 0, 1);
        if (':' == $flag) {
            $shop_id = $this->autoBuildVar($shop_id);
            $parseStr .= '$_result=' . $shop_id . ';';
            $shop_id = '$_result';
        } else {
            $shop_id = $this->autoBuildVar($shop_id);
        }

        $id    = isset($tag['id']) ? $tag['id'] : 'vo';
        $num   = isset($tag['num']) ? (int) $tag['num'] : 0;
        $cache = isset($tag['cache']) ? $tag['cache'] : 0;
        $key   = isset($tag['key']) ? $tag['key'] : 'key';
        $parse = '<?php ';
        $parse .= '$fiTagShopGoods =  model("Tags")->listShopGoods("' . $type . '",' . $shop_id . ',' . $num . ',' . $cache . '); ';
        $parse .= 'foreach($fiTagShopGoods as $' . $key . '=>$' . $id . '){';
        $parse .= '?>';
        $parse .= $content;
        $parse .= '<?php } ?>';
        return $parse;

    }

    /**
     * 自营店铺楼层商品数据调用
     *   shop:店铺ID
     *   num:获取记录数量
     * cache:缓存时间
     *   key:序号
     *    id:循环中定义的元素变量
     * {fi:shopfloorgoods cat='1' num='6'}{/fi:shopfloorgoods}
     */
    public function tagShopFloorGoods($tag, $content)
    {
        $cat_id = isset($tag['cat']) ? $tag['cat'] : 0;
        $flag  = substr($cat_id, 0, 1);
        if (':' == $flag) {
            $cat_id = $this->autoBuildVar($cat_id);
            $parseStr .= '$_result=' . $cat_id . ';';
            $cat_id = '$_result';
        } else {
            $cat_id = $this->autoBuildVar($cat_id);
        }

        $shop_id = isset($tag['shop']) ? $tag['shop'] : 0;
        $flag   = substr($shop_id, 0, 1);
        if (':' == $flag) {
            $shop_id = $this->autoBuildVar($shop_id);
            $parseStr .= '$_result=' . $shop_id . ';';
            $shop_id = '$_result';
        } else {
            $shop_id = $this->autoBuildVar($shop_id);
        }

        $id    = isset($tag['id']) ? $tag['id'] : 'vo';
        $num   = isset($tag['num']) ? (int) $tag['num'] : 0;
        $cache = isset($tag['cache']) ? $tag['cache'] : 0;
        $key   = isset($tag['key']) ? $tag['key'] : 'key';
        $parse = '<?php ';
        $parse .= '$fiTagShopFloorGoods =  model("Tags")->listShopFloorGoods(' . $cat_id . ',' . $shop_id . ',' . $num . ',' . $cache . '); ';
        $parse .= 'foreach($fiTagShopFloorGoods as $' . $key . '=>$' . $id . '){';
        $parse .= '?>';
        $parse .= $content;
        $parse .= '<?php } ?>';
        return $parse;

    }
}
