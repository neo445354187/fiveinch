<?php
namespace fi\common\helper;

/**
 * 访问外部的控制器，相当于浏览器功能
 */
class Browser
{

    /**
     * [curl curl访问]
     * @param  [type]  $url    [description]
     * @param  boolean $params [description]
     * @param  boolean $header [description]
     * @param  string  $method [description]
     * @return [type]          [description]
     */
    public function curl($url, $params = false, $header = false, $method = 'get')
    {
        if (is_array($params)) {
            $params = http_build_query($params);
        }
        $ch = curl_init();
        if ('GET' == strtoupper($method)) {
            if (!empty($params)) {
                curl_setopt($ch, CURLOPT_URL, $url . "?" . $params);
            } else {
                curl_setopt($ch, CURLOPT_URL, $url);
            }
        } else {
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        }

        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

        if (is_array($header)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        } else {
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Expect:']);
        }
        if (stripos($url, 'https://') === 0) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
}
