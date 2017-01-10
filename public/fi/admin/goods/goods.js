var grid;
function initSaleGrid(){
	grid = $("#maingrid").ligerGrid({
		url:FI.U('admin/goods/saleByPage'),
		pageSize:FI.pageSize,
		pageSizeOptions:FI.pageSizeOptions,
		height:'99%',
        width:'100%',
        rowHeight:65,
        minColToggle:6,
        rownumbers:true,
        columns: [
            { display: '&nbsp;', name: 'goods_name',width:60,align:'left',heightAlign:'left',isSort: false,render: function (rowdata, rowindex, value){
            	return "<img style='height:60px;width:60px;' src='"+FI.conf.ROOT+"/"+rowdata['goods_img']+"'>";
            }},
	        { display: '商品名称', name: 'goods_name',heightAlign:'left',isSort: false,render: function (rowdata, rowindex, value){
	            return rowdata['goods_name'];
	        }},
	        { display: '商品编号', name: 'goods_sn',isSort: false,render: function (rowdata, rowindex, value){
	        	return "<div class='goods-valign-m'>"+rowdata['goods_sn']+"</div>";
	        }},
	        { display: '价格', name: 'shop_price',isSort: false,render: function (rowdata, rowindex, value){
	        	return "<div class='goods-valign-m'>"+rowdata['shop_price']+"</div>";
	        }},
	        { display: '所属店铺', name: 'shop_name',isSort: false,render: function (rowdata, rowindex, value){
	        	return "<div class='goods-valign-m'>"+rowdata['shop_name']+"</div>";
	        }},
	        { display: '所属分类', name: 'goodsCatName',isSort: false,render: function (rowdata, rowindex, value){
	        	return "<div class='goods-valign-m'>"+rowdata['goodsCatName']+"</div>";
	        }},
	        { display: '销量', name: 'sale_num',isSort: false,render: function (rowdata, rowindex, value){
	        	return "<div class='goods-valign-m'>"+rowdata['sale_num']+"</div>";
	        }},
	        { display: '操作', name: 'op',isSort: false,render: function (rowdata, rowindex, value){
	            var h = "";
	            h += "<div class='goods-valign-m'><a target='_blank' href='"+FI.U("home/goods/detail","id="+rowdata['goods_id'])+"'>查看</a> ";
	            if(FI.GRANT.SJSP_04)h += "<a href='javascript:illegal(" + rowdata['goods_id'] + ")'>违规下架</a> ";
	            if(FI.GRANT.SJSP_03)h += "<a href='javascript:del(" + rowdata['goods_id'] + ",1)'>删除</a></div> "; 
	            return h;
	        }}
        ]
    });
}
function loadSaleGrid(){
	var params = FI.getParams('.j-ipt');
	params.area_id_path = FI.ITGetAllAreaVals('area_id1','j-areas').join('_');
	params.goods_cat_id_path = FI.ITGetAllGoodsCatVals('cat_0','pgoodsCats').join('_');
	grid.set('url',FI.U('admin/goods/saleByPage',params));
}

function del(id,type){
	var box = FI.confirm({content:"您确定要删除该商品吗?",yes:function(){
	           var loading = FI.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
	           $.post(FI.U('admin/goods/del'),{id:id},function(data,textStatus){
	           			layer.close(loading);
	           			var json = FI.toAdminJson(data);
	           			if(json.status=='1'){
	           			    FI.msg(json.msg,{icon:1});
	           			    layer.close(box);
	           			    if(type==0){
	           		            grid.reload();
	           			    }else{
	           			    	grid.reload();
	           			    }
	           			}else{
	           			    FI.msg(json.msg,{icon:2});
	           			}
	           		});
	            }});
}
function illegal(id){
	var w = FI.open({type: 1,title:"商品违规原因",shade: [0.6, '#000'],border: [0],
	    content: '<textarea id="illegal_remarks" rows="7" style="width:96%" maxLength="200"></textarea>',
	    area: ['500px', '260px'],btn: ['确定', '关闭窗口'],
        yes: function(index, layero){
        	var illegal_remarks = $.trim($('#illegal_remarks').val());
        	if(illegal_remarks==''){
        		FI.msg('请输入违规原因 !', {icon: 5});
        		return;
        	}
        	var ll = FI.msg('数据处理中，请稍候...');
		    $.post(FI.U('admin/goods/illegal'),{id:id,illegal_remarks:illegal_remarks},function(data){
		    	layer.close(w);
		    	layer.close(ll);
		    	var json = FI.toAdminJson(data);
				if(json.status>0){
					FI.msg(json.msg, {icon: 1});
					grid.reload();
				}else{
					FI.msg(json.msg, {icon: 2});
				}
		   });
        }
	});
}

