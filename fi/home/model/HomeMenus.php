<?php

namespace fi\home\model;

/**
 * 菜单业务处理
 */
class HomeMenus extends Base
{

    /**
     * 获取菜单树
     */
    public function getMenus()
    {
        $data = cache('FI_HOME_MENUS');
        if (!$data) {
            $rs = $this->where(['is_show' => 1, 'status' => 1])
                ->field('menu_id,parent_id,menu_name,menu_url,menu_type')->order('menu_sort asc,menu_id asc')->select();
            $m1  = ['0' => [], '1' => []];
            $tmp = [];

            //获取第一级
            foreach ($rs as $key => $v) {
                if ($v['parent_id'] == 0) {
                    $m1[$v['menu_type']][$v['menu_id']] = ['menu_id' => $v['menu_id'], 'parent_id' => $v['parent_id'], 'menu_name' => $v['menu_name'], 'menu_url' => $v['menu_url']];
                } else {
                    $tmp[$v['parent_id']][] = ['menu_id' => $v['menu_id'], 'parent_id' => $v['parent_id'], 'menu_name' => $v['menu_name'], 'menu_url' => $v['menu_url']];
                }
            }
            //获取第二级
            foreach ($m1 as $key => $v) {
                foreach ($v as $key1 => $v1) {
                    if (isset($tmp[$v1['menu_id']])) {
                        $m1[$key][$key1]['list'] = $tmp[$v1['menu_id']];
                    }

                }
            }
            //获取第三级
            foreach ($m1 as $key => $v) {
                foreach ($v as $key1 => $v1) {
                    if (isset($v1['list'])) {
                        foreach ($v1['list'] as $key2 => $v2) {
                            if (isset($tmp[$v2['menu_id']])) {
                                $m1[$key][$key1]['list'][$key2]['list'] = $tmp[$v2['menu_id']];
                            }

                        }
                    }
                }
            }
            cache('FI_HOME_MENUS', $m1, 31536000);
            return $m1;
        }
        return $data;
    }

    /**
     * 获取菜单URL
     */
    public function getMenusUrl()
    {
        $data = cache('FI_PRO_MENUS');
        if (!$data) {
            $list  = $this->where('status', 1)->order('menu_type asc')->select();
            $menus = [];
            foreach ($list as $key => $v) {
                $menus[strtolower($v['menu_url'])] = $v['menu_type'];
                if ($v['menu_other_url'] != '') {
                    $str = explode(',', $v['menu_other_url']);
                    foreach ($str as $vkey => $vv) {
                        if ($vv == '') {
                            continue;
                        }

                        $menus[strtolower($vv)] = $v['menu_type'];
                    }
                }
            }
            cache('FI_PRO_MENUS', $menus, 31536000);
            return $menus;
        }
        return $data;
    }

}
