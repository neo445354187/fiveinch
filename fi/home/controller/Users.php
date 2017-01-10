<?php
namespace fi\home\controller;

use fi\common\model\LogSms;
use fi\common\model\Users as MUsers;

/**
 * 用户控制器
 */
class Users extends Base
{
    /**
     * 去登录
     */
    public function login()
    {
        $USER = session('FI_USER');
        //如果已经登录了则直接跳去用户中心
        if (!empty($USER) && $USER['user_id'] != '') {
            $this->redirect("users/index");
        }
        $login_name = cookie("login_name");
        if (!empty($login_name)) {
            $this->assign('login_name', cookie("login_name"));
        } else {
            $this->assign('login_name', '');
        }
        return $this->fetch('default/user_login');
    }

    /**
     * 用户退出
     */
    public function logout()
    {
        session('FI_USER', null);
        setcookie("login_password", null);
        return FIReturn("", 1);
    }

    /**
     * 用户注册
     *
     */
    public function regist()
    {
        $login_name = cookie("login_name");
        if (!empty($login_name)) {
            $this->assign('login_name', cookie("login_name"));
        } else {
            $this->assign('login_name', '');
        }
        return $this->fetch('default/regist');
    }

    /**
     * 新用户注册
     */
    public function toRegist()
    {
        $m  = new MUsers();
        $rs = $m->regist();
        return $rs;

    }

    /**
     * 验证登陆
     *
     */
    public function checkLogin()
    {
        $m  = new MUsers();
        $rs = $m->checkLogin();
        return $rs;
    }

    /**
     * 获取验证码
     */
    public function getPhoneVerifyCode()
    {
        $user_phone = input("post.user_phone");
        $rs        = array();
        if (!FIIsPhone($user_phone)) {
            return FIReturn("手机号格式不正确!");
            exit();
        }
        $m  = new MUsers();
        $rs = $m->checkUserPhone($user_phone, (int) session('FI_USER.user_id'));
        if ($rs["status"] != 1) {
            return FIReturn("手机号已存在!");
            exit();
        }
        $phoneVerify = rand(100000, 999999);
        $msg         = "欢迎您注册成为" . FIConf("CONF.mallName") . "会员，您的注册验证码为:" . $phoneVerify . "，请在10分钟内输入。【" . FIConf("mallName") . "】";
        $m           = new LogSms();
        $rv          = $m->sendSMS(0, $user_phone, $msg, 'getPhoneVerifyCode', $phoneVerify);

        if ($rv['status'] == 1) {
            session('VerifyCode_user_phone', $phoneVerify);
            session('VerifyCode_user_phone_Time', time());
        }
        return $rv;
    }

    /**
     * 判断手机或邮箱是否存在
     */
    public function checkLoginKey()
    {
        $m = new MUsers();
        if (input("post.login_name")) {
            $val = input("post.login_name");
        }

        if (input("post.user_phone")) {
            $val = input("post.user_phone");
        }

        if (input("post.user_email")) {
            $val = input("post.user_email");
        }

        $rs = FICheckLoginKey($val);
        if ($rs["status"] == 1) {
            return array("ok" => "");
        } else {
            return array("error" => $rs["msg"]);
        }
    }

    /**
     * 判断邮箱是否存在
     */
    public function checkEmail()
    {
        $data = $this->checkLoginKey();
        if (isset($data['error'])) {
            $data['error'] = '对不起，该邮箱已存在';
        }

        return $data;
    }

    /**
     * 判断用户名是否存在/忘记密码
     */
    public function checkFindKey()
    {
        $m      = new MUsers();
        $user_id = (int) session('FI_USER.user_id');
        $rs     = FICheckLoginKey(input("post.login_name"), $user_id);
        if ($rs["status"] == 1) {
            return array("error" => "该用户不存在！");
        } else {
            return array("ok" => "");
        }

    }

    /**
     * 跳到用户注册协议
     */
    public function protocol()
    {
        return $this->fetch("default/user_protocol");
    }

    /**
     * 用户中心
     */
    public function index()
    {
        session('FI_MENID0', 0);
        session('FI_MENUID30', 0);
        return $this->fetch('default/users/index');
    }

