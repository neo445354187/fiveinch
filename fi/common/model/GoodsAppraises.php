<?php
namespace fi\common\model;

/**
 * 评价类
 */
use think\Db;

class GoodsAppraises extends Base
{

    public function queryByPage()
    {
        $shop_id = (int) session('FI_USER.shop_id');

        $where                  = [];
        $where['g.goods_status'] = 1;
        $where['g.status']    = 1;
        $where['g.is_sale']      = 1;
        $c1Id                   = (int) input('cat1');
        $c2Id                   = (int) input('cat2');
        $goods_name              = input('goods_name');
        if ($goods_name != '') {
            $where['g.goods_name'] = ['like', "%$goods_name%"];
        }
        if ($c2Id != 0 && $c1Id != 0) {
            $where['g.shop_cat_id2'] = $c2Id;
        } else if ($c1Id != 0) {
            $where['g.shop_cat_id1'] = $c1Id;
        }
        $where['g.shop_id'] = $shop_id;

        $model = model('goods');
        $data  = $model->alias('g')
            ->field('g.goods_id,g.goods_img,g.goods_name,ga.shop_reply,ga.id gaId,ga.reply_time,ga.goods_score,ga.service_score,ga.time_score,ga.content,ga.images,u.login_name')
            ->join('__GOODS_APPRAISES__ ga', 'g.goods_id=ga.goods_id', 'inner')
            ->join('__USERS__ u', 'u.user_id=ga.user_id', 'inner')
            ->where($where)
            ->paginate()->toArray();
        if ($data !== false) {
            return FIReturn('', 1, $data['Rows']);
        } else {
            return FIReturn($this->getError(), -1);
        }
    }
    /**
     * 用户评价
     */
    public function userAppraise()
    {
        $user_id = (int) session('FI_USER.user_id');

        $where                  = [];
        $where['g.goods_status'] = 1;
        $where['g.status']    = 1;
        $where['g.is_sale']      = 1;

        $where['ga.user_id'] = $user_id;

        $model = model('goods');
        $data  = $model->alias('g')
            ->field('g.goods_id,g.goods_img,g.goods_name,ga.goods_score,ga.service_score,ga.time_score,ga.content,ga.images,ga.shop_reply,ga.reply_time,s.shop_name,u.user_name,o.order_no')
            ->join('__GOODS_APPRAISES__ ga', 'g.goods_id=ga.goods_id', 'inner')
            ->join('__ORDERS__ o', 'o.order_id=ga.order_id', 'inner')
            ->join('__USERS__ u', 'u.user_id=ga.user_id', 'inner')
            ->join('__SHOPS__ s', 'o.shop_id=s.shop_id', 'inner')
            ->where($where)
            ->paginate()->toArray();
        if ($data !== false) {
            return FIReturn('', 1, $data['Rows']);
        } else {
            return FIReturn($this->getError(), -1);
        }
    }
    /**
     * 添加评价
     */
    public function add()
    {
        //检测订单是否有效
        $order_id      = (int) input('order_id');
        $goods_id      = (int) input('goods_id');
        $goods_spec_id  = (int) input('goods_spec_id');
        $user_id       = (int) session('FI_USER.user_id');
        $goods_score   = (int) input('goods_score');
        $time_score    = (int) input('time_score');
        $service_score = (int) input('service_score');
        $orders       = model('orders')->where(['order_id' => $order_id, 'status' => 1])->field('order_status,order_no,is_appraise,order_score,shop_id')->find();
        if (empty($orders)) {
            return FIReturn("无效的订单");
        }

        if ($orders['order_status'] != 2) {
            return FIReturn("订单状态已改变，请刷新订单后再尝试!");
        }

        //检测商品是否已评价
        $apCount = $this->where(['order_id' => $order_id, 'goods_id' => $goods_id, 'goods_spec_id' => $goods_spec_id])->count();
        if ($apCount > 0) {
            return FIReturn("该商品已评价!");
        }

        Db::startTrans();
        try {
            //增加订单评价
            $data                 = [];
            $data['user_id']       = $user_id;
            $data['goods_spec_id']  = $goods_spec_id;
            $data['goods_id']      = $goods_id;
            $data['shop_id']       = $orders['shop_id'];
            $data['order_id']      = $order_id;
            $data['goods_score']   = $goods_score;
            $data['service_score'] = $service_score;
            $data['time_score']    = $time_score;
            $data['content']      = input('content');
            $data['images']       = input('images');
            $data['create_time']   = date('Y-m-d H:i:s');
            $rs                   = $this->validate('GoodsAppraises.add')->allowField(true)->save($data);
            if ($rs !== false) {
                FIUseImages(0, $this->id, $data['images']);
                //增加商品评分，也就是说评价进入了goods_appraises表，而商品最终评分进入了goods_score表
                $prefix    = config('database.prefix');
                $updateSql = "update " . $prefix . "goods_scores set
				             total_score=" . (int) ($goods_score + $service_score + $time_score) . ",
				             goods_score=" . (int) $goods_score . ",
				             service_score=" . (int) $service_score . ",
				             time_score=" . (int) $time_score . ",
				             total_users=total_users+1,goods_users=goods_users+1,service_users=service_users+1,time_users=time_users+1
				             where goods_id=" . $goods_id;
                Db::execute($updateSql);
                //增加商品评价数
                Db::table('__GOODS__')->where('goods_id', $goods_id)->setInc('appraise_num');
                //增加店铺评分
                $updateSql = "update " . $prefix . "shop_scores set
				             total_score=" . (int) ($goods_score + $service_score + $time_score) . ",
				             goods_score=" . (int) $goods_score . ",
				             service_score=" . (int) $service_score . ",
				             time_score=" . (int) $time_score . ",
				             total_users=total_users+1,goods_users=goods_users+1,service_users=service_users+1,time_users=time_users+1
				             where shop_id=" . $orders['shop_id'];
                Db::execute($updateSql);
                // 查询该订单是否已经完成评价,修改orders表中的is_appraise
                $ogRs = Db::table('__ORDER_GOODS__')->alias('og')
                    ->join('__GOODS_APPRAISES__ ga', 'og.order_id=ga.order_id and og.goods_id=ga.goods_id and og.goods_spec_id=ga.goods_spec_id', 'left')
                    ->where('og.order_id', $order_id)->field('og.id,ga.id gid');
                $isFinish = true;
                foreach ($ogRs as $key => $v) {
                    if ($v['id'] > 0 && $v['gid'] == '') {
                        $isFinish = false;
                        break;
                    }
                }
                //订单商品全部评价完则修改订单状态
                if ($isFinish) {
                    if (FIConf("is_appraisesScore") == 1) {
                        //给用户增加积分
                        $score                = [];
                        $score['user_id']      = $user_id;
                        $score['score']       = 5;
                        $score['data_src']     = 1;
                        $score['data_id']      = $order_id;
                        $score['data_remarks'] = "评价订单【" . $orders['order_no'] . "】获得积分5个";
                        $score['score_type']   = 1;
                        $score['create_time']  = date('Y-m-d H:i:s');
                        model('UserScores')->save($score);
                    }
                    //修改订单评价状态
                    model('orders')->where('order_id', $order_id)->update(['is_appraise' => 1, 'is_closed' => 1]);
                }
                
                (new Redundancy())->edit($goods_id);

                Db::commit();
                return FIReturn('评价成功', 1);
            } else {
                return FIReturn($this->getError(), -1);
            }
        } catch (\Exception $e) {
            Db::rollback();
            // print_r($e);
            return FIReturn('评价失败', -1);
        }

    }
    /**
     * 根据商品id取评论
     */
    public function getById()
    {
        // 处理匿名
        $anonymous = (int) input('anonymous');

        $goods_id = (int) input('goods_id');
        $rs      = $this->alias('ga')
            ->field('ga.content,ga.images,ga.shop_reply,ga.reply_time,ga.shop_id,s.shop_name,u.login_name,goods_spec_names')
            ->join('__USERS__ u', 'ga.user_id=u.user_id', 'left')
            ->join('__ORDER_GOODS__  og', 'og.order_id=ga.order_id and og.goods_id=ga.goods_id', 'inner')
            ->join('__SHOPS__ s', 'ga.shop_id=s.shop_id', 'inner')
            ->where(['ga.goods_id' => $goods_id,
                'ga.status'         => 1,
                'ga.is_show'           => 1])->paginate()->toArray();
        foreach ($rs['Rows'] as $k => $v) {
            $rs['Rows'][$k]['goods_spec_names'] = str_replace('@@_@@', '<br/>', $v['goods_spec_names']);
            if ($anonymous) {
                $start                       = floor((strlen($v['login_name']) / 2)) - 1;
                $rs['Rows'][$k]['login_name'] = substr_replace($v['login_name'], '**', $start, 2);
            }
        }
        if ($rs !== false) {
            return FIReturn('', 1, $rs);
        } else {
            return FIReturn($this->getError(), -1);
        }
    }

    /**
     * 商家回复评价
     */
    public function shop_reply()
    {
        $id                = (int) input('id');
        $data['shop_reply'] = input('reply');
        $data['reply_time'] = date('Y-m-d');
        $rs                = $this->where('id', $id)->update($data);
        if ($rs !== false) {
            return FIReturn('回复成功', 1);
        } else {
            return FIReturn('回复失败', -1);
        }

    }

}
