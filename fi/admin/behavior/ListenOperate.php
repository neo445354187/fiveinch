<?php
namespace fi\admin\behavior;

/**
 * 记录用户的访问日志
 */
class ListenOperate
{
    public function run(&$params)
    {
        $urls    = FIConf('listenUrl');
        $request = request();
        $visit   = strtolower($request->module() . "/" . $request->controller() . "/" . $request->action());
        if (array_key_exists($visit, $urls) && $urls[$visit]['isParent']) {
            $data                = [];
            $data['menu_id']      = $urls[$visit]['menu_id'];
            $data['operate_url']  = $_SERVER['REQUEST_URI'];
            $data['operate_desc'] = $urls[$visit]['name'];
            $data['content']     = !empty($_REQUEST) ? json_encode($_REQUEST) : '';
            $data['operate_ip']   = $request->ip();
            model('admin/LogOperates')->add($data);
        }
    }
}
