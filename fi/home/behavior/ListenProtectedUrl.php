<?php
namespace fi\home\behavior;

/**
 * 检测用户有没有登录和访问权限
 */
class ListenProtectedUrl
{
    public function run(&$params)
    {
        $request = request();
        $urls    = FIConf('protectedUrl'); //获取受保护url
        $visit   = strtolower($request->module() . "/" . $request->controller() . "/" . $request->action());
        //受保护资源进来检测身份
        if (isset($urls[$visit])) {
            $menu_type = (int) $urls[$visit];
            $user_type = -1;
            
            UID > 0 && $user_type = 0;
            SID > 0 && $user_type = 1;
            //未登录不允许访问受保护的资源
            if ($user_type == -1) {
                if ($request->isAjax()) {
                    echo json_encode(['status' => -999, 'msg' => '对不起，您还没有登录，请先登录']);
                } else {
                    header("Location:" . url('home/users/login'));
                }
                exit();
            }
            //已登录但不是商家 则不允许访问受保护的商家资源
            if ($user_type == 0 && $menu_type == 1) {
                if ($request->isAjax()) {
                    echo json_encode(['status' => -999, 'msg' => '对不起，您不是商家，请先申请为商家再访问']);
                } else {
                    header("Location:" . url('home/shops/login'));
                }
                exit();
            }

        }
    }
}
