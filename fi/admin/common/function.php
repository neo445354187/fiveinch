<?php
/**
 */
/**
 * 加载系统访问路径
 */
function FIVisitPrivilege()
{
    $listenUrl = cache('FI_LISTEN_URL');
    if (!$listenUrl) {
        $list      = model('admin/Privileges')->getAllPrivileges();
        $listenUrl = [];
        foreach ($list as $v) {
            if ($v['privilege_url'] == '') {
                continue;
            }

            $listenUrl[strtolower($v['privilege_url'])] = [
                'code'     => $v['privilege_code'],
                'url'      => $v['privilege_url'],
                'name'     => $v['privilege_name'],
                'isParent' => true,
                'menu_id'   => $v['menu_id'],
            ];
            if (strpos($v['other_privilege_url'], '/') !== false) {
                $t = explode(',', $v['other_privilege_url']);
                foreach ($t as $vv) {
                    if (strpos($vv, '/') !== false) {
                        $listenUrl[strtolower($vv)] = [
                            'code'     => $v['privilege_code'],
                            'url'      => $vv,
                            'name'     => $v['privilege_name'],
                            'isParent' => false,
                            'menu_id'   => $v['menu_id'],
                        ];
                    }
                }
            }
        }
        cache('FI_LISTEN_URL', $listenUrl);
    }
    return $listenUrl;
}

/**
 * 判断有没有权限
 * @param $code 权限代码
 * @param $type 返回的类型  true-boolean   false-string
 */
function FIGrant($code)
{
    $STAFF = session("FI_STAFF");
    if (in_array($code, $STAFF['privileges'])) {
        return true;
    }

    return false;
}

/**
 * 循环删除指定目录下的文件及文件夹
 * @param string $dirpath 文件夹路径
 */
function FIDelDir($dirpath)
{
    $dh = opendir($dirpath);
    while (($file = readdir($dh)) !== false) {
        if ($file != "." && $file != "..") {
            $fullpath = $dirpath . "/" . $file;
            if (!is_dir($fullpath)) {
                unlink($fullpath);
            } else {
                FIDelDir($fullpath);
                rmdir($fullpath);
            }
        }
    }
    closedir($dh);
    $isEmpty = true;
    $dh      = opendir($dirpath);
    while (($file = readdir($dh)) !== false) {
        if ($file != "." && $file != "..") {
            $isEmpty = false;
            break;
        }
    }
    return $isEmpty;
}
