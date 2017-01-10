<?php

namespace fi\common\model;

use think\Db;

/**
 * 订单业务处理类
 */
class Orders extends Base {

    /**
     * 提交订单
     */
    public function submit() {
        $address_id = (int) input('post.s_address_id');
        $deliver_type = ((int) input('post.deliver_type') != 0) ? 1 : 0;
        $is_invoice = ((int) input('post.is_invoice') != 0) ? 1 : 0;
        $invoice_client = ($is_invoice == 1) ? input('post.invoice_client') : '';
        //pay_type为0是货到付款，1是在线付款，现在只支持在线付款，所以做出暂时修改
//        $pay_type = ((int) input('post.pay_type') != 0) ? 1 : 0;
        $pay_type = 1;
        $user_id = (int) session('FI_USER.user_id');
        //检测购物车
        $carts = model('carts')->getCarts(true);
        if (empty($carts['carts']))
            return FIReturn("请选择要购买的商品");
        //检测地址是否有效
        $address = Db::name('user_address')->where(['user_id' => $user_id, 'address_id' => $address_id, 'status' => 1])->find();
        if (empty($address)) {
            return FIReturn("无效的用户地址");
        }
        $area_ids = [];
        $areaMaps = [];
        $tmp = explode('_', $address['area_id_path']);
        $address['area_id2'] = $tmp[1]; //记录配送城市
        foreach ($tmp as $vv) {
            if ($vv == '')
                continue;
            if (!in_array($vv, $area_ids))
                $area_ids[] = $vv;
        }
        if (!empty($area_ids)) {
            $areas = Db::name('areas')->where(['status' => 1, 'area_id' => ['in', $area_ids]])->field('area_id,area_name')->select();
            foreach ($areas as $v) {
                $areaMaps[$v['area_id']] = $v['area_name'];
            }
            $tmp = explode('_', $address['area_id_path']);
            $area_names = [];
            foreach ($tmp as $vv) {
                if ($vv == '')
                    continue;
                $area_names[] = $areaMaps[$vv];
                $address['area_name'] = implode('', $area_names);
            }
        }
        $address['user_address'] = $address['area_name'] . $address['user_address'];
        FIUnset($address, 'is_default,status,create_time,user_id');
        //生成订单
        Db::startTrans();
        try {
            $order_unique = FIOrderQnique();
            foreach ($carts['carts'] as $ckey => $shopOrder) {
                $order_no = FIOrderNo();
                $order_score = 0;
                //创建订单
                $order = [];
                $order = array_merge($order, $address);
                $order['order_no'] = $order_no;
                $order['user_id'] = $user_id;
                $order['shop_id'] = $shopOrder['shop_id'];
                $order['pay_type'] = $pay_type;
                if ($pay_type == 1) {
                    $order['order_status'] = -2; //待付款
                    $order['is_pay'] = 0;
                } else {
                    $order['order_status'] = 0; //待发货
                }
                $order['goods_money'] = $shopOrder['goods_money'];
                $order['deliver_type'] = $deliver_type;
                $order['deliver_money'] = ($deliver_type == 1) ? 0 : FIOrderFreight($shopOrder['shop_id'], $order['area_id2']);
                $order['total_money'] = $order['goods_money'] + $order['deliver_money'];
                $order['real_total_money'] = $order['total_money'];
                $order['need_pay'] = $order['real_total_money'];
                //积分
                $order_score = 0;
                //如果开启下单获取积分则有积分
                if (FIConf('CONF.isOrderScore') == 1) {
                    $order_score = round($order['goods_money'], 0);
                }
                $order['order_score'] = $order_score;
                $order['is_invoice'] = $is_invoice;
                $order['invoice_client'] = $invoice_client;
                $order['order_remarks'] = input('post.remark_' . $shopOrder['shop_id']);
                $order['order_unique'] = $order_unique;
                $order['order_src'] = 0;
                $order['status'] = 1;
                $order['create_time'] = date('Y-m-d H:i:s');
                $result = $this->data($order, true)->isUpdate(false)->allowField(true)->save($order);
                if (false !== $result) {
                    $order_id = $this->order_id;
                    $orderTotalGoods = [];
                    foreach ($shopOrder['list'] as $gkey => $goods) {
                        //创建订单商品记录
                        $orderGgoods = [];
                        $orderGoods['order_id'] = $order_id;
                        $orderGoods['goods_id'] = $goods['goods_id'];
                        $orderGoods['goods_num'] = $goods['cart_num'];
                        $orderGoods['goods_price'] = $goods['shop_price'];
                        $orderGoods['goods_spec_id'] = $goods['goods_spec_id'];
                        if (!empty($goods['specNames'])) {
                            $specNams = [];
                            foreach ($goods['specNames'] as $pkey => $spec) {
                                $specNams[] = $spec['cat_name'] . '：' . $spec['item_name'];
                            }
                            $orderGoods['goods_spec_names'] = implode('@@_@@', $specNams);
                        }
                        $orderGoods['goods_name'] = $goods['goods_name'];
                        $orderGoods['goods_img'] = $goods['goods_img'];
                        $orderTotalGoods[] = $orderGoods;
                        //修改库存
                        if ($goods['goods_spec_id'] > 0) {
                            Db::name('goods_specs')->where('id', $goods['goods_spec_id'])->setDec('spec_stock', $goods['cart_num']);
                        }
                        Db::name('goods')->where('goods_id', $goods['goods_id'])->setDec('goods_stock', $goods['cart_num']);
                    }
                    Db::name('order_goods')->insertAll($orderTotalGoods);
                    //建立订单记录
                    $logOrder = [];
                    $logOrder['order_id'] = $order_id;
                    $logOrder['order_status'] = $order['order_status'];
                    $logOrder['log_content'] = ($pay_type == 1) ? "下单成功，等待用户支付" : "下单成功";
                    $logOrder['log_user_id'] = $user_id;
                    $logOrder['log_type'] = 0;
                    $logOrder['log_time'] = date('Y-m-d H:i:s');
                    Db::name('log_orders')->insert($logOrder);
                    //给店铺增加提示消息
                    FISendMsg($shopOrder['user_id'], "您有一笔新的订单【" . $order_no . "】待处理。", ['from' => 1, 'data_id' => $order_id]);
                }
            }
            //删除已选的购物车商品
            Db::name('carts')->where(['user_id' => $user_id, 'is_check' => 1])->delete();
            Db::commit();
            return FIReturn("提交订单成功", 1, $order_unique);
        } catch (\Exception $e) {
            print_r($e);
            Db::rollback();
            return FIReturn('提交订单失败', -1);
        }
    }

