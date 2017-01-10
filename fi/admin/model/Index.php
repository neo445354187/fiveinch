<?php 
namespace fi\admin\model;
use think\Db;
/**
 * 系统业务处理
 */
class Index extends Base{
    /**
	 * 清除缓存
	 */
	public function clearCache(){
		$dirpath = dirname(FIRootPath())."/runtime/cache";
		$isEmpty = FIDelDir($dirpath);
		return $isEmpty;
	}
	/**
	 * 获取基础统计信息
	 */
	public function summary(){
		$data = [];
		//今日统计
		$data['tody'] = ['user_type0'=>0,'user_type1'=>0];
		$rs = Db::name('users')->where(['create_time'=>['like',date('Y-m-d').'%'],'status'=>1])->group('user_type')->field('user_type,count(user_id) counts')->select();
		$tmp = [];
		if(!empty($rs)){
			foreach ($rs as $key => $v){
				$tmp[$v['user_type']] = $v['counts'];
			}
		}
		if(isset($tmp['0']))$data['tody']['user_type0'] = $tmp['0'];
		if(isset($tmp['1']))$data['tody']['user_type1'] = $tmp['1'];
		$data['tody']['shopApplys'] = Db::name('shop_applys')->where(['create_time'=>['like',date('Y-m-d').'%'],'status'=>1])->count();
		$data['tody']['compalins'] = Db::name('order_complains')->where(['complain_time'=>['like',date('Y-m-d').'%']])->count();
		$data['tody']['saleGoods'] = Db::name('goods')->where(['status'=>1,'goods_status'=>1,'is_sale'=>1,'create_time'=>['like',date('Y-m-d').'%']])->count();
		$data['tody']['auditGoods'] = Db::name('goods')->where(['status'=>1,'goods_status'=>0,'is_sale'=>1,'create_time'=>['like',date('Y-m-d').'%']])->count();
		$data['tody']['order'] = Db::name('orders')->where(['status'=>1,'create_time'=>['like',date('Y-m-d').'%']])->count();
		//商城统计
		$data['mall'] = ['user_type0'=>1,'user_type1'=>0];
		$rs = Db::name('users')->where(['status'=>1])->group('user_type')->field('user_type,count(user_id) counts')->select();
		$tmp = [];
		if(!empty($rs)){
			foreach ($rs as $key => $v){
				$tmp[$v['user_type']] = $v['counts'];
			}
		}
		if(isset($tmp['0']))$data['mall']['user_type0'] = $tmp['0'];
		if(isset($tmp['1']))$data['mall']['user_type1'] = $tmp['1'];
		$data['mall']['saleGoods'] = Db::name('goods')->where(['status'=>1,'goods_status'=>1,'is_sale'=>1])->count();
		$data['mall']['auditGoods'] = Db::name('goods')->where(['status'=>1,'goods_status'=>0,'is_sale'=>1])->count();
		$data['mall']['order'] = Db::name('orders')->where(['status'=>1])->count();
		$data['mall']['brands'] = Db::name('brands')->where(['status'=>1])->count();
		$data['mall']['appraise'] = Db::name('goods_appraises')->where(['status'=>1])->count();
		$rs = Db::query('select VERSION() as sqlversion');
		$data['MySQL_Version'] = $rs[0]['sqlversion'];
		return $data;
	}
	
    /**
	 * 保存授权码
	 */
	public function saveLicense(){
		$data = [];
		$data['field_value'] = input('license');
	    $result = model('SysConfigs')->where('field_code','mallLicense')->update($data);
		if(false !== $result){
			cache('FI_CONF',null);
			return FIReturn("操作成功",1);
		}
		return FIReturn("操作失败");
	}
}