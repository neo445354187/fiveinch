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
        $user_id = (int) session('FI_USER.user_id');
        $shop_id = (int) session('FI_USER.shop_id');
        $data   = [];
        if (in_array('message', $tasks)) {
            //获取用户未读消息
            $data['message']['num'] = Db::name('messages')->where(['receive_user_id' => $user_id, 'msg_status' => 0])->count();
            $data['message']['id']  = 49;
        }
        //获取商家待处理订单
        if (in_array('shoporder', $tasks)) {
            $data['shoporder']['24'] = Db::name('orders')->where(['shop_id' => $shop_id, 'order_status' => 0, 'status' => 1])->count();
            $data['shoporder']['45'] = Db::name('orders')->where(['shop_id' => $shop_id, 'order_status' => -3, 'status' => 1])->count();
            $data['shoporder']['25'] = Db::name('order_complains')->where(['respond_target_id' => $shop_id, 'complain_status' => 1])->count();
            $data['shoporder']['55'] = Db::name('orders')->where(['shop_id' => $shop_id, 'order_status' => -2, 'status' => 1])->count();
            //获取库存预警数量
            $goodsn                  = Db::table('__GOODS__')->where('shop_id =' . $shop_id . ' and status = 1 and goods_stock <= warn_stock and is_spec = 0 and warn_stock>0')->count();
            $specsn                  = Db::table('__GOODS_SPECS__')->where('shop_id =' . $shop_id . ' and status = 1 and spec_stock <= warn_stock and warn_stock>0')->count();
            $data['shoporder']['54'] = $goodsn + $specsn;
        }
        //获取用户订单状态
        if (in_array('userorder', $tasks)) {
            $data['userorder']['3'] = Db::name('orders')->where(['user_id' => $user_id, 'order_status' => -2, 'status' => 1])->count();
            $data['userorder']['5'] = Db::name('orders')->where(['user_id' => $user_id, 'order_status' => 1, 'status' => 1])->count();
            $data['userorder']['6'] = Db::name('orders')->where(['user_id' => $user_id, 'order_status' => 2, 'is_appraise' => 0, 'status' => 1])->count();
        }
        //获取用户购物车数量
        if (in_array('cart', $tasks)) {
            $cart_num      = 0;
            $rs           = Db::name('carts')->field('cart_num')->where(['user_id' => $user_id])->select();
            foreach ($rs as $key => $v) {
                $cart_num = $cart_num + $v['cart_num'];
            }
            $data['cart']['goods'] = count($rs);
            $data['cart']['num']   = $cart_num;
        }
        return $data;
    }
}
