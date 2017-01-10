<?php
namespace fi\admin\model;
/**
 * 图片空间业务处理
 */
use think\Db;
class Images extends Base{
	/**
	 * 获取图片空间概况
	 */
	public function summary(){
		$rs = Db::table('__IMAGES__')->where(['status'=>1])->field('from_table,is_use,sum(img_size) img_size')->group('from_table,is_use')
		        ->order('from_table asc')->select();
		//获取目录名称
		$rs2 = Db::table('__DATAS__')->where(['cat_id'=>3])->field('data_name,data_val')->select();
		$imagesMap = [];
		foreach ($rs2 as $key =>$v){
			$imagesMap[$v['data_val']] = $v['data_name'];
		}
	    $images = [];
		foreach ($rs as $key =>$v){
			if(!isset($images[$v['from_table']]))$images[$v['from_table']] = ['directory'=>'','data'=>['0'=>0,'1'=>0]];
			if(isset($imagesMap[$v['from_table']]))$images[$v['from_table']]['directory'] = $imagesMap[$v['from_table']];
		    $images[$v['from_table']]['data'][$v['is_use']] = round($v['img_size']/1024/1024,2);
		}
		$maxSize = 0;
		foreach ($images as $key =>$v){
			$size = (float)$v['data']['0']+(float)$v['data']['1'];
			if($maxSize<$size)$maxSize = $size;
		}
		$images['_FISummary_'] = $maxSize;
		return $images;
	}
	/**
	 * 获取记录
	 */
	public function pageQuery(){
		$key = input('keyword');
		$is_use = (int)input('is_use');
		$where = ['from_table'=>$key,'a.status'=>1];
		if($is_use !=-1)$where['is_use'] = $is_use;
		$page = $this->alias('a')->join('__USERS__ u','a.own_id=u.user_id and from_type=0','left')
		            ->join('__SHOPS__ s','s.user_id=u.user_id','left')
		            ->join('__STAFFS__ sf','sf.staff_id=a.own_id','left')
		            ->where($where)->field('a.img_id,u.login_name,u.user_type,from_type,sf.login_name login_name2,s.shop_name,img_path,img_size,is_use,a.create_time')
		            ->order('a.img_id desc')->paginate(input('post.pagesize/d'))->toArray();
		foreach ($page['Rows'] as $key => $v){
			if($v['from_type']==1){
				$page['Rows'][$key]['login_name'] = $v['login_name2'];
			}
			$page['Rows'][$key]['img_size'] = round($v['img_size']/1024/1024,2);
			unset($page['Rows'][$key]['login_name2']);
		}
		return $page;
	}
	/**
	 * 删除图片
	 */
	public function del(){
		$id = (int)input('id');
		$image = $this->where('img_id',$id)->find();
		$rs = $this->where('img_id',$id)->update(['status'=>-1]);
		if(false !== $rs){
			$m = FIConf('CONF.fiMobileImgSuffix');
			$timg_path =  str_replace('.','_thumb.',$image['img_path']);
			$mimg_path =  str_replace('.',$m.'.',$image['img_path']);
	        $mtimg_path = str_replace('.',$m.'_thumb.',$image['img_path']);
	        
			if(file_exists(FIRootPath()."/".$image['img_path']))unlink(FIRootPath()."/".$image['img_path']); 
			if(file_exists(FIRootPath()."/".$timg_path))unlink(FIRootPath()."/".$timg_path); 
			if(file_exists(FIRootPath()."/".$mimg_path))unlink(FIRootPath()."/".$mimg_path); 
			if(file_exists(FIRootPath()."/".$mtimg_path))unlink(FIRootPath()."/".$mtimg_path); 
			return FIReturn("删除成功", 1);
		}
		return FIReturn("删除失败");
	}
}
