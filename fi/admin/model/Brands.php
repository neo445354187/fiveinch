<?php
namespace fi\admin\model;
use think\Db;
/**
 * 品牌业务处理
 */
class Brands extends Base{
	/**
	 * 分页
	 */
	public function pageQuery(){
		$key = input('get.key');
		$id = input('get.id/d');
		$where = [];
		$where['b.status'] = 1;
		if($key!='')$where['b.brand_name'] = ['like','%'.$key.'%'];
		if($id>0)$where['gcb.cat_id'] = $id;
		$total = Db::table('__BRANDS__')->alias('b');
		if($id>0){ 
		    $total->join('__CAT_BRANDS__ gcb','b.brand_id = gcb.brand_id','left');
		}
		$page = $total->where($where)
		->field('b.brand_id,b.brand_name,b.brand_img,b.brand_desc')
		->order('b.brand_id', 'desc')
		->paginate(input('post.pagesize/d'))->toArray();
		if(count($page['Rows'])>0){
			foreach ($page['Rows'] as $key => $v){
				$page['Rows'][$key]['brand_desc'] = strip_tags(htmlspecialchars_decode($v['brand_desc']));
			}
		}
		return $page;
	}	
	
	/**
	 * 获取指定对象
	 */
	public function getById($id){
		$result = $this->where(['brand_id'=>$id])->find();
		//获取关联的分类
		$result['cat_ids'] = Db::table('__CAT_BRANDS__')->where(['brand_id'=>$id])->column('cat_id');
		return $result;
	}
	
	/**
	 * 新增
	 */
	public function add(){
		$data = input('post.');
		FIUnset($data,'brand_id,status');
		$data['create_time'] = date('Y-m-d H:i:s');
		$idsStr = explode(',',$data['cat_id']);
		if($idsStr!=''){
			foreach ($idsStr as $v){
				if((int)$v>0)$ids[] = (int)$v;
			}
		}
		Db::startTrans();
        try{
			$result = $this->validate('Brands.add')->allowField(true)->save($data);
			if(false !== $result){
				//启用上传图片
			    FIUseImages(1, $this->brand_id, $data['brand_img']);
				//商品描述图片
				FIEditorImageRocord(1, $this->brand_id, '',$data['brand_desc']);
				foreach ($ids as $key =>$v){
					$d = array();
					$d['cat_id'] = $v;
					$d['brand_id'] = $this->brand_id;
					Db::table('__CAT_BRANDS__')->insert($d);
				}
				Db::commit();
				return FIReturn("新增成功", 1);
			}
        }catch (\Exception $e) {
            Db::rollback();
        }
        return FIReturn('新增失败',-1);
	}
	
	/**
	 * 编辑
	 */
	public function edit(){
		$brand_id = input('post.id/d');
		$data = input('post.');
		$idsStr = explode(',',$data['cat_id']);
		if($idsStr!=''){
			foreach ($idsStr as $v){
				if((int)$v>0)$ids[] = (int)$v;
			}
		}
		$filter = array();
		//获取品牌的关联分类
		$catBrands = Db::table('__CAT_BRANDS__')->where(['brand_id'=>$brand_id])->select();
		foreach ($catBrands as $key =>$v){
			if(!in_array($v['cat_id'],$ids))$filter[] = $v['cat_id'];
		}
		Db::startTrans();
        try{
			FIUseImages(1, $brand_id, $data['brand_img'], 'brands', 'brand_img');
			// 品牌描述图片
			$desc = $this->where('brand_id',$brand_id)->value('brand_desc');
			FIEditorImageRocord(1, $brand_id, $desc, $data['brand_desc']);
			$result = $this->validate('Brands.edit')->allowField(['brand_name','brand_img','brand_desc'])->save(input('post.'),['brand_id'=>$brand_id]);
			if(false !== $result){
				foreach ($catBrands as $key =>$v){
					Db::table('__CAT_BRANDS__')->where('brand_id',$brand_id)->delete();
				}
				foreach ($ids as $key =>$v){
					$d = array();
					$d['cat_id'] = $v;
					$d['brand_id'] = $brand_id;
					Db::table('__CAT_BRANDS__')->insert($d);
				}
				Db::commit();
				return FIReturn("修改成功", 1);
			}
        }catch (\Exception $e) {
            Db::rollback();
        }
        return FIReturn('修改失败',-1);
	}
	
	/**
	 * 删除
	 */
	public function del(){
		$id = input('post.id/d');
		$data = [];
		$data['status'] = -1;
		Db::startTrans();
        try{
			$result = $this->where(['brand_id'=>$id])->update($data);
		    FIUnuseImage('brands','brand_img',$id);
			// 品牌描述图片
			$desc = $this->where('brand_id',$id)->value('brand_desc');
			FIEditorImageRocord(1, $id, $desc,'');
			if(false !== $result){
				Db::commit();
				return FIReturn("删除成功", 1);
			}
        }catch (\Exception $e) {
            Db::rollback();
        }
        return FIReturn('删除失败',-1);
	}
	
	/**
	 * 获取品牌
	 */
	public function searchBrands(){
		$goodsCatatId = (int)input('post.goods_cat_id');
		if($goodsCatatId<=0)return [];
		$key = input('post.key');
		$where = [];
		$where['status'] = 1;
		$where['cat_id'] = $goodsCatatId;
		if($key!='')$where['brandsName'] = ['like','%'.$key.'%'];
		return $this->alias('s')->join('__CAT_BRANDS__ cb','s.brand_id=cb.brand_id','inner')
		            ->where($where)->field('brand_name,s.brand_id')->select();
	}
}