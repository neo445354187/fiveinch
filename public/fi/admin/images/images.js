function initSummary(){
	 var loading = FI.msg('正在获取数据，请稍后...', {icon: 16,time:60000});
	 $.post(FI.U('admin/images/summary'),{rnd:Math.random()},function(data,textStatus){
	       layer.close(loading);
	       var json = FI.toAdminJson(data);
	       if(json.status==1){
	    	   json = json.data;
	    	   var html = [],tmp,i=1,divLen = 0;
	    	   for(var key in json){
	    		   if(key=='_FISummary_')continue;
	    		   tmp = json[key];
	    		   html.push('<tr class="l-grid-row fi-grid-tree-row '+(((i%2==0))?"l-grid-row-alt":"")+'" height="28">'
	    				     ,'<td class="l-grid-row-cell l-grid-row-cell-rownumbers" style="width:26px;">'+(i++)+'</td>'
	    				     ,'<td class="l-grid-row-cell">'+FI.blank(tmp.directory,'未知目录')+'('+key+')'+'</td>'
	    				     ,'<td class="l-grid-row-cell">'+getCharts(json['_FISummary_'],tmp.data['1'],tmp.data['0'])+'</td>'
	    				     ,'<td class="l-grid-row-cell" nowrap>'+tmp.data['1']+'/'+tmp.data['0']+'</td>'
	    				     ,'<td class="l-grid-row-cell"><a href="'+FI.U('admin/images/lists','keyword='+key)+'">查看详情</a></td>');
	    	   }
	    	   $('#list').html(html.join(''));
	       }else{
	           FI.msg(json.msg,{icon:2});
	       }
	 });
}
function getCharts(maxSize,size1,size2){
	var w = FI.pageWidth()-400;
	var tlen = (parseFloat(size1,10)+parseFloat(size2,10))*w/maxSize+1;
	var s1len = parseFloat(size1,10)*w/maxSize;
	var s2len = parseFloat(size2,10)*w/maxSize;
	return ['<div style="width:'+tlen+'px"><div style="height:20px;float:left;width:'+s1len+'px;background:green;"></div><div style="height:20px;float:left;width:'+s2len+'px;background:#ddd;"></div></div>'];
}
var grid;
function initGrid(){
	
	grid = $("#maingrid").ligerGrid({
		url:FI.U('admin/images/pageQuery','keyword='+$('#key').val()+"&is_use="+$('#is_use').val()),
		pageSize:FI.pageSize,
		pageSizeOptions:FI.pageSizeOptions,
		height:'99%',
        width:'100%',
        rowHeight:50,
        minColToggle:6,
        rownumbers:true,
        columns: [
	        { display: '图片', name: 'img_path',isSort: false,render: function (rowdata, rowindex, value){
	        	return '<div style="margin:5px;"><img height="40" width="40" src="'+FI.conf.ROOT+'/'+value+'"/></div>';
	        }},
	        { display: '上传者', name: 'user_name',isSort: false,render: function (rowdata, rowindex, value){
	        	if(rowdata['from_type']==1){
	        		return "【职员】"+rowdata['login_name'];
	        	}else{
	        		if(FI.blank(rowdata['user_type'])==''){
	        			return '游客';
	        		}else{
	        			if(rowdata['user_type']==1){
	        				return "【商家:"+rowdata['shop_name']+"】"+rowdata['login_name'];
	        			}else{
	        				return rowdata['login_name'];
	        			}
	        		}
	        	}
	        }},
	        { display: '文件大小(M)', name: 'img_size',isSort: false},
	        { display: '状态', name: 'is_use',isSort: false,render: function (rowdata, rowindex, value){
	        	return (value==1)?'有效':'无效';
	        }},
	        { display: '上传时间', name: 'create_time',isSort: false},
	        { display: '操作', name: 'op',isSort: false,render: function (rowdata, rowindex, value){
	        	var h = '<a href="javascript:toView('+rowdata['img_id']+',\''+rowdata['img_path']+'\')">查看</a>';
	        	if(FI.GRANT.TPKJ_04)h += '&nbsp;&nbsp;<a href="javascript:toDel('+rowdata['img_id']+')">删除</a>';
	        	return h;
	        }}
        ]
    });
	loadGrid();
}
function loadGrid(){
	grid.set('url',FI.U('admin/images/pageQuery','keyword='+$('#key').val()+"&is_use="+$('#is_use').val()))
}
function toView(id,img){
    parent.showBox({title:'图片详情',type:2,content:FI.U('admin/images/checkImages','img_path='+img),area: ['700px', '510px'],btn:['关闭']});
}
function toDel(id){
	var box = FI.confirm({content:"您确定要删除该图片吗?<br/>注意：删除该图片后将不可找回!",yes:function(){
		var loading = FI.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
		$.post(FI.U('admin/images/del'),{id:id},function(data,textStatus){
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