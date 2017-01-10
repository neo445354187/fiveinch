<?php
namespace fi\common\model;

use fi\common\helper\Browser;
use fi\common\helper\Tree;
use think\Session;

/**
 * 地区类
 */
class Areas extends Base
{

    /**
     * 省代号
     */
    const CODE_PROVINCE = 0;

    const CODE_CITY = 1;

    const CODE_DISTRICT = 2;

    const COUNTRY = '中国';

    const PROVINCE_EXT = '省';

    const CITY_EXT = '市';

    const SHOWING = 1;

    /**
     * 获取所有城市-根据字母分类
     */
    public function getCityGroupByKey()
    {
        $rs     = array();
        $rslist = $this->where('is_show=1 AND status = 1 AND area_type=1')->field('area_id,area_name,area_key')->order('area_key, area_sort')->select();
        foreach ($rslist as $key => $row) {
            $rs[$row["area_key"]][] = $row;
        }
        return $rs;
    }

    public function getArea($area_id2)
    {
        $rs = $this->where(["is_show" => 1, "status" => 1, "area_type" => 1, "area_id" => $area_id2])->field('area_id,area_name,area_key')->find();
        return $rs;
    }
    /**
     *  获取地区列表
     */
    public function listQuery($parent_id = 0)
    {
        $parent_id = ($parent_id > 0) ? $parent_id : (int) input('parent_id');
        return $this->where(['is_show' => 1, 'status' => 1, 'parent_id' => $parent_id])->field('area_id,area_name,parent_id')->order('area_sort desc')->select();
    }
    /**
     *  获取指定对象
     */
    public function getById($id)
    {
        return $this->where(["area_id" => (int) $id])->find()->toArray();
    }
    /**
     * 根据子分类获取其父级分类
     */
    public function getParentIs($id, $data = array())
    {
        $data[]   = $id;
        $parent_id = $this->where('area_id', $id)->value('parent_id');
        if ($parent_id == 0) {
            krsort($data);
            return $data;
        } else {
            return $this->getParentIs($parent_id, $data);
        }
    }

    /**
     * [getAddr 根据$area_id获取]
     * @param  [type] $area_id [description]
     * @return [type]         [description]
     */
    public function getAddr($area_id)
    {
        // 如果有筛选地区 获取上级地区信息
        if ($area_id !== 0) {
            $area_ids = $this->getParentIs($area_id);
            /*
            2 => int 440000
            1 => int 440100
            0 => int 440106
             */
            $selectArea = [];
            $area_name   = '';
            foreach ($area_ids as $k => $v) {
                $a = $this->getById($v);
                $area_name .= $a['area_name'];
                $selectArea[] = $a;
            }
            // 地区完整名称
            $selectArea['area_name'] = $area_name;
            // 当前选择的地区
            $data['areaInfo'] = $selectArea;

            $data['area2'] = $this->listQuery($area_ids[2]); // 广东的下级

            $data['area3'] = $this->listQuery($area_ids[1]); // 广州的下级
        } else {
            // 获取地区
            $data['area1'] = $this->listQuery(); // 省级
            // 默认地区信息
            $data['area2'] = $this->listQuery(440000); // 广东的下级
            $data['area3'] = $this->listQuery(440100); // 广州的下级
        }
        return $data;
    }

    /**
     * [getLocation 根据具体ip地址获取]
     * @return [type] [description]
     */
    public function getLocation()
    {
        // $ip      = request()->ip();//debug
        $ip      = '221.239.19.1';
        $ip_info = Browser::curl('http://int.dpool.sina.com.cn/iplookup/iplookup.php',
            ['format' => 'json', 'ip' => $ip]
        );
        $ip_info = json_decode($ip_info, true);
        if (isset($ip_info['country']) && $ip_info['country'] == self::COUNTRY) {
            $result = $this->getLocationByNameOrId($ip_info['province'], $ip_info['city']);
            if ($result) {
                return explode('_', $result['city'])[1];
            }
        }
        //地址未知，没查出来或者在国外
        return lang('UNKNOWN');

    }

    /**
     * [getLocationByNameOrId ]
     * @param  [type] $province [省名]
     * @param  [type] $city     [市名]
     * @param  [type] $is_id     [判断是否实参是否为id]
     * @return [type]           [description]
     */
    public function getLocationByNameOrId($province, $city, $is_id = false)
    {
        //组装sql
        $this->field('area_id, area_name, area_type')->limit(2);
        if ($is_id) {
            $this->where(['area_id' => ['IN', [$province, $city]]]);
        } else {
            $this->where([
                'area_name' => ['LIKE', "$province%"],
                'area_type' => ['IN', [self::CODE_PROVINCE, self::CODE_CITY]],
            ])->whereOr(['area_name' => ['LIKE', "$city%"]]);
        }
        $this->where([
            'status' => CODE_SUCCESS,
        ]);
        $result = $this->select();

        if ($result) {
            foreach ($result as $key => $res) {
                if ($res['area_type'] == self::CODE_PROVINCE) {
                    $data['province'] = $res['area_id'] . '_' . $res['area_name'];
                } else {
                    $data['city'] = $res['area_id'] . '_' . $res['area_name'];
                }

            }
            //加入session
            $this->addLocationToSession($data);
            return $data;
        }
        return false;
    }

    /**
     * [addLocationToSession 将用户地址加入session]
     * @param [type] $location [description]
     */
    private function addLocationToSession($location)
    {
        return Session::set('user_location', $location);
    }

    /**
     * [getProvincesAndCities 获取省市信息]
     * @return [type] [description]
     */
    public function getProvincesAndCities()
    {
        $list = $this->field('area_id, parent_id, area_name')
            ->where([
                'is_show'   => self::SHOWING,
                'status' => FLAG_ENABLE,
                'area_type' => ['IN', [self::CODE_PROVINCE, self::CODE_CITY]],
            ])->toArray()
            ->select();
        if ($list) {
            $list = Tree::list_to_tree($list, 'area_id', 'parent_id');
        }
        return $list;
    }

    /**
     * [getLocation 把含有省、市、区的记录数组返回下标分别为province、city、district的数组]
     * @param  [type] $area_id_path [description]
     * @return [array]        [description]
     */
    public function getLocationByAreaIdPath($area_id_path)
    {
        $area_ids  = explode('_', trim($area_id_path, '_'));
        $location = array();
        $addrs    = $this->field('area_id, area_name, area_type')
            ->where(['area_id' => ['IN', $area_ids]])
            ->limit(3)
            ->toArray()
            ->select();
        if ($addrs) {
            foreach ($addrs as $key => $addr) {
                switch ($addr['area_type']) {
                    case self::CODE_PROVINCE:
                        $location['province'] = $addr['area_id'] .'_'. $addr['area_name'];
                        break;
                    case self::CODE_CITY:
                        $location['city'] = $addr['area_id'] .'_'. $addr['area_name'];
                        break;
                    case self::CODE_DISTRICT:
                        $location['district'] = $addr['area_id'] .'_'. $addr['area_name'];
                        break;
                }
            }
        }
        return $location;
    }
}