function initAuditGrid(){
	grid = $("#maingrid").ligerGrid({
		url:FI.U('admin/goods/auditByPage'),
		pageSize:FI.pageSize,
		pageSizeOptions:FI.pageSizeOptions,
		height:'99%',
        width:'100%',
        rowHeight:65,
        minColToggle:6,
        rownumbers:true,
        columns: [
	        { display: '&nbsp;', name: 'goods_name',width:60,align:'left',heightAlign:'left',isSort: false,render: function (rowdata, rowindex, value){
            	return "<img style='height:60px;width:60px;' src='"+FI.conf.ROOT+"/"+rowdata['goods_img']+"'>";
            }},
	        { display: '商品名称', name: 'goods_name',heightAlign:'left',isSort: false,render: function (rowdata, rowindex, value){
	            return rowdata['goods_name'];
	        }},
	        { display: '商品编号', name: 'goods_sn',isSort: false,render: function (rowdata, rowindex, value){
	        	return "<div class='goods-valign-m'>"+rowdata['goods_sn']+"</div>";
	        }},
	        { display: '价格', name: 'shop_price',isSort: false,render: function (rowdata, rowindex, value){
	        	return "<div class='goods-valign-m'>"+rowdata['shop_price']+"</div>";
	        }},
	        { display: '所属店铺', name: 'shop_name',isSort: false,render: function (rowdata, rowindex, value){
	        	return "<div class='goods-valign-m'>"+rowdata['shop_name']+"</div>";
	        }},
	        { display: '所属分类', name: 'goodsCatName',isSort: false,render: function (rowdata, rowindex, value){
	        	return "<div class='goods-valign-m'>"+rowdata['goodsCatName']+"</div>";
	        }},
	        { display: '销量', name: 'sale_num',isSort: false,render: function (rowdata, rowindex, value){
	        	return "<div class='goods-valign-m'>"+rowdata['sale_num']+"</div>";
	        }},
	        { display: '操作', name: 'op',isSort: false,render: function (rowdata, rowindex, value){
	            var h = "";
	            h += "<div class='goods-valign-m'><a target='_blank' href='"+FI.U("home/goods/detail","id="+rowdata['goods_id']+"&key="+rowdata['verfiycode'])+"'>查看</a> ";
	            if(FI.GRANT.DSHSP_04)h += "<a href='javascript:allow(" + rowdata['goods_id'] + ",0)'>审核通过</a> ";
	            if(FI.GRANT.DSHSP_03)h += "<a href='javascript:del(" + rowdata['goods_id'] + ",0)'>删除</a></div> "; 
	            return h;
	        }}
        ]
    });
}
function loadAuditGrid(){
	var params = FI.getParams('.j-ipt');
	params.area_id_path = FI.ITGetAllAreaVals('area_id1','j-areas').join('_');
	params.goods_cat_id_path = FI.ITGetAllGoodsCatVals('cat_0','pgoodsCats').join('_');
	grid.set('url',FI.U('admin/goods/auditByPage',params));
}
function allow(id,type){
	var box = FI.confirm({content:"您确定审核通过该商品吗?",yes:function(){
        var loading = FI.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
        $.post(FI.U('admin/goods/allow'),{id:id},function(data,textStatus){
        			layer.close(loading);
        			var json = FI.toAdminJson(data);
        			if(json.status=='1'){
        			    FI.msg(json.msg,{icon:1});
        			    layer.close(box);
        			    if(type==0){
        		            grid.reload();
        			    }else{
        			    	location.reload();
        			    }
        			}else{
        			    FI.msg(json.msg,{icon:2});
        			}
        		});
         }});
}

function initIllegalGrid(){
	grid = $("#maingrid").ligerGrid({
		url:FI.U('admin/goods/illegalByPage'),
		pageSize:FI.pageSize,
		pageSizeOptions:FI.pageSizeOptions,
		height:'99%',
        width:'100%',
        rowHeight:65,
        minColToggle:6,
        rownumbers:true,
        columns: [
	        { display: '&nbsp;', name: 'goods_name',width:60,align:'left',heightAlign:'left',isSort: false,render: function (rowdata, rowindex, value){
            	return "<img style='height:60px;width:60px;' src='"+FI.conf.ROOT+"/"+rowdata['goods_img']+"'>";
            }},
	        { display: '商品名称', name: 'goods_name',heightAlign:'left',isSort: false,render: function (rowdata, rowindex, value){
	            return rowdata['goods_name'];
	        }},
	        { display: '商品编号', name: 'goods_sn',isSort: false,render: function (rowdata, rowindex, value){
	        	return "<div class='goods-valign-m'>"+rowdata['goods_sn']+"</div>";
	        }},
	        { display: '所属店铺', name: 'shop_name',isSort: false,render: function (rowdata, rowindex, value){
	        	return "<div class='goods-valign-m'>"+rowdata['shop_name']+"</div>";
	        }},
	        { display: '所属分类', name: 'goodsCatName',isSort: false,render: function (rowdata, rowindex, value){
	        	return "<div class='goods-valign-m'>"+rowdata['goodsCatName']+"</div>";
	        }},   
	        { display: '违规原因', name: 'illegal_remarks',isSort: false,render: function (rowdata, rowindex, value){
	        	return "<div class='goods-valign-m'>"+rowdata['illegal_remarks']+"</div>";
	        }},
	        { display: '操作', name: 'op',isSort: false,render: function (rowdata, rowindex, value){
	            var h = "";
	            h += "<div class='goods-valign-m'><a target='_blank' href='"+FI.U("home/goods/detail","id="+rowdata['goods_id']+"&key="+rowdata['verfiycode'])+"'>查看</a> ";
	            if(FI.GRANT.WGSP_04)h += "<a href='javascript:allow(" + rowdata['goods_id'] + ",0)'>审核通过</a> ";
	            if(FI.GRANT.WGSP_03)h += "<a href='javascript:del(" + rowdata['goods_id'] + ",0)'>删除</a></div> "; 
	            return h;
	        }}
        ]
    });
}
function loadIllegalGrid(){
	var params = FI.getParams('.j-ipt');
	params.area_id_path = FI.ITGetAllAreaVals('area_id1','j-areas').join('_');
	params.goods_cat_id_path = FI.ITGetAllGoodsCatVals('cat_0','pgoodsCats').join('_');
	grid.set('url',FI.U('admin/goods/illegalByPage',params));
}