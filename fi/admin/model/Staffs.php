<?php

namespace fi\admin\model;

use fi\admin\model\LogStaffLogins;
use fi\admin\model\Roles;
use think\Db;

/**
 * 职员业务处理
 */
class Staffs extends Base
{

    /**
     * 判断用户登录帐号密码
     * 说明：这里有权限判断，下面对表进行说明
     * 表
     * staffs：储存后台用户表，字段staff_role_id表示该用户的角色id
     * roles： 存储用户的角色表，字段privileges存储角色的权限code
     * privileges：存储着权限代码和url的映射
     * menus：菜单表，储存菜单
     * 
     */
    public function checkLogin()
    {
        $login_name = input("post.login_name");
        $login_password  = input("post.login_password");
        $code      = input("post.verifyCode");

        if (!FIVerifyCheck($code)) {
            return FIReturn('验证码错误!');
        }
        $staff = $this->where(['login_name' => $login_name, 'staff_status' => 1, 'status' => 1])->find();
        if (empty($staff)) {
            return FIReturn('账号或密码错误!');
        }

        if ($staff['login_password'] == md5($login_password . $staff['secret_key'])) {
            $staff->last_time = date('Y-m-d H:i:s');
            $staff->last_ip   = request()->ip();
            $staff->save();
            //记录登录日志
            LogStaffLogins::create([
                'staff_id'   => $staff['staff_id'],
                'login_time' => date('Y-m-d H:i:s'),
                'login_ip'   => request()->ip(),
            ]);
            //获取角色权限
            $role              = Roles::get(['status' => 1, 'role_id' => $staff['staff_role_id']]);
            $staff['role_name'] = $role['role_name'];
            //判断是否是最高管理员
            if ($staff['staff_id'] == 1) {
                $staff['privileges'] = Db::table('__PRIVILEGES__')->where(['status' => 1])->column('privilege_code');
                $staff['menu_ids']    = Db::table('__MENUS__')->where('status', 1)->column('menu_id');
            } else {
                $staff['privileges'] = explode(',', $role['privileges']);
                $staff['menu_ids']    = [];
                //获取管理员拥有的菜单
                if (!empty($staff['privileges'])) {
                    $menus = Db::table('__MENUS__')->alias('m')->join('__PRIVILEGES__ p', 'm.menu_id=p.menu_id and p.status=1', 'inner')
                        ->where(['p.privilege_code' => ['in', $staff['privileges']]])->field('m.menu_id')->select();
                    $menu_ids = [];
                    if (!empty($menus)) {
                        foreach ($menus as $key => $v) {
                            $menu_ids[] = $v['menu_id'];
                        }
                        $staff['menu_ids'] = $menu_ids;
                    }
                }
            }
            //存入了session
            session("FI_STAFF", $staff);
            return FIReturn("", 1, $staff);
        }
        return FIReturn('账号或密码错误!');
    }

    /**
     * 分页
     */
    public function pageQuery()
    {
        $key                 = input('get.key');
        $where               = [];
        $where['s.status'] = 1;
        if ($key != '') {
            $where['login_name|staff_name|staff_no'] = ['like', '%' . $key . '%'];
        }

        return Db::table('__STAFFS__')->alias('s')->join('__ROLES__ r', 's.staff_role_id=r.role_id and r.status=1', 'left')
            ->where($where)->field('staff_id,staff_name,login_name,work_itatus,staff_no,last_time,last_ip,role_name')
            ->order('staff_id', 'desc')->paginate(input('pagesize/d'));
    }

    /**
     * 删除
     */
    public function del()
    {
        $id               = input('post.id/d');
        $data             = [];
        $data['status'] = -1;
        Db::startTrans();
        try {
            $result = $this->update($data, ['staff_id' => $id]);
            if (false !== $result) {
                FIUnuseImage('staffs', 'staff_photo', $id);
                Db::commit();
                return FIReturn("删除成功", 1);
            }
        } catch (\Exception $e) {
            Db::rollback();
            return FIReturn('删除失败', -1);
        }
    }

    /**
     * 获取角色权限
     */
    public function getById($id)
    {
        return $this->get(['status' => 1, 'staff_id' => $id]);
    }

    /**
     * 新增
     */
    public function add()
    {
        $data               = input('post.');
        $data['secret_key']  = rand(1000, 9999);
        $data["login_password"]   = md5(input("post.login_password") . $data["secret_key"]);
        $data["staffFlag"]  = 1;
        $data["create_time"] = date('Y-m-d H:i:s');
        Db::startTrans();
        try {
            $result = $this->validate('Staffs.add')->allowField(true)->save($data);
            if (false !== $result) {
                FIUseImages(1, $this->staff_id, $data['staff_photo']);
                Db::commit();
                return FIReturn("新增成功", 1);
            }
        } catch (\Exception $e) {
            Db::rollback();
            return FIReturn('新增失败', -1);
        }
    }

    /**
     * 编辑
     */
    public function edit()
    {
        $id   = input('post.staff_id/d');
        $data = input('post.');
        FIUnset($data, 'staff_id,login_password,secret_key,status,create_time,last_time,last_ip');
        Db::startTrans();
        try {
            FIUseImages(1, $id, $data['staff_photo'], 'staffs', 'staff_photo');
            $result = $this->validate('Staffs.edit')->allowField(true)->save($data, ['staff_id' => $id]);
            if (false !== $result) {
                Db::commit();
                return FIReturn("编辑成功", 1);
            }
        } catch (\Exception $e) {
            print_r($e);
            Db::rollback();
            return FIReturn('编辑失败', -1);
        }
    }

    /**
     * 检测账号是否重复
     */
    public function checkLoginKey($key)
    {
        $rs = $this->where(['login_name' => $key, 'status' => 1])->count();
        return ($rs == 0) ? FIReturn('该账号可用', 1) : FIReturn("对不起，该账号已存在");
    }

    /**
     * 修改自己密码
     */
    public function editMyPass($staff_id)
    {
        if (input("post.newPass") == '') {
            FIReturn("密码不能为空");
        }

        $staff = $this->where('staff_id', $staff_id)->field('secret_key,login_password')->find();
        if (empty($staff)) {
            return FIReturn("修改失败");
        }

        $srcPass = md5(input("post.srcPass") . $staff["secret_key"]);
        if ($srcPass != $staff['login_password']) {
            return FIReturn("原密码错误");
        }

        $staff->login_password = md5(input("post.newPass") . $staff["secret_key"]);
        $result          = $staff->save();
        if (false !== $result) {
            return FIReturn("修改成功", 1);
        } else {
            return FIReturn($this->getError(), -1);
        }
    }

    /**
     * 修改职员密码
     */
    public function editPass($staff_id)
    {
        if (input("post.newPass") == '') {
            FIReturn("密码不能为空");
        }

        $staff = $this->where('staff_id', $staff_id)->field('secret_key')->find();
        if (empty($staff)) {
            return FIReturn("修改失败");
        }

        $staff->login_password = md5(input("post.newPass") . $staff["secret_key"]);
        $result          = $staff->save();
        if (false !== $result) {
            return FIReturn("修改成功", 1);
        } else {
            return FIReturn($this->getError(), -1);
        }
    }

}
