<?php
namespace fi\common\model;

use think\Db;

/**
 * 商品分类类
 */
class GoodsCats extends Base
{
    /**
     * 获取列表
     */
    public function listQuery($parent_id, $is_floor = -1)
    {
        $dbo = $this->where(['status' => 1, 'is_show' => 1, 'parent_id' => $parent_id]);
        if ($is_floor != -1) {
            $dbo->where('is_floor', $is_floor);
        }

        return $dbo->order('cat_sort asc')->select();
    }

    /**
     * 根据子分类获取其父级分类
     */
    public function getParentIs($id, $data = array())
    {
        $data[]   = $id;
        $parent_id = $this->where('cat_id', $id)->value('parent_id');
        if ($parent_id == 0) {
            krsort($data);
            return $data;
        } else {
            return $this->getParentIs($parent_id, $data);
        }
    }

    /**
     * 获取首页楼层
     */
    public function getFloors()
    {
        $cats1 = Db::table('__GOODS_CATS__')->where(['status' => 1, 'is_show' => 1, 'parent_id' => 0, 'is_floor' => 1])
            ->field("cat_name,cat_id")->order('cat_sort asc')->limit(10)->select();
        // if (!empty($cats1)) {
            // $ids = [];
            // foreach ($cats1 as $key => $v) {
            //     $ids[] = $v['cat_id'];
            // }
            // $cats2 = [];
            // $rs    = Db::table('__GOODS_CATS__')->where(['status' => 1, 'is_show' => 1, 'parent_id' => ['in', $ids], 'is_floor' => 1])
            //     ->field("parent_id,cat_name,cat_id")->order('cat_sort asc')->select();
            // foreach ($rs as $key => $v) {
            //     $cats2[$v['parent_id']][] = $v;//parent_id就是上一级的cat_id
            // }
            // foreach ($cats1 as $key => $v) {
            //     $cats1[$key]['children'] = (isset($cats2[$v['cat_id']])) ? $cats2[$v['cat_id']] : [];
            // }
        // }
        return $cats1;
    }
}
