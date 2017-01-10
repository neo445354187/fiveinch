<?php
namespace fi\admin\model;
use think\Db;
/**
 * 前台菜单业务处理
 */
class HomeMenus extends Base{	
	protected $insert = ['status'=>1]; 
	
	/**
	 * 获取菜单
	 */
	public function getById($id){
		return $this->get(['status'=>1,'menu_id'=>$id]);
	}
	
	/**
	 * 新增菜单
	 */
	public function add(){
		$data = input('post.');
		$data['create_time'] = date('Y-m-d H:i:s');
		$data["status"] = 1;
		$result = $this->validate('HomeMenus.add')->allowField(true)->save($data);
        if(false !== $result){
        	return FIReturn("新增成功", 1);
        }else{
        	return FIReturn($this->getError(),-1);
        }
	}
    /**
	 * 编辑菜单
	 */
	public function edit(){
		$menu_id = input('post.menu_id/d',0);
	    $result = $this->validate('HomeMenus.edit')->allowField(['menu_name','menu_sort','menu_type','is_show','menu_url','menu_other_url'])->save(input('post.'),['menu_id'=>$menu_id]);
        if(false !== $result){
        	return FIReturn("编辑成功", 1);
        }else{
        	return FIReturn($this->getError(),-1);
        }
	}
	/**
	 * 删除菜单
	 */
	public function del(){
	    $menu_id = input('post.menu_id/d',0);
		$data = [];
		$data['status'] = -1;
	    $result = $this->update($data,['menu_id'=>$menu_id]);
	    $this->update($data,['parent_id'=>$menu_id]);
        if(false !== $result){
        	return FIReturn("删除成功", 1);
        }else{
        	return FIReturn($this->getError(),-1);
        }
	}
	
	/**
	 * 分页
	 */
	public function pageQuery(){
		$menu_type = (int)input('menu_type',-1);
		$where = [];
		$where['a.status'] = 1;
		if($menu_type!=-1)$where['a.menu_type'] = $menu_type;
		$rs = $this->alias('a')->join('__HOME_MENUS__ b','a.parent_id = b.menu_id','left')
			->field("a.menu_id, a.parent_id, a.menu_name, a.menu_url, a.menu_other_url, a.menu_type, a.is_show, a.menu_sort, b.menu_name parentName")
			->where($where)
			->order('a.menu_type asc,a.menu_sort asc')
			->paginate(input('pagesize/d',1));
		return $rs;
	}
	
	/**
	 * 显示隐藏
	 */
	public function setToggle(){
		$menu_id = input('post.menu_id',0);
		$is_show = input('post.is_show/d');
		$result = $this->where(['menu_id'=>$menu_id,"status"=>1])->setField("is_show", $is_show);
		if(false !== $result){
			return FIReturn("设置成功", 1);
		}else{
			return FIReturn($this->getError(),-1);
		}
	}
	
	/**
	 * 获取菜单列表
	 */
	public function getMenus($parent_id = -1){
		$rs = $this->where(['parent_id'=>$parent_id,'status'=>1])->field('menu_id, parent_id, menu_name, menu_url,menu_other_url')->order('menu_sort', 'asc')->select();
		if(count($rs)>0){
			foreach ($rs as $key =>$v){
				$children = self::getMenus($rs[$key]['menu_id']);
				if(!empty($children)){
					$rs[$key]["children"] = $children;
				}
			}
		};
		return $rs;
	}
	
	/**
    * 修改排序
    */ 
    public function changeSort(){
    	$id = (int)input('id');
    	$menu_sort = (int)input('menu_sort');
        $rs = $this->where('menu_id',$id)->setField('menu_sort',$menu_sort);
        if($rs!==false){
        	return FIReturn('修改成功',1);
        }
        return FIReturn('修改失败',-1);
    }
}
