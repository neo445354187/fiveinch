<?php
namespace fi\common\model;

/**
 * 某些较杂业务处理类
 */
use think\db;

class Systems extends Base
{
    /**
     * 获取定时任务
     */
    public function getSysMessages()
    {
        $tasks  = strtolower(input('post.tasks'));
        $tasks  = explode(',', $tasks);
        $userId = (int) session('FI_USER.userId');
        $shopId = (int) session('FI_USER.shopId');
        $data   = [];
        if (in_array('message', $tasks)) {
            //获取用户未读消息
            $data['message']['num'] = Db::name('messages')->where(['receiveUserId' => $userId, 'msgStatus' => 0])->count();
            $data['message']['id']  = 49;
        }
        //获取商家待处理订单
        if (in_array('shoporder', $tasks)) {
            $data['shoporder']['24'] = Db::name('orders')->where(['shopId' => $shopId, 'orderStatus' => 0, 'dataFlag' => 1])->count();
            $data['shoporder']['45'] = Db::name('orders')->where(['shopId' => $shopId, 'orderStatus' => -3, 'dataFlag' => 1])->count();
            $data['shoporder']['25'] = Db::name('order_complains')->where(['respondTargetId' => $shopId, 'complainStatus' => 1])->count();
            $data['shoporder']['55'] = Db::name('orders')->where(['shopId' => $shopId, 'orderStatus' => -2, 'dataFlag' => 1])->count();
            //获取库存预警数量
            $goodsn                  = Db::table('__GOODS__')->where('shopId =' . $shopId . ' and dataFlag = 1 and goodsStock <= warnStock and isSpec = 0 and warnStock>0')->count();
            $specsn                  = Db::table('__GOODS_SPECS__')->where('shopId =' . $shopId . ' and dataFlag = 1 and specStock <= warnStock and warnStock>0')->count();
            $data['shoporder']['54'] = $goodsn + $specsn;
        }
        //获取用户订单状态
        if (in_array('userorder', $tasks)) {
            $data['userorder']['3'] = Db::name('orders')->where(['userId' => $userId, 'orderStatus' => -2, 'dataFlag' => 1])->count();
            $data['userorder']['5'] = Db::name('orders')->where(['userId' => $userId, 'orderStatus' => 1, 'dataFlag' => 1])->count();
            $data['userorder']['6'] = Db::name('orders')->where(['userId' => $userId, 'orderStatus' => 2, 'isAppraise' => 0, 'dataFlag' => 1])->count();
        }
        //获取用户购物车数量
        if (in_array('cart', $tasks)) {
            $cartNum      = 0;
            $rs           = Db::name('carts')->field('cartNum')->where(['userId' => $userId])->select();
            foreach ($rs as $key => $v) {
                $cartNum = $cartNum + $v['cartNum'];
            }
            $data['cart']['goods'] = count($rs);
            $data['cart']['num']   = $cartNum;
        }
        return $data;
    }
}
