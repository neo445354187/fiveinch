<?php
namespace fi\admin\model;
use think\Db;
use think\Loader;
/**
 * 店铺业务处理
 */
class Shops extends Base{
	/**
	 * 分页
	 */
	public function pageQuery($shop_status=1){
		return Db::table('__SHOPS__')->alias('s')->join('__AREAS__ a2','s.area_id=a2.area_id','left')
		       ->where(['s.status'=>1,'s.shop_status'=>$shop_status])
		       ->field('shop_id,shop_sn,shop_name,a2.area_name,shop_keeper,telephone,shop_address,shop_company,shop_ative,shop_status')
		       ->order('shop_id desc')->paginate(input('pagesize/d'));
	}
	/**
	 * 删除
	 */
    public function del(){
	    $id = input('post.id/d');
		$data = [];
		$data['status'] = -1;
	    $result = $this->update($data,['shop_id'=>$id]);
	    FIUnuseImage('shops','shop_img',$id);
        if(false !== $result){
        	return FIReturn("删除成功", 1);
        }else{
        	return FIReturn($this->getError(),-1);
        }
	}
	
	/**
	 * 获取店铺信息
	 */
	public function getById($id){
		$shop = $this->get(['status'=>1,'shop_id'=>$id])->toArray();
		//获取经营范围
		$goodscats = Db::name('cat_shops')->where('shop_id',$id)->select();
		$shop['catshops'] = [];
		foreach ($goodscats as $v){
			$shop['catshops'][$v['cat_id']] = true;
		}
		//获取认证类型
	    $shopAccreds = Db::name('shop_accreds')->where('shop_id',$id)->select();
	    $shop['accreds'] = [];
		foreach ($shopAccreds as $v){
			$shop['accreds'][$v['accred_id']] = true;
		}
		return $shop;
	}
	/**
	 * 生成店铺编号
	 * @param $key 编号前缀,要控制不要超过int总长度，最好是一两个字母
	 */
	public function getShopSn($key = ''){
		$rs = $this->Max("REPLACE(shop_sn,'S','')+''");
		if($rs==''){
			return $key.'000000001';
		}else{
			for($i=0;$i<1000;$i++){
			   $num = (int)str_replace($key,'',$rs);
			   $shop_sn = $key.sprintf("%09d",($num+1));
			   $ischeck = $this->checkShopSn($shop_sn);
			   if(!$ischeck)return $shop_sn;
			}
			return '';//一直都检测到那就不要强行添加了
		}
	}
	
	/**
	 * 检测店铺编号是否存在
	 */
	public function checkShopSn($shop_sn,$shop_id=0){
		$dbo = $this->where(['shop_sn'=>$shop_sn,'status'=>1]);
		if($shop_id>0)$dbo->where('shop_id','<>',$shop_id);
		$num = $dbo->Count();
		if($num==0)return false;
		return true;
	}
	

