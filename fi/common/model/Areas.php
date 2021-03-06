<?php
namespace fi\common\model;
use fi\common\helper\Browser;
use think\Session;
/**
 * 地区类
 */
class Areas extends Base{

	const CODE_PROVINCE = 0;
	// const $CODE_CITY = 0;

	const COUNTRY = '中国';

	const PROVINCE_EXT = '省';

	const CITY_EXT = '市';
	
 	/**
	   * 获取所有城市-根据字母分类
	   */
	public function getCityGroupByKey(){
		$rs = array();
	  	$rslist = $this->where('isShow=1 AND dataFlag = 1 AND areaType=1')->field('areaId,areaName,areaKey')->order('areaKey, areaSort')->select();
	  	foreach ($rslist as $key =>$row){
	  		$rs[$row["areaKey"]][] = $row;
	  	}
	  	return $rs;
	}
	
	public function getArea($areaId2){
		$rs = $this->where(["isShow"=>1,"dataFlag"=>1,"areaType"=>1,"areaId"=>$areaId2])->field('areaId,areaName,areaKey')->find();
		return $rs;
	}
	/**
	 *  获取地区列表
	 */
	public function listQuery($parentId = 0){
		$parentId = ($parentId>0)?$parentId:(int)input('parentId');
		return $this->where(['isShow'=>1,'dataFlag'=>1,'parentId'=>$parentId])->field('areaId,areaName,parentId')->order('areaSort desc')->select();
	}
	/**
	 *  获取指定对象
	 */
    public function getById($id){
		return $this->where(["areaId"=>(int)$id])->find()->toArray();
	}
    /**
	 * 根据子分类获取其父级分类
	 */
	public function getParentIs($id,$data = array()){
		$data[] = $id;
		$parentId = $this->where('areaId',$id)->value('parentId');
		if($parentId==0){
			krsort($data);
			return $data;
		}else{
			return $this->getParentIs($parentId, $data);
		}
	}

	/**
	 * [getAddr 根据$area_id获取]
	 * @param  [type] $areaId [description]
	 * @return [type]         [description]
	 */
	public function getAddr($area_id)
	{
		// 如果有筛选地区 获取上级地区信息
		if ($area_id !== 0) {
		    $areaIds = $this->getParentIs($area_id);
		    /*
		    2 => int 440000
		    1 => int 440100
		    0 => int 440106
		     */
		    $selectArea = [];
		    $areaName   = '';
		    foreach ($areaIds as $k => $v) {
		        $a = $this->getById($v);
		        $areaName .= $a['areaName'];
		        $selectArea[] = $a;
		    }
		    // 地区完整名称
		    $selectArea['areaName'] = $areaName;
		    // 当前选择的地区
		    $data['areaInfo'] = $selectArea;

		    $data['area2'] = $this->listQuery($areaIds[2]); // 广东的下级

		    $data['area3'] = $this->listQuery($areaIds[1]); // 广州的下级
		}else{
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
		$ip = request()->ip();
		$ip = '221.239.19.1';
		$ip_info = (new Browser())->curl('http://int.dpool.sina.com.cn/iplookup/iplookup.php', 
			['format'=>'json','ip'=> $ip]
		);
		$ip_info = json_decode($ip_info, true);
		if (isset($ip_info['country']) && $ip_info['country'] == self::COUNTRY) {
			$province = $ip_info['province'] . self::PROVINCE_EXT;
			$city = $ip_info['city'] . self::CITY_EXT;
			$result = $this->getLocationByName($province, $city);
			if ($result) {
				$this->addLocationToSession($result);
				return explode('_',$result['city'])[1];
			}
		} 
		//地址未知，没查出来或者在国外
		return lang('UNKNOWN');
		
	}

	/**
	 * [getLocationByName ]
	 * @param  [type] $province [省名]
	 * @param  [type] $city     [市名]
	 * @return [type]           [description]
	 */
	private function getLocationByName($province, $city)
	{

		$result = $this->field('areaId, areaName, areaType')
		->where([
			'areaName' => ['IN',[$province, $city]],
			'dataFlag' => CODE_SUCCESS
		])->limit(2)->select();
		if ($result) {
			foreach ($result as $key => $res) {
				if ($res['areaType'] == self::CODE_PROVINCE) {
					$data['province'] = $res['areaId'].'_'.$res['areaName'];
				} else {
					$data['city'] = $res['areaId'].'_'.$res['areaName'];
				}
				
			}
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
		return Session::set('user_location',$location);
	}
}
