<?php
namespace fi\admin\model;
use think\Db;
/**
 * 文章业务处理
 */
class Articles extends Base{
	/**
	 * 分页
	 */
	public function pageQuery(){
		$key = input('get.key');
		$where = [];
		$where['a.status'] = 1;
		if($key!='')$where['a.article_title'] = ['like','%'.$key.'%'];
		$page = Db::table('__ARTICLES__')->alias('a')
		->join('__ARTICLE_CATS__ ac','a.cat_id= ac.cat_id','left')
		->join('__STAFFS__ s','a.staff_id= s.staff_id','left')
		->where($where)
		->field('a.article_id,a.cat_id,a.article_title,a.is_show,a.article_content,a.article_key,a.create_time,ac.cat_name,s.staff_name')
		->order('a.article_id', 'desc')
		->paginate(input('post.pagesize/d'))->toArray();
		if(count($page['Rows'])>0){
			foreach ($page['Rows'] as $key => $v){
				$page['Rows'][$key]['article_content'] = strip_tags(htmlspecialchars_decode($v['article_content']));
			}
		}
		return $page;
	}
	
	/**
	 * 显示是否显示/隐藏
	 */
	public function editiIsShow(){
		$id = input('post.id/d');
		$is_show = input('post.is_show/d')?0:1;
		$result = $this->where(['article_id'=>$id])->update(['is_show' => $is_show]);
		if(false !== $result){
			return FIReturn("操作成功", 1);
		}else{
			return FIReturn($this->getError(),-1);
		}
	}
	
	/**
	 * 获取指定对象
	 */
	public function getById($id){
		$single = $this->where(['article_id'=>$id,'status'=>1])->find();
		$singlec = Db::table('__ARTICLE_CATS__')->where(['cat_id'=>$single['cat_id'],'status'=>1])->field('cat_name')->find();
		$single['cat_name']=$singlec['cat_name'];
		return $single;
	}
	
	/**
	 * 新增
	 */
	public function add(){
		$data = input('post.');
		FIUnset($data,'article_id,status');
		$data["staff_id"] = (int)session('FI_STAFF.staff_id');
		$data['create_time'] = date('Y-m-d H:i:s');
		$result = $this->validate('Articles.add')->allowField(true)->save($data);
		if(false !== $result){
			return FIReturn("新增成功", 1);
		}else{
			return FIReturn($this->getError(),-1);
		}
	}
	
	/**
	 * 编辑
	 */
	public function edit(){
		$article_id = input('post.id/d');
		$data = input('post.');
		FIUnset($data,'article_id,status,create_time');
		$data["staff_id"] = (int)session('FI_STAFF.staff_id');
		$result = $this->validate('Articles.edit')->allowField(true)->save($data,['article_id'=>$article_id]);
		if(false !== $result){
			return FIReturn("修改成功", 1);
		}else{
			return FIReturn($this->getError(),-1);
		}
	}
	
	/**
	 * 删除
	 */
	public function del(){
		$id = input('post.id/d');
		$data = [];
		$data['status'] = -1;
		$result = $this->where(['article_id'=>$id])->update($data);
		if(false !== $result){
			return FIReturn("删除成功", 1);
		}else{
			return FIReturn($this->getError(),-1);
		}
	}
}