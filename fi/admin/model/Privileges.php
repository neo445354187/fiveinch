<?php
namespace fi\admin\model;

use think\Db;

/**
 * 权限业务处理
 */
class Privileges extends Base
{
    /**
     * 加载指定菜单的权限
     */
    public function listQuery($parent_id)
    {
        $rs = $this->where(['menu_id' => $parent_id, 'status' => 1])->order('privilege_id', 'asc')->select();
        return ['Rows' => $rs];
    }
    /**
     * 获取指定权限
     */
    public function getById($id)
    {
        return $this->get(['privilege_id' => $id, 'status' => 1]);
    }

    /**
     * 新增
     */
    public function add()
    {
        $result = $this->validate('Privileges.add')->allowField(true)->save(input('post.'));
        if (false !== $result) {
            cache('FI_LISTEN_URL', null);
            return FIReturn("新增成功", 1);
        } else {
            return FIReturn($this->getError(), -1);
        }
    }
    /**
     * 编辑
     */
    public function edit()
    {
        $id     = input('post.id/d');
        $result = $this->validate('Privileges.edit')->allowField(true)->save(input('post.'), ['privilege_id' => $id]);
        if (false !== $result) {
            cache('FI_LISTEN_URL', null);
            return FIReturn("编辑成功", 1);
        } else {
            return FIReturn($this->getError(), -1);
        }
    }
    /**
     * 删除
     */
    public function del()
    {
        $id               = input('post.id/d');
        $data             = [];
        $data['status'] = -1;
        $result           = $this->update($data, ['privilege_id' => $id]);
        if (false !== $result) {
            return FIReturn("删除成功", 1);
        } else {
            return FIReturn($this->getError(), -1);
        }
    }

    /**
     * 检测权限代码是否存在
     */
    public function checkPrivilegeCode()
    {
        $code = input('code');
        if ($code == '') {
            return FIReturn("", 1);
        }

        $rs = $this->where(['privilege_code' => $code, 'status' => 1])->Count();
        if ($rs == 0) {
            return FIReturn("", 1);
        }

        return FIReturn("该权限代码已存在!", -1);
    }

    /**
     * 加载权限并且标用户的权限
     */
    public function listQueryByRole($id)
    {
        $mrs = Db::table('__MENUS__')->alias('m')->join('__PRIVILEGES__ p', 'm.menu_id= p.menu_id and is_menu_privilege=1 and p.status=1', 'left')
            ->where(['parent_id' => $id, 'm.status' => 1])
            ->field('m.menu_id id,m.menu_name name,p.privilege_code,1 as isParent')
            ->order('menu_sort', 'asc')
            ->select();
        $prs = $this->where(['status' => 1, 'menu_id' => $id])->field('privilege_id id,privilege_name name,privilege_code,0 as isParent')->select();
        if ($mrs) {
            if ($prs) {
                foreach ($prs as $v) {
                    array_unshift($mrs, $v);
                }
            }
        } else {
            if ($prs) {
                $mrs = $prs;
            }

        }
        if (!$mrs) {
            return [];
        }

        $privileges = session("FI_STAFF.grant");
        if (count($privileges) > 0) {
            foreach ($mrs as $key => $v) {
                if ($v['isParent'] == 1) {
                    $mrs[$key]['isParent'] = true;
                    $mrs[$key]['open']     = true;
                } else {
                    $mrs[$key]['id'] = 'p' . $v['id'];
                }
            }
        }
        return $mrs;
    }
    /**
     * 加载全部权限
     */
    public function getAllPrivileges()
    {
        return $this->where(['status' => 1])
            ->field('menu_id,privilege_name,privilege_code,privilege_url,other_privilege_url')
            ->select();
    }
}