    /**
     * 跳去修改个人资料
     */
    public function edit()
    {
        $m = new MUsers();
        //获取用户信息
        $user_id = (int) session('FI_USER.user_id');
        $data   = $m->getById($user_id);
        $this->assign('data', $data);
        return $this->fetch('default/users/user_edit');
    }
    /**
     * 跳去修改密码页
     */
    public function editPass()
    {
        $m = new MUsers();
        //获取用户信息
        $user_id = (int) session('FI_USER.user_id');
        $data   = $m->getById($user_id);
        $this->assign('data', $data);
        return $this->fetch('default/users/security/user_pass');
    }
    /**
     * 修改密码
     */
    public function passedit()
    {
        $user_id = (int) session('FI_USER.user_id');
        $m      = new MUsers();
        $rs     = $m->editPass($user_id);
        return $rs;
    }
    /**
     * 修改
     */
    public function toEdit()
    {
        $m  = new MUsers();
        $rs = $m->edit();
        return $rs;
    }
    /**
     * 安全设置页
     */
    public function security()
    {
        //获取用户信息
        $m    = new MUsers();
        $data = $m->getById((int) session('FI_USER.user_id'));
        if ($data['user_phone'] != '') {
            $data['user_phone'] = FIStrReplace($data['user_phone'], '*', 3);
        }

        if ($data['user_email'] != '') {
            $data['user_email'] = FIStrReplace($data['user_email'], '*', 2, '@');
        }

        $this->assign('data', $data);
        return $this->fetch('default/users/security/index');
    }
    /**
     * 修改邮箱页
     */
    public function editEmail()
    {
        //获取用户信息
        $user_id = (int) session('FI_USER.user_id');
        $m      = new MUsers();
        $data   = $m->getById($user_id);
        if ($data['user_email'] != '') {
            $data['user_email'] = FIStrReplace($data['user_email'], '*', 2, '@');
        }

        $this->assign('data', $data);
        $process = 'One';
        $this->assign('process', $process);
        if ($data['user_email']) {
            return $this->fetch('default/users/security/user_edit_email');
        } else {
            return $this->fetch('default/users/security/user_email');
        }
    }
    /**
     * 发送验证邮件/绑定邮箱
     */
    public function getEmailVerify()
    {
        $user_email = input('post.user_email');
        if (!$user_email) {
            return FIReturn('请输入邮箱!', -1);
        }
        $code    = input("post.verifyCode");
        $process = input("post.process");
        if (!FIVerifyCheck($code)) {
            return FIReturn('验证码错误!', -1);
        }
        $rs = FICheckLoginKey($user_email, (int) session('FI_USER.user_id'));
        if ($rs["status"] != 1) {
            return FIReturn("邮箱已存在!");
            exit();
        }
        $base64 = new \org\Base64();
        $key    = $base64->encrypt($user_email . "_" . session('FI_USER.user_id') . "_" . time() . "_" . $process, (int) session('FI_USER.login_secret'), 30 * 60);
        $url    = url('home/users/emailEdit', array('key' => $key), true, true);
        $html   = "您好，会员 " . session('FI_USER.login_name') . "：<br>
        您在" . date('Y-m-d H:i:s') . "发出了绑定邮箱的请求,请点击以下链接进行绑定邮箱:<br>
        <a href='" . $url . "'>" . $url . "</a><br>
        <br>如果您的邮箱不支持链接点击，请将以上链接地址拷贝到你的浏览器地址栏中。<br>
        该验证邮件有效期为30分钟，超时请重新发送邮件。<br>
        <br><br>*此邮件为系统自动发出的，请勿直接回复。";
        $sendRs = FISendMail($user_email, '绑定邮箱', $html);
        if ($sendRs['status'] == 1) {
            return FIReturn('发送成功', 1);
        } else {
            return FIReturn($sendRs['msg'], -1);
        }
    }
    /**
     * 绑定邮箱
     */
    public function emailEdit()
    {
        $USER = session('FI_USER');
        if (empty($USER) && $USER['user_id'] == '') {
            $this->redirect("home/users/login");
        }
        $key = input('param.');
        if ($key['key'] == '') {
            $this->error('连接已失效！');
        }

        $key        = $key['key'];
        $keyFactory = new \org\Base64();
        $key        = $keyFactory->decrypt($key, (int) session('FI_USER.login_secret'));
        $key        = explode('_', $key);
        if (time() > floatval($key[2]) + 30 * 60) {
            $this->error('连接已失效！');
        }

        if (intval($key[1]) == 0) {
            $this->error('无效的用户！');
        }

        $rs = FICheckLoginKey($key[1], (int) session('FI_USER.user_id'));
        if ($rs["status"] != 1) {
            $this->error('邮箱已存在!');
            exit();
        }
        $m  = new MUsers();
        $rs = $m->editEmail($key[1], $key[0]);
        if ($rs['status'] == 1) {
            $process = 'Three';
            $this->assign('process', $process);
            if ($key[3] == 'Two') {
                return $this->fetch('default/users/security/user_edit_email');
            } else {
                return $this->fetch('default/users/security/user_email');
            }
        }
        $this->error('绑定邮箱失败');
    }
    /**
     * 发送验证邮件/修改邮箱
     */
    public function getEmailVerifyt()
    {
        $m         = new MUsers();
        $data      = $m->getById(session('FI_USER.user_id'));
        $user_email = $data['user_email'];
        if (!$user_email) {
            return FIReturn('请输入邮箱!', -1);
        }
        $code = input("post.verifyCode");
        if (!FIVerifyCheck($code)) {
            return FIReturn('验证码错误!', -1);
        }
        $base64 = new \org\Base64();
        $key    = $base64->encrypt("0_" . session('FI_USER.user_id') . "_" . time(), (int) session('FI_USER.login_secret'), 30 * 60);
        $url    = url('home/users/emailEditt', array('key' => $key), true, true);
        $html   = "您好，会员 " . session('FI_USER.login_name') . "：<br>
        您在" . date('Y-m-d H:i:s') . "发出了修改邮箱的请求,请点击以下链接进行修改邮箱:<br>
        <a href='" . $url . "'>" . $url . "</a><br>
        <br>如果您的邮箱不支持链接点击，请将以上链接地址拷贝到你的浏览器地址栏中。<br>
        该验证邮件有效期为30分钟，超时请重新发送邮件。<br>
        <br><br>*此邮件为系统自动发出的，请勿直接回复。";
        $sendRs = FISendMail($user_email, '修改邮箱', $html);
        if ($sendRs['status'] == 1) {
            return FIReturn('发送成功', 1);
        } else {
            return FIReturn($sendRs['msg'], -1);
        }
    }
    /**
     * 修改邮箱
     */
    public function emailEditt()
    {
        $USER = session('FI_USER');
        if (empty($USER) && $USER['user_id'] != '') {
            $this->redirect("home/users/login");
        }
        $key = input('param.');
        if ($key['key'] == '') {
            $this->error('连接已失效！');
        }

        $key        = $key['key'];
        $keyFactory = new \org\Base64();
        $key        = $keyFactory->decrypt($key, (int) session('FI_USER.login_secret'));
        $key        = explode('_', $key);
        if (time() > floatval($key[2]) + 30 * 60) {
            $this->error('连接已失效！');
        }

        if (intval($key[1]) == 0) {
            $this->error('无效的用户！');
        }

        $m    = new MUsers();
        $data = $m->getById($key[1]);
        if ($data['user_id'] == session('FI_USER.user_id')) {
            $process = 'Two';
            $this->assign('process', $process);
            return $this->fetch('default/users/security/user_edit_email');
        }
        $this->error('无效的用户！');
    }
    /**
     * 修改手机页
     */
    public function editPhone()
    {
        //获取用户信息
        $user_id = (int) session('FI_USER.user_id');
        $m      = new MUsers();
        $data   = $m->getById($user_id);
        if ($data['user_phone'] != '') {
            $data['user_phone'] = FIStrReplace($data['user_phone'], '*', 3);
        }

        $this->assign('data', $data);
        $process = 'One';
        $this->assign('process', $process);
        if ($data['user_phone']) {
            return $this->fetch('default/users/security/user_edit_phone');
        } else {
            return $this->fetch('default/users/security/user_phone');
        }
    }
    /**
     * 跳到发送手机验证
     */
    public function toApply()
    {
        return $this->fetch("default/user_verify_phone");
    }
    /**
     * 绑定手机/获取验证码
     */
    public function getPhoneVerifyo()
    {
        $user_phone = input("post.user_phone");
        if (!FIIsPhone($user_phone)) {
            return FIReturn("手机号格式不正确!");
            exit();
        }
        $rs = array();
        $m  = new MUsers();
        $rs = FICheckLoginKey($user_phone, (int) session('FI_USER.user_id'));
        if ($rs["status"] != 1) {
            return FIReturn("手机号已存在!");
            exit();
        }
        $phoneVerify = rand(100000, 999999);
        $msg         = "欢迎您" . FIConf("CONF.mallName") . "会员，正在操作绑定手机，您的校验码为:" . $phoneVerify . "，请在10分钟内输入。【" . FIConf("mallName") . "】";
        $m           = new LogSms();
        $rv          = $m->sendSMS(0, $user_phone, $msg, 'getPhoneVerify', $phoneVerify);
        if ($rv['status'] == 1) {
            $USER                = '';
            $USER['user_phone']   = $user_phone;
            $USER['phoneVerify'] = $phoneVerify;
            session('Verify_info', $USER);
            session('Verify_user_phone_Time', time());
            return FIReturn('短信发送成功!', 1);
        }
        return $rv;
    }
    /**
     * 绑定手机
     */
    public function phoneEdito()
    {
        $phoneVerify = input("post.Checkcode");
        $process     = input("post.process");
        $timeVerify  = session('Verify_user_phone_Time');
        if (!session('Verify_info.phoneVerify') || time() > floatval($timeVerify) + 10 * 60) {
            return FIReturn("校验码已失效，请重新发送！");
            exit();
        }
        if ($phoneVerify == session('Verify_info.phoneVerify')) {
            $m  = new MUsers();
            $rs = $m->editPhone((int) session('FI_USER.user_id'), session('Verify_info.user_phone'));
            if ($process == 'Two') {
                $rs['process'] = $process;
            } else {
                $rs['process'] = '0';
            }
            return $rs;
        }
        return FIReturn("校验码不一致，请重新输入！");
    }
    public function editPhoneSu()
    {
        $pr      = input("get.pr");
        $process = 'Three';
        $this->assign('process', $process);
        if ($pr == 'Two') {
            return $this->fetch('default/users/security/user_edit_phone');
        } else {
            return $this->fetch('default/users/security/user_phone');
        }
    }
    /**
     * 修改手机/获取验证码
     */
    public function getPhoneVerifyt()
    {
        $m           = new MUsers();
        $data        = $m->getById(session('FI_USER.user_id'));
        $user_phone   = $data['user_phone'];
        $phoneVerify = rand(100000, 999999);
        $msg         = "欢迎您" . FIConf("CONF.mallName") . "会员，正在操作修改手机，您的校验码为:" . $phoneVerify . "，请在10分钟内输入。【" . FIConf("mallName") . "】";
        $m           = new LogSms();
        $rv          = $m->sendSMS(0, $user_phone, $msg, 'getPhoneVerify', $phoneVerify);
        if ($rv['status'] == 1) {
            $USER                = '';
            $USER['user_phone']   = $user_phone;
            $USER['phoneVerify'] = $phoneVerify;
            session('Verify_info2', $USER);
            session('Verify_user_phone_Time2', time());
            return FIReturn('短信发送成功!', 1);
        }
        return $rv;
    }
    /**
     * 修改手机
     */
    public function phoneEditt()
    {
        $phoneVerify = input("post.Checkcode");
        $timeVerify  = session('Verify_user_phone_Time2');
        if (!session('Verify_info2.phoneVerify') || time() > floatval($timeVerify) + 10 * 60) {
            return FIReturn("校验码已失效，请重新发送！");
            exit();
        }
        if ($phoneVerify == session('Verify_info2.phoneVerify')) {
            return FIReturn("验证成功", 1);
        }
        return FIReturn("校验码不一致，请重新输入！", -1);
    }
    public function editPhoneSut()
    {
        $process = 'Two';
        $this->assign('process', $process);
        if (session('Verify_info2.phoneVerify')) {
            return $this->fetch('default/users/security/user_edit_phone');
        }
        $this->error('地址已失效，请重新验证身份');
    }

