var grid;
$(function(){
	grid = $("#maingrid").ligerGrid({
		url:FI.U('admin/speccats/pageQuery'),
		pageSize:FI.pageSize,
		pageSizeOptions:FI.pageSizeOptions,
		height:'99%',
        width:'100%',
        minColToggle:6,
        rownumbers:true,
        columns: [
	        { display: '规格类型', name: 'name', id:'cat_name', isSort: false,align: 'left'},
	        { display: '所属商品分类', name: 'goodsCatNames', isSort: false,align: 'left'},
	        { display: '是否允许上传图片', name: 'is_allow_img', isSort: false,width: 100,render :function(rowdata, rowindex, value){
	        	return (value==1)?'允许':'';
	        }},
	        { display: '是否显示', name: 'is_show', isSort: false,width: 100,render :function(rowdata, rowindex, value){
	        	return (value==1)?'<span style="cursor:pointer" onclick="toggleIsShow('+rowdata['cat_id']+', 0)">显示</span>':(value==0?'<span style="cursor:pointer" onclick="toggleIsShow('+rowdata['cat_id']+', 1)">隐藏</span>':'');
	        }},
	        { display: '操作', name: 'op',isSort: false,width: 200,render: function (rowdata, rowindex, value){
	            var h = "";
	          if(rowdata.specId>0){
	        	  if(FI.GRANT.SPGG_02)h += "<a href='javascript:toEdit("+ rowdata['cat_id']+"," + rowdata['id'] + ")'>修改</a> ";
	        	  if(FI.GRANT.SPGG_03)h += "<a href='javascript:toDel(" + rowdata['id'] + ")'>删除</a> "; 
	            return h;
	          }else{
	        	  if(FI.GRANT.SPGG_02)h += "<a href='javascript:toEditCat(" + rowdata['id'] + ")' >修改</a> ";
	        	  if(FI.GRANT.SPGG_03)h += "<a href='javascript:toDelCat(" + rowdata['id'] + ")'>删除</a> "; 
	          }
	          return h;
	        }}
        ]
    });
})
//------------------规格类型---------------//
function toEditCat(cat_id){
	$("select[id^='bcat_0_']").remove();
	$('#specCatsForm').get(0).reset();
	$.post(FI.U('admin/speccats/get'),{cat_id:cat_id},function(data,textStatus){
        var json = FI.toAdminJson(data);
        FI.setValues(json);
        if(json.goods_cat_id>0){
        	var goods_cat_path = json.goods_cat_path.split("_");
        	$('#bcat_0').val(goods_cat_path[0]);
        	var opts = {id:'bcat_0',val:goods_cat_path[0],childIds:goods_cat_path,className:'goodsCats'}
        	FI.ITSetGoodsCats(opts);
        }
		var title =(cat_id==0)?"新增":"编辑";
		var box = FI.open({title:title,type:1,content:$('#specCatsBox'),area: ['750px', '260px'],btn:['确定','取消'],yes:function(){
			$('#specCatsForm').submit();
		}});
		$('#specCatsForm').validator({
			fields: {
			 	'cat_name': {rule:"required remote;",msg:{required:'请输入规格名称'}},
			},
			valid: function(form){
			    var params = FI.getParams('.ipt');
			    var loading = FI.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
			    params.goods_cat_id = FI.ITGetGoodsCatVal('goodsCats');
			 	$.post(FI.U('admin/speccats/'+((params.cat_id==0)?"add":"edit")),params,function(data,textStatus){
			 		layer.close(loading);
			    	var json = FI.toAdminJson(data);
					if(json.status=='1'){
						FI.msg("操作成功",{icon:1});
						grid.reload();
						layer.close(box);
				  	}else{
				    	FI.msg(json.msg,{icon:2});
					}
			 	});
			}
		});

	});
}

function loadGrid(){
	var keyName = $("#keyName").val();
	var goods_cat_path = FI.ITGetAllGoodsCatVals('cat_0','pgoodsCats');
	grid.set('url',FI.U('admin/speccats/pageQuery',{"keyName":keyName,"goods_cat_path":goods_cat_path.join('_')}));
}

function toDelCat(cat_id){
	var box = FI.confirm({content:"您确定要删除该类型吗?",yes:function(){
		var loading = FI.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
		$.post(FI.U('admin/speccats/del'),{cat_id:cat_id},function(data,textStatus){
			layer.close(loading);
			var json = FI.toAdminJson(data);
			if(json.status=='1'){
				FI.msg("操作成功",{icon:1});
				layer.close(box);
				grid.reload();
			}else{
				FI.msg(json.msg,{icon:2});
			}
		});
	}});
}

function toggleIsShow( cat_id, is_show){
	$.post(FI.U('admin/speccats/setToggle'), {'cat_id':cat_id, 'is_show':is_show}, function(data, textStatus){
		var json = FI.toAdminJson(data);
		if(json.status=='1'){
			FI.msg("操作成功",{icon:1});
			grid.reload();
		}else{
			FI.msg(json.msg,{icon:2});
		}
	})
}

//------------------规格---------------//
function toDel(specId){
	var box = FI.confirm({content:"您确定要删除该规格吗?",yes:function(){
		var loading = FI.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
		$.post(FI.U('admin/specs/del'),{specId:specId},function(data,textStatus){
			layer.close(loading);
			var json = FI.toAdminJson(data);
			if(json.status=='1'){
				FI.msg("操作成功",{icon:1});
				layer.close(box);
				grid.reload();
			}else{
				FI.msg(json.msg,{icon:2});
			}
		});
	}});
}


function toEdit(cat_id,specId){
	    $.post(FI.U('admin/specs/get'),{specId:specId},function(data,textStatus){
	    	var json = FI.toAdminJson(data);
	    	$('#specForm').get(0).reset();
	      	FI.setValues(json);

			var title =(specId==0)?"新增":"编辑";
			var box = FI.open({title:title,type:1,content:$('#specBox'),area: ['450px', '160px'],btn:['确定','取消'],yes:function(){
				$('#specForm').submit();
			}});
			$('#specForm').validator({
				rules: {
			        remote: function(el){
			        	return $.post(FI.U('admin/specs/checkSpecName'),{"specName":el.value,"cat_id":cat_id},function(data,textStatus){});
			        }
			    },
		        fields: {
		        	'specName': {rule:"required; remote;",msg:{required:'请输入规格名称'}},
		        },
		        valid: function(form){
		    	   var params = FI.getParams('.ipt');
		    	   params.cat_id = cat_id;
		    	   var loading = FI.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
		    	   $.post(FI.U('admin/specs/'+((specId==0)?"add":"edit")),params,function(data,textStatus){
		    		   layer.close(loading);
		    		   var json = FI.toAdminJson(data);
		    		   if(json.status=='1'){
		    	          FI.msg("操作成功",{icon:1});
		    	          layer.close(box);
		    	          grid.reload();
		    	          $('#specForm')[0].reset();
		    		   }else{
		    			   FI.msg(json.msg,{icon:2});
		    	      }
		    	    });
		
		    	}
		
			});
	});
}
