<?php
namespace fi\admin\model;
/**
 * 商品评价业务处理
 */
class GoodsAppraises extends Base{
	/**
	 * 分页
	 */
	public function pageQuery(){
		$where = 'p.shop_id=g.shop_id and gp.goods_id=g.goods_id and o.order_id=gp.order_id';
		$shop_name = input('shop_name');
     	$goods_name = input('goods_name');

	 	$area_id1 = (int)input('area_id1');
		if($area_id1>0){
			$where.=" and p.area_id_path like '".$area_id1."%'";

			$area_id2 = (int)input("area_id1_".$area_id1);
			if($area_id2>0)
				$where.=" and p.area_id_path like '".$area_id1."_".$area_id2."%'";

			$area_id3 = (int)input("area_id1_".$area_id1."_".$area_id2);
			if($area_id3>0)
				$where.=" and p.area_id = $area_id3";
		}


	 	if($shop_name!='')
	 		$where.=" and (p.shop_name like '%".$shop_name."%' or p.shop_sn like '%'".$shop_name."%')";
	 	if($goods_name!='')
	 		$where.=" and (g.goods_name like '%".$goods_name."%' or g.goods_sn like '%".$goods_name."%')";

		$rs = $this->alias('gp')->field('gp.*,g.goods_name,g.goods_img,o.order_no,u.login_name')
					->join('__GOODS__ g ','gp.goods_id=g.goods_id','left') 
		         	->join('__ORDERS__ o','gp.order_id=o.order_id','left')
		         	->join('__USERS__ u','u.user_id=gp.user_id','left')
		         	->join('__SHOPS__ p','p.shop_id=gp.shop_id','left')
		         	->where($where)
		         	->order('id desc')
		         	->paginate(input('pagesize/d'))->toArray();
		return $rs;
	}
	public function getById($id){
		return $this->alias('gp')->field('gp.*,o.order_no,u.login_name,g.goods_name,g.goods_img')
					->join('__GOODS__ g ','gp.goods_id=g.goods_id','left') 
		         	->join('__ORDERS__ o','gp.order_id=o.order_id','left')
		         	->join('__USERS__ u','u.user_id=gp.user_id','left')
		         	->where('gp.id',$id)->find();
	}
    /**
	 * 编辑
	 */
	public function edit(){
		$Id = input('post.id/d',0);
		$data = input('post.');
		FIUnset($data,'create_time');
	    $result = $this->validate('GoodsAppraises.edit')->allowField(true)->save($data,['id'=>$Id]);
        if(false !== $result){
        	return FIReturn("编辑成功", 1);
        }else{
        	return FIReturn($this->getError(),-1);
        }
	}
	/**
	 * 删除
	 */
    public function del(){
	    $id = input('post.id/d',0);
		$data = [];
		$data['status'] = -1;
	    $result = $this->update($data,['id'=>$id]);
        if(false !== $result){
        	return FIReturn("删除成功", 1);
        }else{
        	return FIReturn($this->getError(),-1);
        }
	}
	
}
