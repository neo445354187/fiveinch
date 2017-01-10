<?php
namespace fi\admin\model;
/**
 * 文章分类业务处理
 */
use think\Db;
class ArticleCats extends Base{
	/**
	 * 获取树形分类
	 */
	public function pageQuery(){
		$parent_id = input('cat_id/d',0);
		$data = $this->where(['status'=>1,'parent_id'=>$parent_id])->order('cat_id desc')->paginate(input('post.pagesize/d'))->toArray();
		return $data;
	}
	/**
	 * 获取列表
	 */
	public function listQuery($parent_id){
		$rs = $this->where(['status'=>1,'parent_id'=>$parent_id])->order('cat_sort asc,cat_name asc')->select();
		if(count($rs)>0){
			foreach ($rs as $key => $v){
				$rs[$key]['childrenurl'] = url('admin/articlecats/listQuery',array('parent_id'=>$v['cat_id']));
				$rs[$key]['children'] = [];
				$rs[$key]['isextend'] = false;
			}
		}
		return $rs;
	}
	/**
	 * 获取指定对象
	 */
	public function getById($id){
		return $this->get(['status'=>1,'cat_id'=>$id]);
	}
	
	/**
	 *  获取文章分类列表
	 */
	public function listQuery2(){
		return $this->where(['status'=>1,'is_show'=>1])->field('cat_id,cat_name,parent_id')->order('cat_sort desc')->select();
	}
	
	/**
	 * 显示是否显示/隐藏
	 */
	public function editiIsShow(){
		$ids = array();
		$id = input('post.id/d');
		$ids = $this->getChild($id);
		$is_show = input('post.is_show/d')?1:0;
		$result = $this->where("cat_id in(".implode(',',$ids).")")->update(['is_show' => $is_show]);
		if(false !== $result){
			return FIReturn("操作成功", 1);
		}else{
			return FIReturn($this->getError(),-1);
		}
	}
	
	/**
	 * 迭代获取下级
	 * 获取一个分类下的所有子级分类id
	 */
	public function getChild($pid=1){
		$data = $this->where("status=1")->select();
		//获取该分类id下的所有子级分类id
		$ids = $this->_getChild($data, $pid, true);//每次调用都清空一次数组
		//把自己也放进来
		array_unshift($ids, $pid);
		return $ids;
	}
	public function _getChild($data, $pid, $isClear=false){
		static $ids = array();
		if($isClear)//是否清空数组
			$ids = array();
		foreach($data as $k=>$v)
		{
			if($v['parent_id']==$pid && $v['status']==1)
			{
				$ids[] = $v['cat_id'];//将找到的下级分类id放入静态数组
				//再找下当前id是否还存在下级id
				$this->_getChild($data, $v['cat_id']);
			}
		}
		return $ids;
	}
	
	/**
	 * 新增
	 */
	public function add(){
		$parent_id = input('post.parent_id/d');
		$data = input('post.');
		FIUnset($data,'cat_id,cat_type,status');
		$data['parent_id'] = $parent_id;
		$data['create_time'] = date('Y-m-d H:i:s');
		$result = $this->validate('ArticleCats.add')->allowField(true)->save($data);
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
		$cat_id = input('post.id/d');
		$result = $this->validate('ArticleCats.edit')->allowField(['cat_name','is_show','cat_sort'])->save(input('post.'),['cat_id'=>$cat_id]);
		$ids = array();
		$ids = $this->getChild($cat_id);
		$this->where("cat_id in(".implode(',',$ids).")")->update(['is_show' => input('post.')['is_show']]);
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
		$ids = array();
		$id = input('post.id/d');
		$ids = $this->getChild($id);
		$data = [];
		$data['status'] = -1;
		$rs = $this->getById($id);
		if($rs['cat_type']==1){
			return FIReturn("不能删除该分类", -1);
		}else{
			Db::startTrans();
            try{
				$result = $this->where("cat_id in(".implode(',',$ids).")")->update($data);
				if(false !==$result){
					Db::table('__ARTICLES__')->where(['cat_id'=>['in',$ids]])->update(['status'=>-1]);
				}
				Db::commit();
	            return FIReturn("删除成功", 1);
            }catch (\Exception $e) {
                Db::rollback();
                return FIReturn('删除失败',-1);
            }
		}
	}
}