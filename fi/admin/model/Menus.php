<?php
namespace fi\admin\model;

use think\Db;

/**
 * 菜单业务处理
 */
class Menus extends Base
{
    protected $insert = ['status' => 1];
    /**
     * 获取菜单列表
     */
    public function listQuery($parent_id = -1)
    {
        if ($parent_id == -1) {
            return ['id' => 0, 'name' => FIConf('CONF.mallName'), 'isParent' => true, 'open' => true];
        }

        $rs = $this->where(['parent_id' => $parent_id, 'status' => 1])->field('menu_id id,menu_name name')->order('menu_sort', 'asc')->select();
        if (count($rs) > 0) {
            foreach ($rs as $key => $v) {
                $rs[$key]['isParent'] = true;
            }
        };
        return $rs;
    }
    /**
     * 获取菜单
     */
    public function getById($id)
    {
        return $this->get(['status' => 1, 'menu_id' => $id]);
    }

    /**
     * 新增菜单
     */
    public function add()
    {
        $result = $this->validate('Menus.add')->save(input('post.'));
        if (false !== $result) {
            return FIReturn("新增成功", 1);
        } else {
            return FIReturn($this->getError(), -1);
        }
    }
    /**
     * 编辑菜单
     */
    public function edit()
    {
        $menu_id = input('post.menu_id/d');
        $result = $this->validate('Menus.edit')->allowField(['menu_name', 'menu_sort'])->save(input('post.'), ['menu_id' => $menu_id]);
        if (false !== $result) {
            return FIReturn("编辑成功", 1);
        } else {
            return FIReturn($this->getError(), -1);
        }
    }
    /**
     * 删除菜单
     */
    public function del()
    {
        $menu_id           = input('post.id/d');
        $data             = [];
        $data['status'] = -1;
        $result           = $this->update($data, ['menu_id' => $menu_id]);
        if (false !== $result) {
            return FIReturn("删除成功", 1);
        } else {
            return FIReturn($this->getError(), -1);
        }
    }

    /**
     * 获取用户菜单
     */
    public function getMenus()
    {
        $STAFF = session('FI_STAFF');
        return $this->where(['parent_id' => 0, 'status' => 1, 'menu_id' => ['in', $STAFF['menu_ids']]])->field('menu_id,menu_name')->order('menu_sort', 'asc')->select();
    }

    /**
     * 获取子菜单
     */
    public function getSubMenus($parent_id)
    {
        //用户权限判断
        $STAFF      = session('FI_STAFF');
        $allowMenus = [];
        $rs2        = $this->where(['parent_id' => $parent_id, 'status' => 1, 'menu_id' => ['in', $STAFF['menu_ids']]])->field('menu_id,menu_name')->order('menu_sort', 'asc')->select();
        foreach ($rs2 as $key2 => $v2) {
            if (!in_array($v2['menu_id'], $STAFF['menu_ids'])) {
                continue;
            }

            $rs3 = Db::table('__MENUS__')->alias('m')
                ->join('__PRIVILEGES__ p', 'm.menu_id= p.menu_id and is_menu_privilege=1 and p.status=1', 'inner')
                ->where(['parent_id' => $v2['menu_id'], 'm.status' => 1, 'm.menu_id' => ['in', $STAFF['menu_ids']]])
                ->field('m.menu_id,m.menu_name,privilege_url')
                ->order('menu_sort', 'asc')
                ->select();
            if (!empty($rs3)) {
                $rs2[$key2]['list'] = $rs3;
            }

        }
        return $rs2;
    }
}
