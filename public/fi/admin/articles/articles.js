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
	        { display: '标题', name: 'articleTitle',isSort: false},
            { display: '分类', name: 'catName',isSort: false,},
            { display: '是否显示', width: 100, name: 'isShow',isSort: false,
                render: function (item)
                {
                    if (parseInt(item.isShow) == 1) return '<span style="cursor:pointer;" onclick="toggleIsShow('+item["isShow"]+','+item["articleId"]+');">显示</span>';
                    return '<span style="cursor:pointer;" onclick="toggleIsShow('+item["isShow"]+','+item["articleId"]+');">隐藏</span>';
                }
            },
            { display: '最后编辑者',name: 'staffName',width: 100, isSort: false},
	        { display: '创建时间', name: 'createTime',width: 200,isSort: false},
	        { display: '操作', name: 'op',width: 100,isSort: false,
	        	render: function (rowdata){
		            var h = "";
		            if(FI.GRANT.WZGL_02)h += "<a href='javascript:toEdit("+rowdata["articleId"]+")'>修改</a> ";
		            if(FI.GRANT.WZGL_03)h += "<a href='javascript:toDel("+rowdata["articleId"]+")'>删除</a> "; 
		            return h;
	        	}
	        }
        ]
    });

}

function initCombo(){
    combo = $("#catIds").ligerComboBox({
        width: 210,
        selectBoxWidth: 200,
        selectBoxHeight: 300,valueField:'catId',textField: 'catName',treeLeafOnly:false,
        tree: { url: FI.U('admin/articlecats/listQuery2'), checkbox: false, ajaxType: 'post', textFieldName : 'catName',idField: 'catId',parentIDField: 'parentId'},
        onSelected: function (value)
        {
        	$('#catId').val(value);
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
    	$.post(FI.U('admin/articles/editiIsShow'),{id:v,isShow:t},function(data,textStatus){
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