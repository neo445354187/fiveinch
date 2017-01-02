<?php
namespace fi\common\helper;

/**
 * 访问外部的控制器，相当于浏览器功能
 */
class Tree
{

    /**
     * 对查询结果集进行排序
     *
     * @access public
     * @param array   $list   查询结果
     * @param string  $field  排序的字段名
     * @param string  $sortby 排序类型
     * asc正向排序 desc逆向排序 nat自然排序
     * @return array|bool
     */
    function list_sort_by( $list, $field, $sortby = 'asc' ) {
        if ( is_array( $list ) ) {
            $refer = $resultSet = [];
            foreach ( $list as $i => $data )
                $refer[$i] = &$data[$field];
            switch ( $sortby ) {
            case 'asc': // 正向排序
                asort( $refer );
                break;
            case 'desc':// 逆向排序
                arsort( $refer );
                break;
            case 'nat': // 自然排序
                natcasesort( $refer );
                break;
            }
            foreach ( $refer as $key => $val ) {
                $resultSet[] = &$list[$key];
            }
            return $resultSet;
        }
        return FALSE;
    }

    /**
     * 把返回的数据集转换成Tree
     *
     * @param array   $list  要转换的数据集
     * @param string  $pid   parent标记字段
     * @param string  $level level标记字段
     * @return array
     */
    function list_to_tree( $list, $pk = 'id', $pid = 'pid', $child = '_child', $root = 0 ) {
        // 创建Tree
        $tree = [];
        if ( is_array( $list ) ) {
            // 创建基于主键的数组引用
            $refer = [];
            foreach ( $list as $key => $data ) {
                $refer[$data[$pk]] = & $list[$key];
            }
            foreach ( $list as $key => $data ) {
                // 判断是否存在parent
                $parentId = $data[$pid];
                if ( $root == $parentId ) {
                    $tree[] = & $list[$key];
                } else {
                    if ( isset( $refer[$parentId] ) ) {
                        $parent = & $refer[$parentId];
                        $parent[$child][] = & $list[$key];
                    }
                }
            }
        }
        return $tree;
    }

    /**
     * 将list_to_tree的树还原成列表
     *
     * @param array   $tree  原来的树
     * @param string  $child 孩子节点的键
     * @param string  $order 排序显示的键，一般是主键 升序排列
     * @param array   $list  过渡用的中间数组，
     * @return array        返回排过序的列表数组
     * @author yangweijie <yangweijiester@gmail.com>
     */
    function tree_to_list( $tree, $child = '_child', $order = 'id', &$list = [] ) {
        if ( is_array( $tree ) ) {
            $refer = [];
            foreach ( $tree as $key => $value ) {
                $reffer = $value;
                if ( isset( $reffer[$child] ) ) {
                    unset( $reffer[$child] );
                    tree_to_list( $value[$child], $child, $order, $list );
                }
                $list[] = $reffer;
            }
            $list = list_sort_by( $list, $order, $sortby = 'asc' );
        }
        return $list;
    }
}
