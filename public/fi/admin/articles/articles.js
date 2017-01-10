var grid;
var combo;
function initGrid(){
	grid = $("#maingrid").ligerGrid({
		url:FI.U('admin/articles/pageQuery'),
		pageSize:FI.pageSize,
		pageSizeOptions:FI.pageSizeOptions,
		height:'99%',
        width:'100%',
        minColToggle:6,
        rownumbers:true,
        columns: [
	        { display: '标题', name: 'article_title',isSort: false},
            { display: '分类', name: 'cat_name',isSort: false,},
            { display: '是否显示', width: 100, name: 'is_show',isSort: false,
                render: function (item)
                {
                    if (parseInt(item.is_show) == 1) return '<span style="cursor:pointer;" onclick="toggleIsShow('+item["is_show"]+','+item["article_id"]+');">显示</span>';
                    return '<span style="cursor:pointer;" onclick="toggleIsShow('+item["is_show"]+','+item["article_id"]+');">隐藏</span>';
                }
            },
            { display: '最后编辑者',name: 'staff_name',width: 100, isSort: false},
	        { display: '创建时间', name: 'create_time',width: 200,isSort: false},
	        { display: '操作', name: 'op',width: 100,isSort: false,
	        	render: function (rowdata){
		            var h = "";
		            if(FI.GRANT.WZGL_02)h += "<a href='javascript:toEdit("+rowdata["article_id"]+")'>修改</a> ";
		            if(FI.GRANT.WZGL_03)h += "<a href='javascript:toDel("+rowdata["article_id"]+")'>删除</a> "; 
		            return h;
	        	}
	        }
        ]
    });

}

function initCombo(){
    combo = $("#cat_ids").ligerComboBox({
        width: 210,
        selectBoxWidth: 200,
        selectBoxHeight: 300,valueField:'cat_id',textField: 'cat_name',treeLeafOnly:false,
        tree: { url: FI.U('admin/articlecats/listQuery2'), checkbox: false, ajaxType: 'post', textFieldName : 'cat_name',idField: 'cat_id',parentIDField: 'parent_id'},
        onSelected: function (value)
        {
        	$('#cat_id').val(value);
        }
    });
    $('.l-text-combobox').css('width','202');
}

function loadGrid(){
	grid.set('url',FI.U('admin/articles/pageQuery','key='+$('#key').val()));
}

function toggleIsShow(t,v){
	if(!FI.GRANT.WZGL_02)return;
    var loading = FI.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
    	$.post(FI.U('admin/articles/editiIsShow'),{id:v,is_show:t},function(data,textStatus){
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

function toEdit(id){
	location.href=FI.U('admin/articles/toEdit','id='+id);
}

function toEdits(id){
    var params = FI.getParams('.ipt');
    params.id = id;
    var loading = FI.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
	$.post(FI.U('admin/articles/'+((id>0)?"edit":"add")),params,function(data,textStatus){
		  layer.close(loading);
		  var json = FI.toAdminJson(data);
		  if(json.status=='1'){
		    	FI.msg(json.msg,{icon:1});
		        setTimeout(function(){ 
			    	location.href=FI.U('admin/articles/index');
		        },1000);
		  }else{
		        FI.msg(json.msg,{icon:2});
		  }
	});
}

function toDel(id){
	var box = FI.confirm({content:"您确定要删除该文章吗?",yes:function(){
	           var loading = FI.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
	           	$.post(FI.U('admin/articles/del'),{id:id},function(data,textStatus){
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