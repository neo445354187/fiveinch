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
		$where['a.dataFlag'] = 1;
		if($key!='')$where['a.articleTitle'] = ['like','%'.$key.'%'];
		$page = Db::table('__ARTICLES__')->alias('a')
		->join('__ARTICLE_CATS__ ac','a.catId= ac.catId','left')
		->join('__STAFFS__ s','a.staffId= s.staffId','left')
		->where($where)
		->field('a.articleId,a.catId,a.articleTitle,a.isShow,a.articleContent,a.articleKey,a.createTime,ac.catName,s.staffName')
		->order('a.articleId', 'desc')
		->paginate(input('post.pagesize/d'))->toArray();
		if(count($page['Rows'])>0){
			foreach ($page['Rows'] as $key => $v){
				$page['Rows'][$key]['articleContent'] = strip_tags(htmlspecialchars_decode($v['articleContent']));
			}
		}
		return $page;
	}
	
	/**
	 * 显示是否显示/隐藏
	 */
	public function editiIsShow(){
		$id = input('post.id/d');
		$isShow = input('post.isShow/d')?0:1;
		$result = $this->where(['articleId'=>$id])->update(['isShow' => $isShow]);
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
		$single = $this->where(['articleId'=>$id,'dataFlag'=>1])->find();
		$singlec = Db::table('__ARTICLE_CATS__')->where(['catId'=>$single['catId'],'dataFlag'=>1])->field('catName')->find();
		$single['catName']=$singlec['catName'];
		return $single;
	}
	
	/**
	 * 新增
	 */
	public function add(){
		$data = input('post.');
		FIUnset($data,'articleId,dataFlag');
		$data["staffId"] = (int)session('FI_STAFF.staffId');
		$data['createTime'] = date('Y-m-d H:i:s');
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
		$articleId = input('post.id/d');
		$data = input('post.');
		FIUnset($data,'articleId,dataFlag,createTime');
		$data["staffId"] = (int)session('FI_STAFF.staffId');
		$result = $this->validate('Articles.edit')->allowField(true)->save($data,['articleId'=>$articleId]);
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
		$data['dataFlag'] = -1;
		$result = $this->where(['articleId'=>$id])->update($data);
		if(false !== $result){
			return FIReturn("删除成功", 1);
		}else{
			return FIReturn($this->getError(),-1);
		}
	}
}