    /**
     * 处理图像裁剪
     */
    public function editUserPhoto()
    {
        $imageSrc = trim(input('post.photoSrc'), '/');
        $image    = \image\Image::open($imageSrc);
        $x        = (int) input('post.x');
        $y        = (int) input('post.y');
        $w        = (int) input('post.w', 150);
        $h        = (int) input('post.h', 150);
        $rs       = $image->crop($w, $h, $x, $y, 150, 150)->save($imageSrc);
        if ($rs) {
            return FIReturn('', 1, $imageSrc);
            exit;
        }
        return FIReturn('发生未知错误.', -1);

    }

    /**
     * 忘记密码
     */
    public function forgetPass()
    {
        return $this->fetch('default/forget_pass');
    }
    public function forgetPasst()
    {
        if (time() < floatval(session('findPass.findTime')) + 30 * 60) {
            $user_id = session('findPass.user_id');
            $m      = new MUsers();
            $info   = $m->getById($user_id);
            if ($info['user_phone'] != '') {
                $info['user_phone'] = FIStrReplace($info['user_phone'], '*', 3);
            }

            if ($info['user_email'] != '') {
                $info['user_email'] = FIStrReplace($info['user_email'], '*', 2, '@');
            }

            $this->assign('forgetInfo', $info);
            return $this->fetch('default/forget_pass2');
        } else {
            $this->error('页面已过期！');
        }
    }
    public function forgetPasss()
    {
        $USER = session('findPass');
        if (empty($USER) && $USER['user_id'] != '') {
            $this->error('请在同一浏览器操作！');
        }
        $key = input('param.');
        if ($key['key'] == '') {
            $this->error('连接已失效！');
        }

        $key        = $key['key'];
        $keyFactory = new \org\Base64();
        $key        = $keyFactory->decrypt($key, (int) session('findPass.login_secret'));
        $key        = explode('_', $key);
        if (time() > floatval($key[2]) + 30 * 60) {
            $this->error('连接已失效！');
        }

        if (intval($key[1]) == 0) {
            $this->error('无效的用户！');
        }

        session('REST_user_id', $key[1]);
        session('REST_Time', $key[2]);
        session('REST_success', '1');
        return $this->fetch('default/forget_pass3');
    }
    public function forgetPassf()
    {
        return $this->fetch('default/forget_pass4');
    }
    /**
     * 找回密码
     */
    public function findPass()
    {
        //禁止缓存
        header('Cache-Control:no-cache,must-revalidate');
        header('Pragma:no-cache');
        $code = input("post.verifyCode");
        $step = input("post.step/d");
        switch ($step) {
            case 1: #第一步，验证身份
                if (!FIVerifyCheck($code)) {
                    return FIReturn('验证码错误!', -1);
                }
                $login_name = input("post.login_name");
                $rs        = FICheckLoginKey($login_name);
                if ($rs["status"] == 1) {
                    return FIReturn("用户名不存在!");
                    exit();
                }
                $m    = new MUsers();
                $info = $m->checkAndGetLoginInfo($login_name);
                if ($info != false) {
                    session('findPass', array('user_id' => $info['user_id'], 'login_name' => $login_name, 'user_phone' => $info['user_phone'], 'user_email' => $info['user_email'], 'login_secret' => $info['login_secret'], 'findTime' => time()));
                    return FIReturn("操作成功", 1);
                } else {
                    return FIReturn("用户名不存在!");
                }

                break;
            case 2: #第二步,验证方式
                if (session('findPass.login_name') != null) {
                    if (input("post.modes") == 1) {
                        if (session('findPass.user_phone') == null) {
                            return FIReturn('你没有预留手机号码，请通过邮箱方式找回密码！', -1);
                        }
                        $phoneVerify = input("post.Checkcode");
                        if (!$phoneVerify) {
                            return FIReturn('校验码不能为空!', -1);
                        }
                        return $this->checkfindPhone($phoneVerify);
                    } else {
                        if (session('findPass.user_email') == null) {
                            return FIReturn('你没有预留邮箱，请通过手机号码找回密码！', -1);
                        }
                        if (!FIVerifyCheck($code)) {
                            return FIReturn('验证码错误!', -1);
                        }
                        return $this->getfindEmail();
                    }
                } else {
                    $this->error('页面已过期！');
                }

                break;
            case 3: #第三步,设置新密码
                $resetPass = session('REST_success');
                if ($resetPass != 1) {
                    $this->error("页面已失效!");
                }

                $login_password   = input("post.login_password");
                $repassword = input("post.repassword");
                if ($login_password == $repassword) {
                    $m  = new MUsers();
                    $rs = $m->resetPass();
                    if ($rs['status'] == 1) {
                        return $rs;
                    } else {
                        return $rs;
                    }
                } else {
                    return FIReturn('两次密码不同！', -1);
                }

                break;
            default:
                $this->error('页面已过期！');
                break;
        }
    }
    /**
     * 手机验证码获取
     */
    public function getfindPhone()
    {
        $smsVerfy = input("post.smsVerfy");
        session('FI_USER', session('findPass.user_id'));
        if (session('findPass.user_phone') == '') {
            return FIReturn('你没有预留手机号码，请通过邮箱方式找回密码！', -1);
        }
        $phoneVerify = rand(100000, 999999);
        $msg         = "您正在重置登录密码，验证码为:" . $phoneVerify . "，请在10分钟内输入。【" . FIConf("mallName") . "】";
        $m           = new LogSms();
        session('FI_USER', null);
        $rv = $m->sendSMS(0, session('findPass.user_phone'), $msg, 'getPhoneVerify', $phoneVerify);
        if ($rv['status'] == 1) {
            $USER                = '';
            $USER['phoneVerify'] = $phoneVerify;
            $USER['time']        = time();
            session('findPhone', $USER);
            return FIReturn('短信发送成功!', 1);
        }
        return $rv;
    }
    /**
     * 手机验证码检测
     * -1 错误，1正确
     */
    public function checkfindPhone($phoneVerify)
    {
        if (!session('findPhone.phoneVerify') || time() > floatval(session('findPhone.time')) + 10 * 60) {
            return FIReturn("校验码已失效，请重新发送！");
            exit();
        }
        if (session('findPhone.phoneVerify') == $phoneVerify) {
            $fuser_id = session('findPass.user_id');
            if (!empty($fuser_id)) {
                $rs['status'] = 1;
                $keyFactory   = new \org\Base64();
                $key          = $keyFactory->encrypt("0_" . session('findPass.user_id') . "_" . time(), (int) session('findPass.login_secret'), 30 * 60);
                $rs['url']    = url('Home/Users/forgetPasss', array('key' => $key), true, true);
                return $rs;
            }
            return FIReturn('无效用户', -1);
        }
        return FIReturn('校验码错误!', -1);
    }
    /**
     * 发送验证邮件/找回密码
     */
    public function getfindEmail()
    {
        $base64 = new \org\Base64();
        $key    = $base64->encrypt("0_" . session('findPass.user_id') . "_" . time(), (int) session('findPass.login_secret'), 30 * 60);
        $url    = url('Home/Users/forgetPasss', array('key' => $key), true, true);
        $html   = "您好，会员 " . session('findPass.login_name') . "：<br>
        您在" . date('Y-m-d H:i:s') . "发出了重置密码的请求,请点击以下链接进行密码重置:<br>
        <a href='" . $url . "'>" . $url . "</a><br>
        <br>如果您的邮箱不支持链接点击，请将以上链接地址拷贝到你的浏览器地址栏中。<br>
        该验证邮件有效期为30分钟，超时请重新发送邮件。<br>
        <br><br>*此邮件为系统自动发出的，请勿直接回复。";
        $sendRs = FISendMail(session('findPass.user_email'), '密码重置', $html);
        if ($sendRs['status'] == 1) {
            return FIReturn("操作成功", 1);
        } else {
            return FIReturn($sendRs['msg'], -1);
        }
    }

    /**
     * 加载登录小窗口
     */
    public function toLoginBox()
    {
        return $this->fetch('default/box_login');
    }
}
