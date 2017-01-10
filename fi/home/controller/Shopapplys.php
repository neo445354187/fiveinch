<?php

namespace fi\home\controller;

use fi\common\model\ShopApplys as M;
use fi\common\model\LogSms;

/**
 * 门店申请控制器
 */
class Shopapplys extends Base {

    /**
     * 判断手机或邮箱是否存在
     */
    public function checkShopPhone() {
        $m = new M();
        $user_id = (int) session('FI_USER.user_id');
        $rs = $m->checkShopPhone($user_id);
        if ($rs["status"] == 1) {
            return array("ok" => "");
        } else {
            return array("error" => $rs["msg"]);
        }
    }

    /**
     * 获取验证码
     */
    public function getPhoneVerifyCode() {
        $user_phone = input("post.user_phone2");
        $rs = array();
        if (!FIIsPhone($user_phone)) {
            return FIReturn("手机号格式不正确!");
            exit();
        }
        $m = new M();
        $rs = $m->checkShopPhone($user_phone, (int) session('FI_USER.user_id'));
        if ($rs["status"] != 1) {
            return FIReturn("对不起，该手机号已提交过开店申请，如有疑问请与商城管理员联系!");
            exit();
        }

        $phoneVerify = rand(100000, 999999);
        $msg = "欢迎您申请成为" . FIConf("CONF.mallName") . "商家，您的注册验证码为:" . $phoneVerify . "，请在10分钟内输入。【" . FIConf("CONF.mallName") . "】";
        $m = new LogSms();
        $rv = $m->sendSMS(0, $user_phone, $msg, 'getPhoneVerifyCode', $phoneVerify);

        if ($rv['status'] == 1) {
            session('VerifyCode_shopPhone', $phoneVerify);
            session('VerifyCode_shopPhone_Time', time());
        }
        return $rv;
    }

    /**
     * 提交申请
     */
    public function apply() {

        $m = new M();
        $rs = $m->addApply();
        return $rs;
    }

    /**
     * 跳到用户注册协议
     */
    public function protocol() {
        return $this->fetch("default/shop_protocol");
    }

}
