<?php
namespace fi\common\model;

use Think\Db;
use fi\home\model\Shops;

/**
 * 用户类
 */
class Users extends Base
{
    /**
     * 用户登录验证
     */
    public function checkLogin()
    {
        $login_name   = input("post.login_name");
        $login_password    = input("post.login_password");
        $code        = input("post.verifyCode");
        // $rememberPwd = input("post.rememberPwd", 1);//second 不允许记住密码
        if (!FIVerifyCheck($code) && strpos(FIConf("CONF.captcha_model"), "4") >= 0) {
            return FIReturn('验证码错误!');
        }
        $rs = $this->where("login_name|user_email|user_phone", $login_name)
            ->where(["status" => 1, "user_status" => 1])
            ->find();
        if (!empty($rs)) {
            $user_id = $rs['user_id'];
            //获取用户等级
            $rrs               = Db::name('user_ranks')->where('start_score', '<=', $rs['user_total_score'])->where('end_score', '>=', $rs['user_total_score'])->field('rank_id,rank_name,rebate,userrank_img')->find();
            $rs['rank_id']      = $rrs['rank_id'];
            $rs['rank_name']    = $rrs['rank_name'];
            $rs['userrank_img'] = $rrs['userrank_img'];
            //判断是否为商家登陆
            if (input("post.typ") == 2) {
                $shoprs = $this->where(["status" => 1, "user_status" => 1, "user_type" => 1, "user_id" => $user_id])->find();
                if (empty($shoprs)) {
                    return FIReturn('您还没申请店铺!');
                }
            }
            
            if ($rs['login_password'] != md5($login_password . $rs['login_secret'])) {
                return FIReturn("密码错误");
            }

            $ip = request()->ip();
            $this->where(["user_id" => $user_id])->update(["last_time" => date('Y-m-d H:i:s'), "last_ip" => $ip]);
            //如果是店铺则加载店铺信息
            if ($rs['user_type'] >= 1) {
                $shop = (new Shops())->getShopInfoAndAddress($user_id);
                // $shop  = $shops->where(["user_id" => $user_id, "status" => 1])->find();
                if (!empty($shop)) {
                    $rs = array_merge($shop, $rs->toArray());
                }

            }
            //second 居然把登陆密码、secret以及用户的账户金额都放在session中，删除掉
            FIUnset($rs, 'bank_no,bank_username,login_secret,login_password,user_money,lock_money');

            //记录登录日志
            $data              = array();
            $data["user_id"]    = $user_id;
            $data["login_time"] = date('Y-m-d H:i:s');
            $data["login_ip"]   = $ip;
            Db::name('log_user_logins')->insert($data);

            // $rd = $rs;
            //记住密码;second 不能记录密码，不然商家浏览器关闭后，其他人将可以使用
            // cookie("login_name", $login_name, time() + 3600 * 24 * 90);
            cookie("login_name", $login_name);
            //second 不允许记住密码
            // if ($rememberPwd == "on") {
            //     $datakey = md5($rs['login_name']) . "_" . md5($rs['login_password']);
            //     $key     = $rs['login_secret'];
            //     //加密
            //     $base64   = new \org\Base64();
            //     $loginKey = $base64->encrypt($datakey, $key);
            //     cookie("login_password", $loginKey, time() + 3600 * 24 * 90);
            // } else {
            //     cookie("login_password", null);
            // }
            session('FI_USER', $rs);
            return FIReturn("", "1");
        }
        return FIReturn("用户不存在");
    }

    /**
     * 会员注册
     */
    public function regist()
    {

        $data              = array();
        $data['login_name'] = input("post.login_name");
        $data['login_password']  = input("post.login_password");
        $data['reUserPwd'] = input("post.reUserPwd");
        $login_name         = $data['login_name'];
        //检测账号是否存在
        $crs = FICheckLoginKey($login_name);
        if ($crs['status'] != 1) {
            return $crs;
        }

        if ($data['login_password'] != $data['reUserPwd']) {
            return FIReturn("两次输入密码不一致!");
        }
        foreach ($data as $v) {
            if ($v == '') {
                return FIReturn("注册信息不完整!");
            }
        }
        $nameType   = (int) input("post.nameType");
        $mobileCode = input("post.mobileCode");
        $code       = input("post.verifyCode");
        if ($nameType != 3 && !FIVerifyCheck($code)) {
            return FIReturn("验证码错误!");
        }
        if ($nameType == 3 && FIConf("CONF.phoneVerfy") == 1) {
//手机号码
            $data['user_phone'] = $login_name;
            $verify            = session('VerifyCode_user_phone');
            $startTime         = (int) session('VerifyCode_user_phone_Time');
            if ((time() - $startTime) > 120) {
                return FIReturn("验证码已超过有效期!");
            }
            if ($mobileCode == "" || $verify != $mobileCode) {
                return FIReturn("验证码错误!");
            }
            $login_name = FIRandomLoginName($login_name);
        } else if ($nameType == 1) {
//邮箱注册
            $data['user_email'] = $login_name;
            $unames            = explode("@", $login_name);
            $login_name         = FIRandomLoginName($unames[0]);

        }
        if ($login_name == '') {
            return FIReturn("注册失败!");
        }
//分派不了登录名
        $data['login_name'] = $login_name;
        unset($data['reUserPwd']);
        unset($data['protocol']);
        //检测账号，邮箱，手机是否存在
        $data["login_secret"] = rand(1000, 9999);
        $data['login_password']    = md5($data['login_password'] . $data['login_secret']);
        $data['user_type']    = 0;
        $data['user_name']    = input("post.user_name");
        $data['user_qq']      = "";
        $data['user_score']   = 0;
        $data['create_time']  = date('Y-m-d H:i:s');
        $data['status']    = 1;
        Db::startTrans();
        try {
            $user_id = $this->data($data)->save();
            if (false !== $user_id) {
                $data             = array();
                $ip               = request()->ip();
                $data['last_time'] = date('Y-m-d H:i:s');
                $data['last_ip']   = $ip;
                $user_id           = $this->user_id;
                $this->where(["user_id" => $user_id])->update($data);
                //记录登录日志
                $data              = array();
                $data["user_id"]    = $user_id;
                $data["login_time"] = date('Y-m-d H:i:s');
                $data["login_ip"]   = $ip;
                Db::name('log_user_logins')->insert($data);
                $user = $this->get($user_id);
                session('FI_USER', $user);
                Db::commit();
                return FIReturn("", 1);
            }
        } catch (\Exception $e) {
            Db::rollback();
        }
        return FIReturn("注册失败!");
    }

