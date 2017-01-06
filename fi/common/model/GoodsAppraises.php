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
        $shopId = (int) session('FI_USER.shopId');

        $where                  = [];
        $where['g.goodsStatus'] = 1;
        $where['g.dataFlag']    = 1;
        $where['g.isSale']      = 1;
        $c1Id                   = (int) input('cat1');
        $c2Id                   = (int) input('cat2');
        $goodsName              = input('goodsName');
        if ($goodsName != '') {
            $where['g.goodsName'] = ['like', "%$goodsName%"];
        }
        if ($c2Id != 0 && $c1Id != 0) {
            $where['g.shopCatId2'] = $c2Id;
        } else if ($c1Id != 0) {
            $where['g.shopCatId1'] = $c1Id;
        }
        $where['g.shopId'] = $shopId;

        $model = model('goods');
        $data  = $model->alias('g')
            ->field('g.goodsId,g.goodsImg,g.goodsName,ga.shopReply,ga.id gaId,ga.replyTime,ga.goodsScore,ga.serviceScore,ga.timeScore,ga.content,ga.images,u.loginName')
            ->join('__GOODS_APPRAISES__ ga', 'g.goodsId=ga.goodsId', 'inner')
            ->join('__USERS__ u', 'u.userId=ga.userId', 'inner')
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
        $userId = (int) session('FI_USER.userId');

        $where                  = [];
        $where['g.goodsStatus'] = 1;
        $where['g.dataFlag']    = 1;
        $where['g.isSale']      = 1;

        $where['ga.userId'] = $userId;

        $model = model('goods');
        $data  = $model->alias('g')
            ->field('g.goodsId,g.goodsImg,g.goodsName,ga.goodsScore,ga.serviceScore,ga.timeScore,ga.content,ga.images,ga.shopReply,ga.replyTime,s.shopName,u.userName,o.orderNo')
            ->join('__GOODS_APPRAISES__ ga', 'g.goodsId=ga.goodsId', 'inner')
            ->join('__ORDERS__ o', 'o.orderId=ga.orderId', 'inner')
            ->join('__USERS__ u', 'u.userId=ga.userId', 'inner')
            ->join('__SHOPS__ s', 'o.shopId=s.shopId', 'inner')
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
        $orderId      = (int) input('orderId');
        $goodsId      = (int) input('goodsId');
        $goodsSpecId  = (int) input('goodsSpecId');
        $userId       = (int) session('FI_USER.userId');
        $goodsScore   = (int) input('goodsScore');
        $timeScore    = (int) input('timeScore');
        $serviceScore = (int) input('serviceScore');
        $orders       = model('orders')->where(['orderId' => $orderId, 'dataFlag' => 1])->field('orderStatus,orderNo,isAppraise,orderScore,shopId')->find();
        if (empty($orders)) {
            return FIReturn("无效的订单");
        }

        if ($orders['orderStatus'] != 2) {
            return FIReturn("订单状态已改变，请刷新订单后再尝试!");
        }

        //检测商品是否已评价
        $apCount = $this->where(['orderId' => $orderId, 'goodsId' => $goodsId, 'goodsSpecId' => $goodsSpecId])->count();
        if ($apCount > 0) {
            return FIReturn("该商品已评价!");
        }

        Db::startTrans();
        try {
            //增加订单评价
            $data                 = [];
            $data['userId']       = $userId;
            $data['goodsSpecId']  = $goodsSpecId;
            $data['goodsId']      = $goodsId;
            $data['shopId']       = $orders['shopId'];
            $data['orderId']      = $orderId;
            $data['goodsScore']   = $goodsScore;
            $data['serviceScore'] = $serviceScore;
            $data['timeScore']    = $timeScore;
            $data['content']      = input('content');
            $data['images']       = input('images');
            $data['createTime']   = date('Y-m-d H:i:s');
            $rs                   = $this->validate('GoodsAppraises.add')->allowField(true)->save($data);
            if ($rs !== false) {
                FIUseImages(0, $this->id, $data['images']);
                //增加商品评分，也就是说评价进入了goods_appraises表，而商品最终评分进入了goods_score表
                $prefix    = config('database.prefix');
                $updateSql = "update " . $prefix . "goods_scores set
				             totalScore=" . (int) ($goodsScore + $serviceScore + $timeScore) . ",
				             goodsScore=" . (int) $goodsScore . ",
				             serviceScore=" . (int) $serviceScore . ",
				             timeScore=" . (int) $timeScore . ",
				             totalUsers=totalUsers+1,goodsUsers=goodsUsers+1,serviceUsers=serviceUsers+1,timeUsers=timeUsers+1
				             where goodsId=" . $goodsId;
                Db::execute($updateSql);
                //增加商品评价数
                Db::table('__GOODS__')->where('goodsId', $goodsId)->setInc('appraiseNum');
                //增加店铺评分
                $updateSql = "update " . $prefix . "shop_scores set
				             totalScore=" . (int) ($goodsScore + $serviceScore + $timeScore) . ",
				             goodsScore=" . (int) $goodsScore . ",
				             serviceScore=" . (int) $serviceScore . ",
				             timeScore=" . (int) $timeScore . ",
				             totalUsers=totalUsers+1,goodsUsers=goodsUsers+1,serviceUsers=serviceUsers+1,timeUsers=timeUsers+1
				             where shopId=" . $orders['shopId'];
                Db::execute($updateSql);
                // 查询该订单是否已经完成评价,修改orders表中的isAppraise
                $ogRs = Db::table('__ORDER_GOODS__')->alias('og')
                    ->join('__GOODS_APPRAISES__ ga', 'og.orderId=ga.orderId and og.goodsId=ga.goodsId and og.goodsSpecId=ga.goodsSpecId', 'left')
                    ->where('og.orderId', $orderId)->field('og.id,ga.id gid');
                $isFinish = true;
                foreach ($ogRs as $key => $v) {
                    if ($v['id'] > 0 && $v['gid'] == '') {
                        $isFinish = false;
                        break;
                    }
                }
                //订单商品全部评价完则修改订单状态
                if ($isFinish) {
                    if (FIConf("isAppraisesScore") == 1) {
                        //给用户增加积分
                        $score                = [];
                        $score['userId']      = $userId;
                        $score['score']       = 5;
                        $score['dataSrc']     = 1;
                        $score['dataId']      = $orderId;
                        $score['dataRemarks'] = "评价订单【" . $orders['orderNo'] . "】获得积分5个";
                        $score['scoreType']   = 1;
                        $score['createTime']  = date('Y-m-d H:i:s');
                        model('UserScores')->save($score);
                    }
                    //修改订单评价状态
                    model('orders')->where('orderId', $orderId)->update(['isAppraise' => 1, 'isClosed' => 1]);
                }
                
                (new Redundancy())->edit($goodsId);

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

        $goodsId = (int) input('goodsId');
        $rs      = $this->alias('ga')
            ->field('ga.content,ga.images,ga.shopReply,ga.replyTime,ga.shopId,s.shopName,u.loginName,goodsSpecNames')
            ->join('__USERS__ u', 'ga.userId=u.userId', 'left')
            ->join('__ORDER_GOODS__  og', 'og.orderId=ga.orderId and og.goodsId=ga.goodsId', 'inner')
            ->join('__SHOPS__ s', 'ga.shopId=s.shopId', 'inner')
            ->where(['ga.goodsId' => $goodsId,
                'ga.dataFlag'         => 1,
                'ga.isShow'           => 1])->paginate()->toArray();
        foreach ($rs['Rows'] as $k => $v) {
            $rs['Rows'][$k]['goodsSpecNames'] = str_replace('@@_@@', '<br/>', $v['goodsSpecNames']);
            if ($anonymous) {
                $start                       = floor((strlen($v['loginName']) / 2)) - 1;
                $rs['Rows'][$k]['loginName'] = substr_replace($v['loginName'], '**', $start, 2);
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
    public function shopReply()
    {
        $id                = (int) input('id');
        $data['shopReply'] = input('reply');
        $data['replyTime'] = date('Y-m-d');
        $rs                = $this->where('id', $id)->update($data);
        if ($rs !== false) {
            return FIReturn('回复成功', 1);
        } else {
            return FIReturn('回复失败', -1);
        }

    }

}
