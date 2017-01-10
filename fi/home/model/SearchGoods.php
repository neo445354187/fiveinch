<?php
/**
 * 商品搜索
 *
 */

namespace fi\home\model;

use fi\common\helper\Browser;

class SearchGoods extends Base
{
    private $url = '';
    //facet的字段
    private $brand = '';
    /**
     * [$params 搜索必要参数]
     * @var [type]
     */
    private $optional_params = [
        'facet'          => 'on',
        'facet.field'    => 'brand_name',
        'facet.mincount' => 1,
        'facet.limit'    => 50,
        'fl'             => '*,score',
    ];

    /**
     * [$essential_params 必需参数]
     * @var [type]
     */
    private $essential_params = [
        'wt'      => 'json',
        'bq'      => '',
        'defType' => 'edismax',
    ];

    const PROVINCE_WEIGHT = 100;
    const CITY_WEIGHT     = 10000;

    public function __construct()
    {
        parent::__construct();

        $location = session('user_location');
        //把地址加入权重条件
        $this->essential_params['bq'] = '(province:' . $location['province'] . '^' . self::PROVINCE_WEIGHT . ') AND (city:' . $location['city'] . '^' . self::CITY_WEIGHT . ')';
        $this->url                    = 'http://' . config('SOLR_SERVICE') . '/solr/' . config('SOLR_CORE') . '/select';

    }

    /**
     * [getParams 组装条件]
     * @param  [type] $params [description]
     * @return [type]         [返回组装好的条件]
     */
    private function _getParams($params)
    {
        $condition = array();
        //搜索条件用双引号包裹，搜索不分词
        ($search = trim(urldecode($params['keyword']))) && $condition['q'][] = "($search)";
        if ($this->brand = solr_escape(trim($params['brand_name'], ','))) {
            $condition['q'][] = '(brand_name:"' . $this->brand . '")';
            $condition        = array_merge($condition, $this->essential_params);
        } else {
            $condition = array_merge($condition, $this->optional_params, $this->essential_params);
        }
        $condition['q'] = isset($condition['q']) ? implode(' AND ', $condition['q']) : '*:*';

        // 进行分页数据获取和判断
        $condition['rows']  = config('SOLR_PAGE');
        $page_limit         = ceil(config('SOLR_LIMIT') / $condition['rows']);
        $p                  = $params['p'] > $page_limit ? 1 : $params['p'];
        $condition['start'] = ($p - 1) * $condition['rows'];
        //排序
        switch ($params['orderBy']) {
            case 'price':
                $condition['sort'] = $params['upOrDown'] == 'down' ? 'shop_price desc' : 'shop_price asc';
                break;
            case 'appraise_num':
            case 'sale_num':
                $condition['sort'] = $params['orderBy'] . ' desc';
                break;
        }
        $condition['_'] = ceil(microtime(true) * 1000);
        return $condition;
    }

    /**
     * [findAll 根据条件获取solr数据]
     * @param  [type] $condition [description]
     * @return [type]            [description]
     */
    public function findAll($condition)
    {
        $condition = $this->_getParams($condition);
        $result    = json_decode(Browser::curl($this->url, $condition), true);
        //粗暴判断，有数据返回和没有数据返回两种情况
        if (isset($result['response']['numFound']) && $result['response']['numFound'] > 0) {
            //判断是否需要整理facet数据
            $facet = array();
            if (!$this->brand) {
                $facet = array_filter($result['facet_counts']['facet_fields']['brand_name'], function ($val, $key) {
                    return ($key % 2 == 0 && $val != '') ? true : false;
                }, ARRAY_FILTER_USE_BOTH);
            }
            //组装facet结果
            return [
                'numFound'    => $result['response']['numFound'],
                'list'        => $result['response']['docs'],
                'facetFields' => $facet,
            ];
        } else {
            return false;
        }

    }

}
