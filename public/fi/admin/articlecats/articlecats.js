var grid;
function initGrid(){
	grid = $('#maingrid').FIGridTree({
		url:FI.U('admin/articlecats/pageQuery'),
		rownumbers:true,
        columns: [
	        { display: '分类名称', name: 'cat_name', id:'cat_id', align: 'left',isSort: false},
            { display: '分类类型', width: 100, name: 'cat_type',isSort: false,
                render: function (item)
                {
                    if (parseInt(item.cat_type) == 1) return '<span>系统菜单</span>';
                    return '<span>普通类型</span>';
                }
            },
            { display: '是否显示', width: 100, name: 'is_show',isSort: false,
                render: function (item)
                {
                    return '<span id="sh_'+item['cat_id']+'" style="cursor:pointer;" v="'+item.is_show+'" onclick="toggleIsShow(this,'+item["cat_id"]+');">'+((item.is_show=='1')?"显示":"隐藏")+'</span>';
                }
            },
	        { display: '排序号', name: 'cat_sort',width: 100,isSort: false},
	        { display: '操作', name: 'op',width: 200,isSort: false,
	        	render: function (rowdata,e){
		            var h = "";
			        if(FI.GRANT.WZFL_01)h += "<a href='javascript:toEdit("+rowdata["cat_id"]+",0)'>新增子分类</a> ";
		            if(FI.GRANT.WZFL_02)h += "<a href='javascript:toEdit("+rowdata["parent_id"]+","+rowdata["cat_id"]+")'>修改</a> ";
		            if(FI.GRANT.WZFL_03 && rowdata["cat_type"]==0)h += "<a href='javascript:toDel("+rowdata["parent_id"]+","+rowdata["cat_id"]+","+rowdata["cat_type"]+")'>删除</a> "; 
		            return h;
	        	}}
        ]
	});
}
function toggleIsShow(obj,id){
	if(!FI.GRANT.WZFL_02)return;
    var loading = FI.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
    var v = ($(obj).attr('v')=='1')?0:1;
    $.post(FI.U('admin/articlecats/editiIsShow'),{id:id,is_show:v},function(data,textStatus){
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
	$('#articlecatForm')[0].reset();
	if(id>0){
		$.post(FI.U('admin/articlecats/get'),{id:id},function(data,textStatus){
			var json = FI.toAdminJson(data);
			if(json){
				FI.setValues(json);
				editsBox(id);
			}
		});
	}else{
		FI.setValues({parent_id:pid,cat_name:'',is_show:1,cat_sort:0});
		editsBox(id);
	}
}

function editsBox(id){
	var title =(id>0)?"修改文章分类":"新增文章分类";
	var box = FI.open({title:title,type:1,content:$('#articlecatBox'),area: ['465px', '250px'],btn:['确定','取消'],yes:function(){
		          $('#articlecatForm').submit();
	          }});
	$('#articlecatForm').validator({
	    fields: {
	    	cat_name: {
	    		tip: "请输入分类名称",
	    		rule: '分类名称:required;length[~10];'
	    	},
	    	cat_sort: {
            	tip: "请输入排序号",
            	rule: '排序号:required;length[~8];'
            }
	    },
	    valid: function(form){
	        var params = FI.getParams('.ipt');
	        params.id = id;
	        var loading = FI.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
    		$.post(FI.U('admin/articlecats/'+((id>0)?"edit":"add")),params,function(data,textStatus){
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

function toDel(pid,id,type){
	var box = FI.confirm({content:"您确定要删除该分类以及其下的文章吗?",yes:function(){
	           var loading = FI.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
	           	$.post(FI.U('admin/articlecats/del'),{id:id,type:type},function(data,textStatus){
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