    /**
     * 根据订单唯一流水获取订单信息
     */
    public function getByUnique() {
        $id = input('id');
        $isBatch = input('isBatch/d', 1);
        $user_id = (int) session('FI_USER.user_id');
        if ($isBatch == 1) {
            $rs = $this->where(['user_id' => $user_id, 'order_unique' => $id])->field('order_id,order_no,pay_type,need_pay,order_unique,deliver_money')->select();
        } else {
            $rs = $this->where(['user_id' => $user_id, 'order_id' => $id])->field('order_id,order_no,pay_type,need_pay,order_unique,deliver_money')->select();
        }

        $data = [];
        $data['order_unique'] = $id;
        $data['list'] = [];
        $pay_type = 0;
        $total_money = 0;
        $order_ids = [];
        foreach ($rs as $key => $v) {
            if ($v['pay_type'] == 1)
                $pay_type = 1;
            $total_money = $total_money + $v['need_pay'];
            $order_ids[] = $v['order_id'];
            $data['list'][] = $v;
        }
        $data['total_money'] = $total_money;
        $data['pay_type'] = $pay_type;
        //如果是在线支付的话就要加载商品信息和支付信息
        if ($data['pay_type'] == 1) {
            //获取商品信息
            $goods = Db::name('order_goods')->where(['order_id' => ['in', $order_ids]])->select();
            foreach ($goods as $key => $v) {
                if ($v['goods_spec_names'] != '') {
                    $v['goods_spec_names'] = explode('@@_@@', $v['goods_spec_names']);
                } else {
                    $v['goods_spec_names'] = [];
                }
                $data['goods'][$v['order_id']][] = $v;
            }
            //获取支付信息
            $payments = model('payments')->where(['is_online' => 1, 'enabled' => 1])->order('pay_order asc')->select();
            $data['payments'] = $payments;
        }
        return $data;
    }

