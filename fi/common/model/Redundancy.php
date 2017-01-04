<?php
namespace fi\common\model;

/**
 * 冗余表模型
 */
class Redundancy extends Base
{

    /**
     * [del 删除商品触发的冗余表删除记录]
     * @param  [int|array] $goodsId [商品goodsId， 可以执行批量操作]
     * @return [type]     [description]
     */
    public function del($goodsId)
    {
        # code...
    }

    /**
     * [add 添加记录]
     * @param [int|array] $goodsId [description]
     */
    public function add($goodsId)
    {
        # code...
    }

    public function edit($goodsId)
    {
        $this->del($goodsId);
        $this->add($goodsId);
    }
}
