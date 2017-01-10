<?php
namespace fi\home\model;
/**
 * 文章类
 */
use think\Db;
class Articles extends Base{
	/**
	 * 获取帮助左侧列表
	 */
	public function helpList(){
		$arts = cache('arts');
		if(!$arts){
			$rs = $this->alias('a')->join('__ARTICLE_CATS__ ac','a.cat_id=ac.cat_id','inner')
				  ->field('a.article_id,a.cat_id,a.article_title,ac.cat_name')
				  ->where(['a.status'=>1,
				  		   'ac.status'=>1,
				  		   'ac.is_show'=>1,
				  		   'ac.parent_id'=>7])
				  ->cache(true)
				  ->select();
			//同一分类下的文章放一起
			$cat_name = [];
			$arts = [];
			foreach($rs as $k=>$v){
				if(in_array($v['cat_name'],$cat_name)){
					$arts[$v['cat_name'].'-'.$v['cat_id']][] = $v;
				}else{
					$cat_name[] = $v['cat_name'];
					$arts[$v['cat_name'].'-'.$v['cat_id']][] = $v;
				}
			}
			cache('arts',$arts,86400);
		}
		return $arts;
	}
	/**
	*  根据id获取帮助文章
	*/
	public function getHelpById(){
		$id = (int)input('id');
		return $this->alias('a')->join('__ARTICLE_CATS__ ac','a.cat_id=ac.cat_id','inner')->where('ac.parent_id=7 and a.status=1')->cache(true)->find($id);
	}
	/**
	*  根据id获取资讯文章
	*/
	public function getNewsById(){
		$id = (int)input('id');
		return $this->alias('a')->join('__ARTICLE_CATS__ ac','a.cat_id=ac.cat_id','inner')->where('a.cat_id<>7 and ac.parent_id<>7 and a.status=1')->cache(true)->find($id);
	}

	/**
	* 获取资讯列表
	*/
	public function NewsList(){
		$list =  $this->getTree();
		foreach($list as $k=>$v){
			if(!empty($v['children'])){
				foreach($v['children'] as $k1=>$v1){
					// 二级分类下的文章总条数
					$list[$k]['children'][$k1]['newsCount'] = $this->where(['cat_id'=>$v1['cat_id'],
																	'status'=>1])->cache(true)->count();
				}
			}
		}
		return $list;
	}

	public function getTree(){
		$artTree = cache('artTree');
		if(!$artTree){
			$data = Db::table('__ARTICLE_CATS__')->field('cat_name,cat_id,parent_id')->where('parent_id <> 7 and cat_id <> 7 and status=1')->cache(true)->select();
			$artTree = $this->_getTree($data, 0);
			cache('artTree',$artTree,86400);
		}
		return $artTree;
	}
	public function _getTree($data,$parent_id){
		$tree = [];
		foreach($data as $k=>$v){
			if($v['parent_id']==$parent_id){
				// 再找其下级分类
				$v['children'] = $this->_getTree($data,$v['cat_id']);
				$tree[] = $v;
			}
		}
		return $tree;
	}
	/**
	*	根据分类id获取文章列表
	*/
	public function nList(){
		$cat_id = (int)input('cat_id');
		$rs = $this->alias('a')
			  ->join('__ARTICLE_CATS__ ac','a.cat_id=ac.cat_id','inner')
			  ->field('a.*')
			  ->where(['a.cat_id'=>$cat_id,
			  		   'ac.status'=>1,
			  		   'ac.is_show'=>1,
			  		   'ac.parent_id'=>['<>',7],
			  		   ])
			  ->cache(true)
			  ->paginate();
		return $rs;
	}
	/**
	* 面包屑导航
	*/
	public function bcNav(){
		$cat_id = (int)input('cat_id'); //分类id
		$artId = (int)input('id'); 	//文章id
		$data = Db::table('__ARTICLE_CATS__')->field('cat_id,parent_id,cat_name')->cache(true)->select();
		if($artId){
			$cat_id = $this->where('article_id',$artId)->value('cat_id');
		}
		$bcNav = $this->getParent($data,$cat_id,$isClear=true);
		return $bcNav;

	}
	/**
	* 获取父级分类
	*/
	public function getParent($data, $cat_id,$isClear=false){
		static $bcNav = [];
		if($isClear)
			$bcNav = [];
		foreach($data as $k=>$v){
			if($cat_id == $v['cat_id']){
				if($cat_id!=0){
					$this->getParent($data, $v['parent_id']);
					$bcNav[] = $v;
				}
			}
		}
		return $bcNav;
	}

	/**
	*  记录解决情况
	*/
	public function recordSolve(){
		$article_id =  (int)input('id');
		$status =  (int)input('status');
		if($status==1){
			$rs = $this->where('article_id',$article_id)->setInc('solve');
		}else{
			$rs = $this->where('article_id',$article_id)->setInc('unsolve');
		}
		if($rs!==false){
			return FIReturn('操作成功',1);
		}else{
			return FIReturn('操作失败',-1);
		}
	}

	/**
	* 获取资讯中心的子集分类id
	*/
	public function getChildIds(){
		$ids = [];
		$data = Db::table('__ARTICLE_CATS__')->cache(true)->select();
			foreach($data as $k=>$v){
				if($v['parent_id']!=7 && $v['cat_id']!=7 && $v['parent_id']!=0 ){
					$ids[] = $v['cat_id'];
				}
			}
		return $ids;
	}

	/**
	* 获取咨询中中心所有文章
	*/
	public function getArticles(){
		// 获取咨询中心下的所有分类id
		$ids = $this->getChildIds();
		$rs = $this->alias('a')
			  ->field('a.*')
			  ->join('__ARTICLE_CATS__ ac','a.cat_id=ac.cat_id','inner')
			  ->where(['a.cat_id'=>['in',$ids],
			  		   'ac.status'=>1,
			  		   'ac.is_show'=>1,
			  		   'ac.parent_id'=>['<>',7],
			  		   ])
			  ->distinct(true)
			  ->cache(true)
			  ->paginate(15);
		return $rs;

	}
}
