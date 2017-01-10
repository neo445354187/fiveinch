var grid;
$(function(){

	grid = $("#maingrid").ligerGrid({
		url:FI.U('admin/homemenus/pageQuery'),
		pageSize:FI.pageSize,
		pageSizeOptions:FI.pageSizeOptions,
		height:'99%',
        width:'100%',
        minColToggle:6,
        rownumbers:true,
        columns: [
	        { display: '菜单名称', name: 'menu_name', id:"tmenu_id", isSort: false},
	        { display: '父级菜单', name: 'parentName', isSort: false},
	        { display: '菜单类型', name: 'src', isSort: false,render :function(rowdata, rowindex, value){
	        	return (rowdata['menu_type']==1)?"商家菜单":"用户菜单";
	        }},
	        { display: '菜单Url', name: 'menu_url', isSort: false},
	        { display: '是否显示', name: 'is_show', isSort: false,render :function(rowdata, rowindex, value){
	        	return (value==1)?'<span style="cursor:pointer" onclick="is_showtoggle('+rowdata['menu_id']+', 0)">显示</span>':'<span style="cursor:pointer" onclick="is_showtoggle('+rowdata['menu_id']+', 1)">隐藏</span>';
	        }},
	        { display: '排序号', name: 'menu_sort', isSort: false,render:function(rowdata,rowindex,value){
             return '<span style="cursor:pointer;" ondblclick="changeSort(this,'+rowdata["menu_id"]+');">'+value+'</span>';
          }},
	        { display: '操作', name: 'op',isSort: false,render: function (rowdata, rowindex, value){
	            var h = "";
	            if(FI.GRANT.QTCD_01)h += "<a href='javascript:toEdit(0," + rowdata['menu_id'] + ")'>添加子菜单</a> ";
	            if(FI.GRANT.QTCD_02)h += "<a href='javascript:getForEdit(" + rowdata['menu_id'] + ")' href='"+FI.U('admin/homemenus/toEdit','menu_id='+rowdata['menu_id'])+"'>修改</a> ";
	            if(FI.GRANT.QTCD_03)h += "<a href='javascript:toDel(" + rowdata['menu_id'] + ")'>删除</a> "; 
	            return h;
	        }}
        ],
        alternatingRow: false,
        onBeforeShowData: function ()
        {
            var grid = this; 
            grid.collapsedRows = []; 
        },
        onTreeExpand: function (data,e)
        {
            var grid = this;
            if (!data.loaded){
                grid.toggleLoading(true);
                //加载ajax数据
               
                return false;
            }
        },
        tree:{
            columnId: 'tmenu_id',
            isParent: function (data)
            { 
                var exist = 'children' in data;
                if (exist) return true;

                if (data.childrenurl) return true;
                return false;
            }
        }
    });
})


var oldSort;
function changeSort(t,id){
  if(!FI.GRANT.QTCD_02)return;
  $(t).attr('ondblclick'," ");
var html = "<input type='text' id='sort-"+id+"' style='width:30px;' onblur='doneChange(this,"+id+")' value='"+$(t).html()+"' />";
 $(t).html(html);
 $('#sort-'+id).focus();
 $('#sort-'+id).select();
}
function doneChange(t,id){
  var sort = ($(t).val()=='')?0:$(t).val();
  if(sort==oldSort){
    $(t).parent().attr('ondblclick','changeSort(this,'+id+')');
    $(t).parent().html(parseInt(sort));
    return;
  }
  $.post(FI.U('admin/homemenus/changeSort'),{id:id,menu_sort:sort},function(data){
    var json = FI.toAdminJson(data);
    if(json.status==1){
        $(t).parent().attr('ondblclick','changeSort(this,'+id+')');
        $(t).parent().html(parseInt(sort));
    }
  });
}




function toDel(menu_id){
	var box = FI.confirm({content:"删除该菜单会将下边的子菜单也一并删除，您确定要删除吗?",yes:function(){
		var loading = FI.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
		$.post(FI.U('admin/homemenus/del'),{menu_id:menu_id},function(data,textStatus){
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



function edit(menu_id){
  //获取所有参数
  var params = FI.getParams('.ipt');
    params.menu_id = menu_id;
    var loading = FI.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
    $.post(FI.U('admin/homemenus/'+((menu_id==0)?"add":"edit")),params,function(data,textStatus){
      layer.close(loading);
      var json = FI.toAdminJson(data);
      if(json.status=='1'){
          FI.msg("操作成功",{icon:1});
          location.href=FI.U('admin/homemenus/index');
      }else{
            FI.msg(json.msg,{icon:2});
      }
    });
}
function is_showtoggle(menu_id, is_show){
  if(!FI.GRANT.QTCD_02)return;
	$.post(FI.U('admin/homemenus/setToggle'), {'menu_id':menu_id, 'is_show':is_show}, function(data, textStatus){
		var json = FI.toAdminJson(data);
		if(json.status=='1'){
			FI.msg("操作成功",{icon:1});
			grid.reload();
		}else{
			FI.msg(json.msg,{icon:2});
		}
	})
}

function getForEdit(menu_id){
	$('#menuForm')[0].reset();
	var loading = FI.msg('正在获取数据，请稍后...', {icon: 16,time:60000});
    $.post(FI.U('admin/homemenus/get'),{menu_id:menu_id},function(data,textStatus){
          layer.close(loading);
          var json = FI.toAdminJson(data);
          if(json.menu_id){
          		FI.setValues(json);
          		toEdit(json.menu_id,0);
          }else{
          		FI.msg(json.msg,{icon:2});
          }
   });
}

function toEdit(menu_id,parent_id){
	var title = "编辑";
	if(menu_id==0){
		$('#menuForm')[0].reset();
		title = "新增";
	}
	var box = FI.open({title:title,type:1,content:$('#menuBox'),area: ['550px', '350px'],btn:['确定','取消'],yes:function(){
		$('#menuForm').submit();
	}});
	$('#menuForm').validator({
        fields: {
        	'menu_name': {rule:"required;",msg:{required:'请输入菜单名称'}},
        	'menu_url': {rule:"required;",msg:{required:'请输入菜单Url'}},
        	'menu_sort': {rule:"required;integer",msg:{required:'请输入排序号',number:"请输入数字"}},
        	'is_show': {rule:"checked;",msg:{checked:'请选择是否显示'}},
        },
        valid: function(form){
        	var params = FI.getParams('.ipt');
    	   		params.menu_id = menu_id;
   	    		params.parent_id = parent_id;
    	  
   	    	var loading = FI.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
    	   $.post(FI.U('admin/homemenus/'+((menu_id==0)?"add":"edit")),params,function(data,textStatus){
    		   layer.close(loading);
    		   var json = FI.toAdminJson(data);
    		   if(json.status=='1'){
    	          FI.msg("操作成功",{icon:1});
    	          $('#menuForm')[0].reset();
    	          layer.close(box);
    	          grid.reload();
    	          $('#menuForm')[0].reset();
    		   }else{
    			   FI.msg(json.msg,{icon:2});
    	      }
    	    });

    	}

  });
}
function loadGrid(){
	grid.set('url',FI.U('admin/homemenus/pageQuery','menu_type='+$('#s_menu_type').val()));
}