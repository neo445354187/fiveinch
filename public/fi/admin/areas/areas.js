var grid;
function initGrid(){
	var parent_id=$('#h_area_id').val();
	grid = $("#maingrid").ligerGrid({
		url:FI.U('admin/areas/pageQuery','parent_id='+parent_id),
		pageSize:FI.pageSize,
		pageSizeOptions:FI.pageSizeOptions,
		height:'99%',
        width:'100%',
        minColToggle:6,
        rownumbers:true,
        columns: [
	        { display: '地区名称', name: 'area_name', align: 'left',isSort: false},
            { display: '是否显示', width: 100, name: 'is_show',isSort: false,
                render: function (item)
                {
                    if (parseInt(item.is_show) == 1) return '<span style="cursor:pointer;" onclick="toggleIsShow('+item["is_show"]+','+item["area_id"]+');">显示</span>';
                    return '<span style="cursor:pointer;" onclick="toggleIsShow('+item["is_show"]+','+item["area_id"]+');">隐藏</span>';
                }
            },
            { display: '排序字母', width: 100, name: 'area_key',isSort: false},
	        { display: '排序号', name: 'area_sort',width: 100,isSort: false},
	        { display: '操作', name: 'op',width: 100,isSort: false,
	        	render: function (rowdata){
		            var h = "";
		            if(rowdata["area_type"] < 3){
			            h += "<a href='"+FI.U('admin/areas/index','parent_id='+rowdata["area_id"])+"'>查看</a> ";
		            }
		            if(FI.GRANT.DQGL_02)h += "<a href='javascript:toEdit("+rowdata["area_id"]+","+rowdata["parent_id"]+")'>修改</a> ";
		            if(FI.GRANT.DQGL_03)h += "<a href='javascript:toDel("+rowdata["area_id"]+")'>删除</a> "; 
		            return h;
	        	}}
        ]
    });
}

function toggleIsShow(t,v){
	if(!FI.GRANT.DQGL_02)return;
    var loading = FI.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
    	$.post(FI.U('admin/areas/editiIsShow'),{id:v,is_show:t},function(data,textStatus){
			  layer.close(loading);
			  var json = FI.toAdminJson(data);
			  if(json.status=='1'){
			    	FI.msg(json.msg,{icon:1});
		            grid.reload();
			  }else{
			    	FI.msg(json.msg,{icon:2});
			  }
		});
}

function toReturn(){
	location.href=FI.U('admin/areas/index','parent_id='+$('#h_parent_id').val());
}

function letterOnblur(obj){
	if($.trim(obj.value)=='')return;
	if($('#area_key').val()!=='')return;
	var loading = FI.msg('正在生成排序字母，请稍后...', {icon: 16,time:60000});
	$.post(FI.U('admin/areas/letterObtain'),{code:obj.value},function(data,textStatus){
		layer.close(loading);
		var json = FI.toAdminJson(data);
		if(json.status == 1){
			$('#area_key').val(json.msg);
		}
	});
}

function toEdit(id,pid){
	$('#areaForm')[0].reset();
	if(id>0){
		var loading = FI.msg('正在获取数据，请稍后...', {icon: 16,time:60000});
		$.post(FI.U('admin/areas/get'),{id:id},function(data,textStatus){
			layer.close(loading);
			var json = FI.toAdminJson(data);
			if(json){
				FI.setValues(json);
				editsBox(id);
			}
		});
	}else{
		FI.setValues({parent_id:pid,area_id:0});
		editsBox(id);
	}
}

function editsBox(id){
	var box = FI.open({title:(id>0)?'修改地区':"新增地区",type:1,content:$('#areasBox'),area: ['460px', '260px'],btn:['确定','取消'],yes:function(){
		$('#areaForm').submit();
	          }});
	$('#areaForm').validator({
	    fields: {
	    	area_name: {
	    		tip: "请输入地区名称",
	    		rule: '地区名称:required;length[~10];'
	    	},
		    area_key: {
	    		tip: "请输入排序字母",
	    		rule: '排序字母:required;length[~1];'
	    	},
	    	area_sort: {
            	tip: "请输入排序号",
            	rule: '排序号:required;length[~8];'
            }
	    },
	    valid: function(form){
	        var params = FI.getParams('.ipt');
	        var loading = FI.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
	    		$.post(FI.U('admin/areas/'+((id>0)?"edit":"add")),params,function(data,textStatus){
	    			  layer.close(loading);
	    			  var json = FI.toAdminJson(data);
	    			  if(json.status=='1'){
	    			    	FI.msg(json.msg,{icon:1});
	    			    	layer.close(box);
	    		            grid.reload();
	    			  }else{
	    			        FI.msg(json.msg,{icon:2});
	    			  }
	    		});
	    }
	});
}

function toDel(id){
	var box = FI.confirm({content:"您确定要删除该地区吗?",yes:function(){
	           var loading = FI.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
	           	$.post(FI.U('admin/areas/del'),{id:id},function(data,textStatus){
	           			  layer.close(loading);
	           			  var json = FI.toAdminJson(data);
	           			  if(json.status=='1'){
	           			    	FI.msg(json.msg,{icon:1});
	           			    	layer.close(box);
	           		            grid.reload();
	           			  }else{
	           			    	FI.msg(json.msg,{icon:2});
	           			  }
	           		});
	            }});
}