<?php
namespace fi\common\helper;
use fi\common\controller\Base;
/**
 * 访问外部的控制器，相当于浏览器功能
 */
class Browser extends Base{

    /**
     * [curl 这个先将就用//debug]
     * @param  [type]  $url     [description]
     * @param  [type]  $params  [description]
     * @param  boolean $is_post [description]
     * @return [type]           [description]
     */
    public function curl($url, $params, $is_post = false)
    {
        $param_str = http_build_query($params);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15); //设置连接等待时间
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if ($is_post) {
            curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
            curl_setopt($ch, CURLOPT_POSTFIELDS, $param_str);
        } else {
            $url .= '?'.$param_str;
        }
        curl_setopt($ch, CURLOPT_URL,$url);//抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
}