    /**
     * 获取用户订单列表
     */
    public function userOrdersByPage($order_status, $is_appraise = -1) {
        $user_id = (int) session('FI_USER.user_id');
        $order_no = (int) input('post.order_no');
        $shop_name = input('post.shop_name');
        $is_refund = (int) input('post.is_refund');
        $where = ['o.user_id' => $user_id, 'o.status' => 1];
        if (is_array($order_status)) {
            $where['order_status'] = ['in', $order_status];
        } else {
            $where['order_status'] = $order_status;
        }
        if ($is_appraise != -1)
            $where['is_appraise'] = $is_appraise;
        if ($order_no > 0) {
            $where['o.order_no'] = ['like', "%$order_no%"];
        }
        if ($shop_name != '') {
            $where['s.shop_name'] = ['like', "%$shop_name%"];
        }
        if ($is_refund > 0) {
            $where['is_refund'] = $is_refund;
        }

        $page = $this->alias('o')->join('__SHOPS__ s', 'o.shop_id=s.shop_id', 'left')->join('__ORDER_COMPLAINS__ oc', 'oc.order_id=o.order_id', 'left')->where($where)
                        ->field('o.order_id,o.order_no,s.shop_name,s.shop_id,s.shop_qq,s.shop_wangwang,o.goods_money,o.total_money,o.real_total_money,
		              o.order_status,o.deliver_type,deliver_money,pay_type,pay_from,o.order_status,need_pay,is_appraise,is_refund,order_src,o.create_time,oc.complain_id')
                        ->order('o.create_time', 'desc')
                        ->paginate(input('pagesize/d'))->toArray();
        if (count($page['Rows']) > 0) {
            $order_ids = [];
            foreach ($page['Rows'] as $v) {
                $order_ids[] = $v['order_id'];
            }
            $goods = Db::name('order_goods')->where('order_id', 'in', $order_ids)->select();
            $goodsMap = [];
            foreach ($goods as $v) {
                $v['goods_spec_names'] = str_replace('@@_@@', '、', $v['goods_spec_names']);
                $goodsMap[$v['order_id']][] = $v;
            }
            foreach ($page['Rows'] as $key => $v) {
                $page['Rows'][$key]['list'] = $goodsMap[$v['order_id']];
                $page['Rows'][$key]['isComplain'] = 1;
                if (($v['complain_id'] == '') && ($v['pay_type'] == 0 || ($v['pay_type'] == 1 && $v['order_status'] != 2))) {
                    $page['Rows'][$key]['isComplain'] = '';
                }
                $page['Rows'][$key]['pay_typeName'] = FILangPayType($v['pay_type']);
                $page['Rows'][$key]['deliver_type'] = FILangDeliverType($v['deliver_type'] == 1);
                $page['Rows'][$key]['status'] = FILangOrderStatus($v['order_status']);
            }
        }
        return $page;
    }

    /**
     * 获取商家订单
     */
    public function shopOrdersByPage($order_status) {
        $order_no = (int) input('post.order_no');
        $shop_name = input('post.shop_name');
        $pay_type = (int) input('post.pay_type');
        $deliver_type = (int) input('post.deliver_type');

        $shop_id = (int) session('FI_USER.shop_id');
        $where = ['shop_id' => $shop_id, 'status' => 1];
        if (is_array($order_status)) {
            $where['order_status'] = ['in', $order_status];
        } else {
            $where['order_status'] = $order_status;
        }
        if ($order_no > 0) {
            $where['order_no'] = ['like', "%$order_no%"];
        }
        if ($shop_name != '') {
            $where['shop_name'] = ['like', "%$shop_name%"];
        }
        if ($pay_type > -1) {
            $where['pay_type'] = $pay_type;
        }
        if ($deliver_type > -1) {
            $where['deliver_type'] = $deliver_type;
        }
        $page = $this->where($where)
                        ->field('order_id,order_no,goods_money,total_money,real_total_money,order_status,deliver_type,deliver_money,is_appraise
		              ,pay_type,pay_from,user_address,order_status,is_pay,is_appraise,user_name,order_src,create_time')
                        ->order('create_time', 'desc')
                        ->paginate()->toArray();
        if (count($page['Rows']) > 0) {
            $order_ids = [];
            foreach ($page['Rows'] as $v) {
                $order_ids[] = $v['order_id'];
            }
            $goods = Db::name('order_goods')->where('order_id', 'in', $order_ids)->select();
            $goodsMap = [];
            foreach ($goods as $v) {
                $v['goods_spec_names'] = str_replace('@@_@@', '、', $v['goods_spec_names']);
                $goodsMap[$v['order_id']][] = $v;
            }
            foreach ($page['Rows'] as $key => $v) {
                $page['Rows'][$key]['list'] = $goodsMap[$v['order_id']];
                $page['Rows'][$key]['pay_typeName'] = FILangPayType($v['pay_type']);
                $page['Rows'][$key]['deliver_type'] = FILangDeliverType($v['deliver_type'] == 1);
                $page['Rows'][$key]['status'] = FILangOrderStatus($v['order_status']);
            }
        }
        return $page;
    }

    /**
     * 商家发货
     */
    public function deliver() {
        $order_id = (int) input('post.id');
        $express_id = (int) input('post.express_id');
        $express_no = (int) input('post.express_no');
        $shop_id = (int) session('FI_USER.shop_id');
        $user_id = (int) session('FI_USER.user_id');
        $order = $this->where(['shop_id' => $shop_id, 'order_id' => $order_id, 'order_status' => 0])->field('order_id,order_no,user_id')->find();
        if (!empty($order)) {
            Db::startTrans();
            try {
                $data = ['order_status' => 1, 'express_id' => $express_id, 'express_no' => $express_no, 'deliveryTime' => date('Y-m-d H:i:s')];
                $result = $this->where('order_id', $order['order_id'])->update($data);
                if (false != $result) {
                    //新增订单日志
                    $logOrder = [];
                    $logOrder['order_id'] = $order_id;
                    $logOrder['order_status'] = 1;
                    $logOrder['log_content'] = "商家已发货" . (($express_no != '') ? "，快递号为：" . $express_no : "");
                    $logOrder['log_user_id'] = $user_id;
                    $logOrder['log_type'] = 0;
                    $logOrder['log_time'] = date('Y-m-d H:i:s');
                    Db::name('log_orders')->insert($logOrder);
                    //发送一条用户信息
                    $msg_content = "您的订单【" . $order['order_no'] . "】已发货啦" . (($express_no != '') ? "，快递号为：" . $express_no : "") . "，请做好收货准备哦~";
                    FISendMsg($order['user_id'], $msg_content, ['from' => 1, 'data_id' => $order_id]);
                    Db::commit();
                    return FIReturn('操作成功', 1);
                }
            } catch (\Exception $e) {
                Db::rollback();
                return FIReturn('操作失败', -1);
            }
        }
        return FIReturn('操作失败，请检查订单状态是否已改变');
    }

    /**
     * 用户收货
     */
    public function receive() {
        $order_id = (int) input('post.id');
        $user_id = (int) session('FI_USER.user_id');
        $order = $this->alias('o')->join('__SHOPS__ s', 'o.shop_id=s.shop_id', 'left')
                        ->where(['o.user_id' => $user_id, 'o.order_id' => $order_id, 'o.order_status' => 1])
                        ->field('o.order_id,o.order_no,s.user_id,o.order_score')->find();
        if (!empty($order)) {
            Db::startTrans();
            try {
                $data = ['order_status' => 2, 'receive_time' => date('Y-m-d H:i:s')];
                $result = $this->where('order_id', $order['order_id'])->update($data);
                if (false != $result) {
                    //新增订单日志
                    $logOrder = [];
                    $logOrder['order_id'] = $order_id;
                    $logOrder['order_status'] = 2;
                    $logOrder['log_content'] = "用户已收货";
                    $logOrder['log_user_id'] = $user_id;
                    $logOrder['log_type'] = 0;
                    $logOrder['log_time'] = date('Y-m-d H:i:s');
                    Db::name('log_orders')->insert($logOrder);
                    //发送一条商家信息
                    FISendMsg($order['user_id'], "您的订单【" . $order['order_no'] . "】，用户已签收", ['from' => 1, 'data_id' => $order_id]);
                    //给用户增加积分
                    if (FIConf("CONF.isOrderScore") == 1) {
                        $score = [];
                        $score['user_id'] = $user_id;
                        $score['score'] = $order['order_score'];
                        $score['data_src'] = 1;
                        $score['data_id'] = $order_id;
                        $score['data_remarks'] = "交易订单【" . $order['order_no'] . "】获得积分" . $order['order_score'] . "个";
                        $score['score_type'] = 1;
                        $score['create_time'] = date('Y-m-d H:i:s');
                        model('UserScores')->save($score);
                    }
                    Db::commit();
                    return FIReturn('操作成功', 1);
                }
            } catch (\Exception $e) {
                Db::rollback();
                return FIReturn('操作失败', -1);
            }
        }
        return FIReturn('操作失败，请检查订单状态是否已改变');
    }

    /**
     * 用户取消订单
     */
    public function cancel() {
        $order_id = (int) input('post.id');
        $reason = (int) input('post.reason');
        $user_id = (int) session('FI_USER.user_id');
        $order = $this->alias('o')->join('__SHOPS__ s', 'o.shop_id=s.shop_id', 'left')
                        ->where(['o.user_id' => $user_id, 'o.order_id' => $order_id, 'o.order_status' => ['in', [-2, 0]]])
                        ->field('o.order_id,o.order_no,s.user_id')->find();
        $reasonData = FIDatas(1, $reason);
        if (empty($reasonData))
            return FIReturn("无效的取消原因");
        if (!empty($order)) {
            Db::startTrans();
            try {
                $data = ['order_status' => -1, 'cancel_reason' => $reason];
                $result = $this->where('order_id', $order['order_id'])->update($data);
                if (false != $result) {
                    //返还商品库存
                    $goods = Db::table('__ORDER_GOODS__')->alias('og')->join('__GOODS__ g', 'og.goods_id=g.goods_id', 'inner')
                                    ->where('order_id', $order_id)->field('og.*,g.is_spec')->select();
                    foreach ($goods as $key => $v) {
                        //修改库存
                        if ($v['is_spec'] > 0) {
                            Db::name('goods_specs')->where('id', $v['goods_spec_id'])->setInc('spec_stock', $v['goods_num']);
                        }
                        Db::name('goods')->where('goods_id', $v['goods_id'])->setInc('goods_stock', $v['goods_num']);
                    }
                    //新增订单日志
                    $logOrder = [];
                    $logOrder['order_id'] = $order_id;
                    $logOrder['order_status'] = -1;
                    $logOrder['log_content'] = "用户取消订单，取消原因：" . $reasonData['data_name'];
                    $logOrder['log_user_id'] = $user_id;
                    $logOrder['log_type'] = 0;
                    $logOrder['log_time'] = date('Y-m-d H:i:s');
                    Db::name('log_orders')->insert($logOrder);
                    //发送一条商家信息
                    FISendMsg($order['user_id'], "订单【" . $order['order_no'] . "】用户已取消，取消原因：" . $reasonData['data_name'], ['from' => 1, 'data_id' => $order_id]);
                    Db::commit();
                    return FIReturn('订单取消成功', 1);
                }
            } catch (\Exception $e) {
                Db::rollback();
                print_r($e);
                return FIReturn('操作失败', -1);
            }
        }
        return FIReturn('操作失败，请检查订单状态是否已改变');
    }

    /**
     * 用户拒收订单
     */
    public function reject() {
        $order_id = (int) input('post.id');
        $reason = (int) input('post.reason');
        $content = input('post.content');
        $user_id = (int) session('FI_USER.user_id');
        $order = $this->alias('o')->join('__SHOPS__ s', 'o.shop_id=s.shop_id', 'left')
                        ->where(['o.user_id' => $user_id, 'o.order_id' => $order_id, 'o.order_status' => 1])
                        ->field('o.order_id,o.order_no,s.user_id')->find();
        $reasonData = FIDatas(2, $reason);
        if (empty($reasonData))
            return FIReturn("无效的拒收原因");
        if ($reason == 10000 && $content == '')
            return FIReturn("请输入拒收原因");
        if (!empty($order)) {
            Db::startTrans();
            try {
                $data = ['order_status' => -3, 'reject_reason' => $reason];
                if ($reason == 10000)
                    $data['reject_other_reason'] = $content;
                $result = $this->where('order_id', $order['order_id'])->update($data);
                if (false != $result) {
                    //新增订单日志
                    $logOrder = [];
                    $logOrder['order_id'] = $order_id;
                    $logOrder['order_status'] = -3;
                    $logOrder['log_content'] = "用户拒收订单，拒收原因：" . $reasonData['data_name'] . (($reason == 10000) ? "-" . $content : "");
                    $logOrder['log_user_id'] = $user_id;
                    $logOrder['log_type'] = 0;
                    $logOrder['log_time'] = date('Y-m-d H:i:s');
                    Db::name('log_orders')->insert($logOrder);
                    //发送一条商家信息
                    $msg_content = "订单【" . $order['order_no'] . "】用户拒收，拒收原因：" . $reasonData['data_name'] . (($reason == 10000) ? "-" . $content : "");
                    FISendMsg($order['user_id'], $msg_content, ['from' => 1, 'data_id' => $order_id]);
                    Db::commit();
                    return FIReturn('操作成功', 1);
                }
            } catch (\Exception $e) {
                Db::rollback();
                return FIReturn('操作失败', -1);
            }
        }
        return FIReturn('操作失败，请检查订单状态是否已改变');
    }

    /**
     * 获取订单价格
     */
    public function getMoneyByOrder() {
        $order_id = (int) input('post.id');
        return $this->where('order_id', $order_id)->field('order_id,goods_money,deliver_money,total_money,real_total_money')->find();
    }

    /**
     * 修改订单价格
     */
    public function editOrderMoney() {
        $order_id = input('post.id');
        $orderMoney = (float) input('post.orderMoney');
        $user_id = (int) session('FI_USER.user_id');
        $shop_id = (int) session('FI_USER.shop_id');
        if ($orderMoney < 0)
            return FIReturn("订单价格不能小于0");
        Db::startTrans();
        try {
            $result = $this->where(['order_id' => $order_id, 'shop_id' => $shop_id, 'order_status' => -2])->update(['real_total_money' => $orderMoney]);
            if (false !== $result) {
                //新增订单日志
                $logOrder = [];
                $logOrder['order_id'] = $order_id;
                $logOrder['order_status'] = -2;
                $logOrder['log_content'] = "商家修改订单价格为：" . $orderMoney;
                $logOrder['log_user_id'] = $user_id;
                $logOrder['log_type'] = 0;
                $logOrder['log_time'] = date('Y-m-d H:i:s');
                Db::name('log_orders')->insert($logOrder);
                Db::commit();
                return FIReturn('操作成功', 1);
            }
        } catch (\Exception $e) {
            Db::rollback();
            return FIReturn('操作失败', -1);
        }
    }

    /**
     * 商家同意/不同意拒收
     */
    public function confer() {
        $order_id = (int) input('post.id');
        $content = input('post.content');
        $status = ((int) input('post.status') == 1) ? 1 : 0;
        $user_id = (int) session('FI_USER.user_id');
        $shop_id = (int) session('FI_USER.shop_id');
        $order = $this->where(['shop_id' => $shop_id, 'order_id' => $order_id, 'order_status' => -3])
                        ->field('order_id,order_no,user_id')->find();
        if ($status == 0 && $content == '')
            return FIReturn("请输入不同意原因");
        if (!empty($order)) {
            Db::startTrans();
            try {
                $data = ['order_status' => (($status == 1) ? -4 : -5)];
                if ($status == 0)
                    $data['shop_reject_reason'] = $content;
                $result = $this->where('order_id', $order['order_id'])->update($data);
                if (false != $result) {
                    //新增订单日志
                    $logOrder = [];
                    $logOrder['order_status'] = (($status == 1) ? -4 : -5);
                    $logOrder['order_id'] = $order_id;
                    $logOrder['log_content'] = ($status == 1) ? "商家同意拒收订单" : "商家不同意拒收订单，原因：" . $content;
                    $logOrder['log_user_id'] = $user_id;
                    $logOrder['log_type'] = 0;
                    $logOrder['log_time'] = date('Y-m-d H:i:s');
                    Db::name('log_orders')->insert($logOrder);
                    //发送一条用户信息
                    $msg_content = "您的订单【" . $order['order_no'] . "】" . (($status == 1) ? "商家同意拒收订单" : "商家不同意拒收订单，原因：" . $content);
                    FISendMsg($order['user_id'], $msg_content, ['from' => 1, 'data_id' => $order_id]);
                    Db::commit();
                    return FIReturn('操作成功', 1);
                }
            } catch (\Exception $e) {
                Db::rollback();
                return FIReturn('操作失败', -1);
            }
        }
        return FIReturn('操作失败，请检查订单状态是否已改变');
    }

    /**
     * 获取订单详情
     */
    public function getByView($order_id) {
        $user_id = (int) session('FI_USER.user_id');
        $shop_id = (int) session('FI_USER.shop_id');
        $orders = $this->alias('o')->join('__EXPRESS__ e', 'o.express_id=e.express_id', 'left')
                        ->join('__SHOPS__ s', 'o.shop_id=s.shop_id', 'left')
                        ->join('__ORDER_REFUNDS__ orf ', 'o.order_id=orf.order_id', 'left')
                        ->where('o.status=1 and o.order_id=' . $order_id . ' and ( o.user_id=' . $user_id . ' or o.shop_id=' . $shop_id . ')')
                        ->field('o.*,e.express_name,s.shop_name,s.shop_qq,s.shop_wangwang,orf.refund_remark,orf.refund_time')->find();
        if (empty($orders))
            return FIReturn("无效的订单信息");

        //获取订单信息
        $orders['log'] = Db::name('log_orders')->where('order_id', $order_id)->order('log_id asc')->select();
        //获取订单商品
        $orders['goods'] = Db::name('order_goods')->where('order_id', $order_id)->order('id asc')->select();
        return $orders;
    }

    /**
     * 根据订单id获取 商品信息跟商品评价
     */
    public function getOrderInfoAndAppr() {
        $order_id = (int) input('oId');
        $goodsInfo = Db::name('order_goods')
                ->field('id,order_id,goods_name,goods_id,goods_spec_names,goods_img,goods_spec_id')
                ->where(['order_id' => $order_id])
                ->select();
        //根据商品id 与 订单id 取评价
        $alreadys = 0; // 已评价商品数
        $count = count($goodsInfo); //订单下总商品数
        if ($count > 0) {
            foreach ($goodsInfo as $k => $v) {
                $goodsInfo[$k]['goods_spec_names'] = str_replace('@@_@@', ';', $v['goods_spec_names']);
                $appraise = Db::name('goods_appraises')
                                ->field('goods_score,service_score,time_score,content,images,create_time')
                                ->where(['goods_id' => $v['goods_id'],
                                    'goods_spec_id' => $v['goods_spec_id'],
                                    'order_id' => $order_id,
                                    'status' => 1,
                                    'is_show' => 1,
                                ])->find();
                if (!empty($appraise)) {
                    ++$alreadys;
                    $appraise['images'] = ($appraise['images'] != '') ? explode(',', $appraise['images']) : [];
                }
                $goodsInfo[$k]['appraise'] = $appraise;
            }
        }
        return ['count' => $count, 'Rows' => $goodsInfo, 'alreadys' => $alreadys];
    }

    /**
     * 检查订单是否已支付
     */
    public function checkOrderPay() {
        $user_id = (int) session('FI_USER.user_id');
        $order_id = input("id");
        $isBatch = (int) input("isBatch");
        $rs = array();
        $where = ["user_id" => $user_id, "status" => 1, "order_status" => -2, "is_pay" => 0, "pay_type" => 1];
        if ($isBatch == 1) {
            $where['order_unique'] = $order_id;
        } else {
            $where['order_id'] = $order_id;
        }
        $rs = $this->field('order_id,order_no')->where($where)->select();
        if (count($rs) > 0) {
            return FIReturn('', 1);
        } else {
            return FIReturn('订单已支付', -1);
        }
    }

}
