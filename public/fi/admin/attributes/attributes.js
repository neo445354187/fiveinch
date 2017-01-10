var grid;
$(function(){
	grid = $("#maingrid").ligerGrid({
		url:FI.U('admin/attributes/pageQuery'),
		pageSize:100,
		pageSizeOptions:[100],
		height:'99%',
        width:'100%',
        minColToggle:6,
        rownumbers:true,
        columns: [
	        { display: '属性名称', name: 'attr_name', isSort: false,align: 'left'},
	        { display: '所属商品分类', name: 'goodsCatNames', isSort: false,align: 'left'},
	        { display: '属性类型', name: 'attr_type', isSort: false,align: 'left',render :function(rowdata, rowindex, value){
	        	return (value==1)?'多选项':(value==2?'下拉框':'输入框');
	        }},
	        { display: '属性选项', name: 'attr_val', isSort: false,align: 'left'},
	        { display: '是否显示', name: 'is_show', isSort: false,width: 100,render :function(rowdata, rowindex, value){
	        	return (value==1)?'<span style="cursor:pointer" onclick="toggleIsShow('+rowdata['attr_id']+', 0)">显示</span>':(value==0?'<span style="cursor:pointer" onclick="toggleIsShow('+rowdata['attr_id']+', 1)">隐藏</span>':'');
	        }},
	        { display: '排序号', name: 'attr_sort', isSort: false,align: 'left'},
	        { display: '操作', name: 'op',isSort: false,width: 200,render: function (rowdata, rowindex, value){
	            var h = "";
	        	if(FI.GRANT.SPSX_02)h += "<a href='javascript:toEdit("+ rowdata['attr_id']+")'>修改</a> ";
	        	if(FI.GRANT.SPSX_03)h += "<a href='javascript:toDel(" + rowdata['attr_id'] + ")'>删除</a> "; 
	            return h;	          
	        }}
        ]
    });
});

//------------------属性类型---------------//
function toEdit(attr_id){
	$("select[id^='bcat_0_']").remove();
	$('#attrForm').get(0).reset();
	$.post(FI.U('admin/attributes/get'),{attr_id:attr_id},function(data,textStatus){
        var json = FI.toAdminJson(data);
        FI.setValues(json);
        if(json.goods_cat_id>0){
        	var goods_cat_path = json.goods_cat_path.split("_");
        	$('#bcat_0').val(goods_cat_path[0]);
        	var opts = {id:'bcat_0',val:goods_cat_path[0],childIds:goods_cat_path,className:'goodsCats'}
        	FI.ITSetGoodsCats(opts);
        }
		var title =(attr_id==0)?"新增":"编辑";
		var box = FI.open({title:title,type:1,content:$('#attrBox'),area: ['750px', '320px'],btn:['确定','取消'],yes:function(){
			$('#attrForm').submit();
		}});
		$('#attrForm').validator({
			rules: {
				attr_type: function() {
		            return ($('#attr_type').val()!='0');
		        }
		    },
			fields: {
			 	'attr_name': {rule:"required",msg:{required:'请输入属性名称'}},
			 	'attr_val': 'required(attr_type)'
			},
			valid: function(form){
			    var params = FI.getParams('.ipt');
			    var loading = FI.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
			    params.goods_cat_id = FI.ITGetGoodsCatVal('goodsCats');
			 	$.post(FI.U('admin/attributes/'+((params.attr_id==0)?"add":"edit")),params,function(data,textStatus){
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
	grid.set('url',FI.U('admin/attributes/pageQuery',{"keyName":keyName,"goods_cat_path":goods_cat_path.join('_')}));
}

function toDel(attr_id){
	var box = FI.confirm({content:"您确定要删除该属性吗?",yes:function(){
		var loading = FI.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
		$.post(FI.U('admin/attributes/del'),{attr_id:attr_id},function(data,textStatus){
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

function toggleIsShow( attr_id, is_show){
	$.post(FI.U('admin/attributes/setToggle'), {'attr_id':attr_id, 'is_show':is_show}, function(data, textStatus){
		var json = FI.toAdminJson(data);
		if(json.status=='1'){
			FI.msg("操作成功",{icon:1});
			grid.reload();
		}else{
			FI.msg(json.msg,{icon:2});
		}
	})
}

function changeArrType(v){
	if(v>0){
		$('#attr_valTr').show();
	}else{
		$('#attr_valTr').hide();
	}
}