	/**
	 * 新增
	 */
	public function add(){
		//判断是不是从开店申请里过来的，还要检测申请人身份[会员/游客]
		$apply_id = input('post.apply_id/d');
		$user_id = 0;
		if($apply_id>0){
			$applys = model('ShopApplys')->checkOpenShop($apply_id);
			$user_id = (int)$applys['user_id'];
		}
		//如果是游客的话就要检测一下账号是否存在
		if($user_id==0){
			$user = [];
			$user['login_name'] = Input('post.login_name');
			$user['login_password'] = Input('post.login_password');
			$ck = FICheckLoginKey($user['login_name']);
			if($ck['status']!=1)return $ck;
			if($user['login_password']=='')$user['login_password'] = '88888888';
			$user["login_secret"] = rand(1000,9999);
	    	$user['login_password'] = md5($user['login_password'].$user['login_secret']);
	    	$user["user_type"] = 1;
	    	$user['create_time'] = date('Y-m-d H:i:s');
		}
    	$validate = Loader::validate('Shops');
        if(!$validate->check(Input('post.')))return FIReturn($validate->getError());
        //判断经营范围
        $goods_cat_ids = Input('post.goods_cat_ids');
        $accred_ids = Input('post.accred_ids');
        if($goods_cat_ids=='')return FIReturn('请选择经营范围');
        Db::startTrans();
        try{
        	//如果是游客的话就先新增会员资料
        	if($user_id==0){
	            model('users')->save($user);
	            $user_id = model('users')->user_id;
        	}else{
        		model('users')->where('user_id',$user_id)->update(['user_type'=>1]);
        	}
	        $data = Input('post.');
	        $data['create_time'] = date('Y-m-d H:i:s');
	        //获取地区
	        $area_ids = model('Areas')->getParentIs($data['area_id']);
		    if(!empty($area_ids))$data['area_id_path'] = implode('_',$area_ids)."_";
	        FIUnset($data,'shop_id,status,is_self');
	        if($data['shop_sn']=='')$data['shop_sn'] = $this->getShopSn('S');
	        $data['user_id'] = $user_id;
	        $shop_id = 0;
	        if($user_id>0){
	        	$this->allowField(true)->save($data);
	        	$shop_id = $this->shop_id;
	        	//启用上传图片
				FIUseImages(1, $shop_id, $data['shop_img']);
	        	//建立店铺配置信息
	        	$sc = [];
	        	$sc['shop_id'] = $shop_id;
	        	Db::name('ShopConfigs')->insert($sc);
	        	//建立店铺评分记录
				$ss = [];
				$ss['shop_id'] = $shop_id;
				Db::name('shop_scores')->insert($ss);
	        	if(Input('post.apply_id/d')>0)model('ShopApplys')->editApplyOpenStatus(Input('post.apply_id/d'),$shop_id);
		        //经营范围
		        $goodsCats = explode(',',$goods_cat_ids);
		        foreach ($goodsCats as $v){
		        	if((int)$v>0)Db::name('cat_shops')->insert(['shop_id'=>$shop_id,'cat_id'=>$v]);
		        }
		        //认证类型
	            if($accred_ids!=''){
	                $accreds = explode(',',$accred_ids);
		            foreach ($accreds as $v){
			        	if((int)$v>0)Db::name('shop_accreds')->insert(['shop_id'=>$shop_id,'accred_id'=>$v]);
			        }
	            }
	        }
	        Db::commit();
	        return FIReturn("新增成功", 1);
        }catch (\Exception $e) {
            Db::rollback();
            return FIReturn('新增失败',-1);
        }
	}
    /**
	 * 编辑
	 */
	public function edit(){
		$shop_id = input('post.shop_id/d',0);
		$validate = Loader::validate('Shops');
        if(!$validate->check(Input('post.')))return FIReturn($validate->getError());
        //判断经营范围
        $goods_cat_ids = input('post.goods_cat_ids');
        $accred_ids = input('post.accred_ids');
        if($goods_cat_ids=='')return FIReturn('请选择经营范围');
        Db::startTrans();
        try{
	        $data = input('post.');
	        //获取地区
	        $area_ids = model('Areas')->getParentIs($data['area_id']);
		    if(!empty($area_ids))$data['area_id_path'] = implode('_',$area_ids)."_";
	        FIUnset($data,'shop_id,user_id,status,create_time,apply_id,goods_cat_ids,accred_ids,is_self');
	        //启用上传图片
			FIUseImages(1, $shop_id, $data['shop_img'],'shops','shop_img');
	        $this->allowField(true)->save($data,['shop_id'=>$shop_id,'status'=>1]);
		    //经营范围
		    Db::name('cat_shops')->where('shop_id','=',$shop_id)->delete();
		    $goodsCats = explode(',',$goods_cat_ids);
		    foreach ($goodsCats as $key =>$v){
		        if((int)$v>0){
		        	Db::name('cat_shops')->insert(['shop_id'=>$shop_id,'cat_id'=>$v]);
		        }
		    }
		    //认证类型
		    Db::name('shop_accreds')->where('shop_id','=',$shop_id)->delete();
	        if($accred_ids!=''){
	            $accreds = explode(',',$accred_ids);
		        foreach ($accreds as $key =>$v){
			        if((int)$v>0){
			        	Db::name('shop_accreds')->insert(['shop_id'=>$shop_id,'accred_id'=>$v]);
			        }
			    }
	        }
	        Db::commit();
	        return FIReturn("编辑成功", 1);
        }catch (\Exception $e) {
            Db::rollback();
            print_r($e);
            return FIReturn('编辑失败',-1);
        }
	}
	/**
	* 获取所有店铺id
	*/
	public function getAllShopId(){
		return $this->where(['status'=>1,'shop_status'=>1])->column('shop_id');
	}
	
	/**
	 * 搜索经验范围的店铺
	 */
	public function searchQuery(){
		$goodsCatatId = (int)input('post.goods_cat_id');
		if($goodsCatatId<=0)return [];
		$key = input('post.key');
		$where = [];
		$where['status'] = 1;
		$where['shop_status'] = 1;
		$where['cat_id'] = $goodsCatatId;
		if($key!='')$where['shopsName|shop_sn'] = ['like','%'.$key.'%'];
		return $this->alias('s')->join('__CAT_SHOPS__ cs','s.shop_id=cs.shop_id','inner')
		            ->where($where)->field('shop_name,s.shop_id,shop_sn')->select();
	}
	
    /**
	 * 自营自动登录
	 */
	public function selfLogin($id){
		$shop_id = $id;
		$userid = $this->where(["status"=>1, "shop_status"=>1,"shop_id"=>$shop_id])->field('user_id')->find();
		if(!empty($userid['user_id'])){
			$user_id = $userid['user_id'];
			//获取用户信息
			$u = new Users();
			$rs = $u->getById($user_id);
			//获取用户等级
			$rrs = Db::table('__USER_RANKS__')->where('start_score','<=',$rs['user_total_score'])->where('end_score','>=',$rs['user_total_score'])->field('rank_id,rank_name,rebate,userrank_img')->find();
			$rs['rank_id'] = $rrs['rank_id'];
			$rs['rank_name'] = $rrs['rank_name'];
			$rs['userrank_img'] = $rrs['userrank_img'];
			$ip = request()->ip();
			$u->where(["user_id"=>$user_id])->update(["last_time"=>date('Y-m-d H:i:s'),"last_ip"=>$ip]);
			//加载店铺信息
			$shops= new Shops();
			$shop = $shops->where(["user_id"=>$user_id,"status" =>1])->find();
			if(!empty($shop))$rs = array_merge($shop->toArray(),$rs->toArray());
			//记录登录日志
			$data = array();
			$data["user_id"] = $user_id;
			$data["login_time"] = date('Y-m-d H:i:s');
			$data["login_ip"] = $ip;
			Db::table('__LOG_USER_LOGINS__')->insert($data);
			session('FI_USER',$rs);
			return FIReturn("","1");
		}
		return FIReturn("",-1);
	}
	
}
