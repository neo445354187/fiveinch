<?php
namespace fi\home\controller;

use fi\common\model\Areas as M;

/**
 * 地区控制器
 */
class Areas extends Base
{

    /**
     * 获取地区信息
     */
    public function listQuery()
    {
        $m  = new M();
        $rs = $m->listQuery();
        return FIReturn('', 1, $rs);
    }

    /**
     * [FunctionName 获取省市信息，并返回给前端]
     * @param string $value [description]
     */
    public function getProvincesAndCities()
    {
        if ($cache = cache(__CLASS__ . __METHOD__)) {
            $this->result = $cache;
        } else {
            $list = model('Areas')->getProvincesAndCities();
            if ($list) {
                $this->result['data'] = $this->fetch('default/areas/getProvincesAndCities', ['list' => $list]);
                cache(__CLASS__ . __METHOD__, $this->result, 3600 * 24);
            } else {
                $this->result['status'] = CODE_FAIL;
                $this->result['msg']    = lang('Failed to obtain regional information');
            }
        }
        return $this->result;
    }

    /**
     * [setLocation 设置用户地址]
     */
    public function setLocation()
    {
        $province_id = input('post.province_id/d');
        $city_id     = input('post.city_id/d');
        $result      = model('Areas')->getLocationByNameOrId($province_id, $city_id, true);
        if (!$result) {
            $this->result['status'] = CODE_FAIL;
        }

        return $this->result;
    }
}