    /**
     * 查询用户手机是否存在
     *
     */
    public function checkUserPhone($user_phone, $user_id = 0)
    {
        $dbo = $this->where(["status" => 1, "user_phone" => $user_phone]);
        if ($user_id > 0) {
            $dbo->where("user_id", "<>", $user_id);
        }
        $rs = $dbo->count();
        if ($rs > 0) {
            return FIReturn("手机号已存在!");
        } else {
            return FIReturn("", 1);
        }
    }

    /**
     * 修改用户密码
     */
    public function editPass($id)
    {
        $data             = array();
        $data["login_password"] = input("post.newPass");
        if (!$data["login_password"]) {
            return FIReturn('密码不能为空', -1);
        }
        $rs = $this->where('user_id=' . $id)->find();
        //核对密码
        if ($rs['login_password']) {
            if ($rs['login_password'] == md5(input("post.oldPass") . $rs['login_secret'])) {
                $data["login_password"] = md5(input("post.newPass") . $rs['login_secret']);
                $rs               = $this->update($data, ['user_id' => $id]);
                if (false !== $rs) {
                    return FIReturn("密码修改成功", 1);
                } else {
                    return FIReturn($this->getError(), -1);
                }
            } else {
                return FIReturn('原始密码错误', -1);
            }
        } else {
            $data["login_password"] = md5(input("post.newPass") . $rs['login_secret']);
            $rs               = $this->update($data, ['user_id' => $id]);
            if (false !== $rs) {
                return FIReturn("密码修改成功", 1);
            } else {
                return FIReturn($this->getError(), -1);
            }
        }
    }
    /**
     *  获取用户信息
     */
    public function getById($id)
    {
        $rs          = $this->get(['user_id' => (int) $id]);
        $rs['ranks'] = Db::name('user_ranks')->where('start_score', '<=', $rs['user_total_score'])->where('end_score', '>=', $rs['user_total_score'])->field('rank_id,rank_name,rebate,userrank_img')->find();
        return $rs;
    }
    /**
     * 编辑资料
     */
    public function edit()
    {
        $Id   = (int) input('post.user_id/d');
        $data = input('post.');
        FIAllow($data, 'brithday,true_name,user_name,user_id,user_photo,user_qq,user_sex');
        Db::startTrans();
        try {
            FIUseImages(0, $Id, $data['user_photo'], 'users', 'user_photo');
            $result = $this->allowField(true)->save($data, ['user_id' => $Id]);
            if (false !== $result) {
                Db::commit();
                return FIReturn("编辑成功", 1);
            }
        } catch (\Exception $e) {
            Db::rollback();
            return FIReturn('编辑失败', -1);
        }
    }
    /**
     * 绑定邮箱
     */
    public function editEmail($user_id, $user_email)
    {
        $data              = array();
        $data["user_email"] = $user_email;
        $rs                = $this->update($data, ['user_id' => $user_id]);
        if (false !== $rs) {
            return FIReturn("", 1);
        } else {
            return FIReturn("", -1);
        }
    }
    /**
     * 绑定手机
     */
    public function editPhone($user_id, $user_phone)
    {
        $data              = array();
        $data["user_phone"] = $user_phone;
        $rs                = $this->update($data, ['user_id' => $user_id]);
        if (false !== $rs) {
            return FIReturn("绑定成功", 1);
        } else {
            return FIReturn($this->getError(), -1);
        }
    }
    /**
     * 查询并加载用户资料
     */
    public function checkAndGetLoginInfo($key)
    {
        if ($key == '') {
            return array();
        }

        $rs = $this->where(["login_name|user_email|user_phone" => ['=', $key], 'status' => 1])->find();
        return $rs;
    }
    /**
     * 重置用户密码
     */
    public function resetPass()
    {
        if (time() > floatval(session('REST_Time')) + 30 * 60) {
            return FIReturn("连接已失效！", -1);
        }
        $reset_user_id = (int) session('REST_user_id');
        if ($reset_user_id == 0) {
            return FIReturn("无效的用户！", -1);
        }
        $user = $this->where(["status" => 1, "user_status" => 1, "user_id" => $reset_user_id])->find();
        if (empty($user)) {
            return FIReturn("无效的用户！", -1);
        }
        $login_password = input("post.login_password");
        if (trim($login_password) == '') {
            return FIReturn("无效的密码！", -1);
        }
        $data['login_password'] = md5($login_password . $user["login_secret"]);
        $rc               = $this->update($data, ['user_id' => $reset_user_id]);
        if (false !== $rc) {
            return FIReturn("修改成功", 1);
        }
        session('REST_user_id', null);
        session('REST_Time', null);
        session('REST_success', null);
        session('findPass', null);
        return $rs;
    }

    /**
     * 获取用户可用积分
     */
    public function getFieldsById($user_id, $fields)
    {
        return $this->where(['user_id' => $user_id, 'status' => 1])->field($fields)->find();
    }
}
