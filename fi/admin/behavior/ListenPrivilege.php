<?php
namespace fi\admin\behavior;

/**
 * 检测有没有访问权限
 */
class ListenPrivilege
{
    public function run(&$params)
    {
        $privileges = session('FI_STAFF.privileges');
        //这个url在initConfig的behavior中设置的
        $urls       = FIConf('listenUrl');
        $request    = request();
        $visit      = strtolower($request->module() . "/" . $request->controller() . "/" . $request->action());
        //先判断url是否允许，在判断url对应的code是否在权限中存在
        if (array_key_exists($visit, $urls) && !in_array($urls[$visit]['code'], $privileges)) {
            if ($request->isAjax()) {
                echo json_encode(['status' => -998, 'msg' => '对不起，您没有操作权限，请与管理员联系']);
            } else {
                header("Content-type: text/html; charset=utf-8");
                echo "对不起，您没有操作权限，请与管理员联系";
            }
            exit();
        }
    }
}
