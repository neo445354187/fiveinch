var grid;
function initGrid(){	
	grid = $('#maingrid').FIGridTree({
		url:FI.U('admin/goodscats/pageQuery'),
		pageSize:10000,
		pageSizeOptions:[10000],
		height:'99%',
        width:'100%',
        minColToggle:6,
        delayLoad :true,
        rownumbers:true,
        columns: [
	        { display: '分类名称', name: 'cat_name', id:'cat_id', align: 'left',isSort: false},
            { display: '推荐楼层', width: 100, name: 'is_floor',isSort: false,
                render: function (itemf)
                {
                    return '<span id="f_'+itemf["cat_id"]+'" v="'+itemf["is_floor"]+'" style="cursor:pointer;" onclick="toggleIsFloor(this,'+itemf["cat_id"]+');">'+((itemf["is_floor"]==1)?"推荐":"不推荐")+'</span>';
                }
            },
            { display: '是否显示', width: 100, name: 'is_show',isSort: false,
                render: function (item)
                {
                    return '<span id="sh_'+item["cat_id"]+'" v="'+item["is_show"]+'" style="cursor:pointer;" onclick="toggleIsShow(this,'+item["cat_id"]+');">'+((item["is_show"]==1)?"显示":"隐藏")+'</span>';
                }
            },
	        { display: '排序号', name: 'cat_sort',width: 100,isSort: false},
	        { display: '操作', name: 'op',width: 150,isSort: false,
	        	render: function (rowdata){
		            var h = "";
			        if(FI.GRANT.SPFL_01)h += "<a href='javascript:toEdit("+rowdata["cat_id"]+",0)'>新增子分类</a> ";
		            if(FI.GRANT.SPFL_02)h += "<a href='javascript:toEdit("+rowdata["parent_id"]+","+rowdata["cat_id"]+")'>修改</a> ";
		            if(FI.GRANT.SPFL_03)h += "<a href='javascript:toDel("+rowdata["parent_id"]+","+rowdata["cat_id"]+")'>删除</a> "; 
		            return h;
	        	}}
        ]
    });
}

function toggleIsFloor(obj,id){
	if(!FI.GRANT.SPFL_02)return;
    var loading = FI.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
    var v = ($(obj).attr('v')=='1')?0:1;
	$.post(FI.U('admin/goodscats/editiIsFloor'),{id:id,is_floor:v},function(data,textStatus){
		  layer.close(loading);
		  var json = FI.toAdminJson(data);
		  if(json.status=='1'){
		    	FI.msg(json.msg,{icon:1});
		    	$('#f_'+id).attr('v',v).html((v==1)?"推荐":"不推荐");
				grid.reload(id);
		  }else{
		    	FI.msg(json.msg,{icon:2});
		  }
	});
}

function toggleIsShow(obj,id){
	if(!FI.GRANT.SPFL_02)return;
    var loading = FI.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
    var v = ($(obj).attr('v')=='1')?0:1;
    $.post(FI.U('admin/goodscats/editiIsShow'),{id:id,is_show:v},function(data,textStatus){
		layer.close(loading);
		var json = FI.toAdminJson(data);
		if(json.status=='1'){
			 FI.msg(json.msg,{icon:1});
			 $('#sh_'+id).attr('v',v).html((v==1)?"显示":"隐藏");
			 grid.reload(id);
		}else{
			 FI.msg(json.msg,{icon:2});
		}
	});
}

function toEdit(pid,id){
	$('#goodscatsForm')[0].reset();
	if(id>0){
		$.post(FI.U('admin/goodscats/get'),{id:id},function(data,textStatus){
			var json = FI.toAdminJson(data);
			if(json){
				FI.setValues(json);
				editsBox(id);
			}
		});
	}else{
		FI.setValues({parent_id:pid,cat_name:'',is_show:1,is_floor:0,cat_sort:0});
		editsBox(id);
	}
}

function editsBox(id,v){
	var title =(id>0)?"修改商品分类":"新增商品分类";
	var box = FI.open({title:title,type:1,content:$('#goodscatsBox'),area: ['465px', '250px'],btn:['确定','取消'],yes:function(){
		$('#goodscatsForm').submit();
	          }});
	$('#goodscatsForm').validator({
	    fields: {
	    	cat_name: {
	    		tip: "请输入商品分类名称",
	    		rule: '商品分类名称:required;length[~10];'
	    	},
	    	cat_sort: {
            	tip: "请输入排序号",
            	rule: '排序号:required;length[~8];'
            },
	    },
	    valid: function(form){
	        var params = FI.getParams('.ipt');
	        params.id = id;
	        var loading = FI.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
    		$.post(FI.U('admin/goodscats/'+((id>0)?"edit":"add")),params,function(data,textStatus){
    			  layer.close(loading);
    			  var json = FI.toAdminJson(data);
    			  if(json.status=='1'){
    			    	FI.msg(json.msg,{icon:1});
    			    	layer.close(box);
    			    	grid.reload(params.parent_id);
    			  }else{
    			        FI.msg(json.msg,{icon:2});
    			  }
    		});
	    }
	});
}

function toDel(pid,id){
	var box = FI.confirm({content:"您确定要删除该商品分类吗?",yes:function(){
	           var loading = FI.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
	           	$.post(FI.U('admin/goodscats/del'),{id:id},function(data,textStatus){
	           			  layer.close(loading);
	           			  var json = FI.toAdminJson(data);
	           			  if(json.status=='1'){
	           			       FI.msg(json.msg,{icon:1});
	           			       layer.close(box);
	           		           grid.reload(pid);
	           			  }else{
	           			       FI.msg(json.msg,{icon:2});
	           			  }
	           		});
	            }});
}