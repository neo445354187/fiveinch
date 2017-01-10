var grid;
function initGrid(){
	grid = $("#maingrid").ligerGrid({
		url:FI.U('admin/goodsappraises/pageQuery'),
		pageSize:100,
		pageSizeOptions:[100],
		height:'99%',
        width:'100%',
        minColToggle:6,
        rownumbers:true,
        columns: [
	        { display: '订单号', name: 'order_no', isSort: false},
	        { display: '商品', name: 'goods_name', isSort: false},
	        { display: '商品主图', name: 'goods_img', isSort: false,render:function(rowdata, rowindex, value){
	        	var thumb = rowdata['goods_img'];
	        		thumb = thumb.replace('.','_thumb.');
	        	return "<img src='"+FI.conf.ROOT+"/"+thumb+"' height='28' width='28'/>";
	        	
	        }},
	        { display: '商品评分', name: 'goods_score', isSort: false,render:function(rowdata, rowindex, value){
	        	var s="<div style='line-height:28px;'>";
	        	for(var i=0;i<value;++i){
	        		s +="<img src='"+FI.conf.ROOT+"/fi/admin/goodsappraises/icon_score_yes.png'>";
	        	}
	        	s += "</div>";
	        	return s;
	        }},
	        { display: '时效评分', name: 'time_score', isSort: false,render:function(rowdata, rowindex, value){
	        	var s="<div style='line-height:28px;'>";
	        	for(var i=0;i<value;++i){
	        		s +="<img src='"+FI.conf.ROOT+"/fi/admin/goodsappraises/icon_score_yes.png'>";
	        	}
	        	s +="</div>";
	        	return s;
	        }},
	        { display: '服务评分', name: 'service_score', isSort: false,render:function(rowdata, rowindex, value){
	        	var s="<div style='line-height:28px;'>";
	        	for(var i=0;i<value;++i){
	        		s +="<img src='"+FI.conf.ROOT+"/fi/admin/goodsappraises/icon_score_yes.png'>";
	        	}
	        	s +="</div>";
	        	return s;
	        }},
	        { display: '评价内容', name: 'content', isSort: false},
	        { display: '状态', name: 'is_show', isSort: false,render:function(rowdata, rowindex, value){
	        	return (value==0)?'隐藏':'显示';
	        }}
	        ,
	        
	        { display: '操作', name: 'op',isSort: false,render: function (rowdata, rowindex, value){
	            var h = "";
	            if(FI.GRANT.PJGL_02)h += "<a href='"+FI.U('admin/goodsappraises/toEdit','id='+rowdata['id'])+"'>修改</a> ";
	            if(FI.GRANT.PJGL_03)h += "<a href='javascript:toDel(" + rowdata['id'] + ")'>删除</a> "; 
	            return h;
	        }}
        ]
    });


}
function toDel(id){
	var box = FI.confirm({content:"您确定要删除该记录吗?",yes:function(){
	           var loading = FI.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
	           	$.post(FI.U('admin/goodsappraises/del'),{id:id},function(data,textStatus){
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
function loadGrid1(){
		var query = FI.getParams('.query');
	    grid.set('url',FI.U('admin/goodsappraises/pageQuery',query));
}

function editInit(){
	

/* 表单验证 */
    $('#goodsAppraisesForm').validator({
            fields: {
                content: {
                  rule:"required;length(3~50)",
                  msg:{length:"评价内容为3-50个字",required:"评价内容为3-50个字"},
                  tip:"评价内容为3-50个字",
                  ok:"",
                },
                score:  {
                  rule:"required",
                  msg:{required:"评分必须大于0"},
                  ok:"",
                  target:"#score_error",
                },
                
            },

          valid: function(form){
            var params = FI.getParams('.ipt');
                //获取修改的评分
                params.goods_score = $('.goods_score').find('[name=score]').val();
                params.time_score = $('.time_score').find('[name=score]').val();
                params.service_score = $('.service_score').find('[name=score]').val();
            var loading = FI.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
            $.post(FI.U('admin/goodsappraises/'+((params.id==0)?"add":"edit")),params,function(data,textStatus){
              layer.close(loading);
              var json = FI.toAdminJson(data);
              if(json.status=='1'){
                  FI.msg("操作成功",{icon:1});
                  location.href=FI.U('Admin/goodsappraises/index');
              }else{
                    FI.msg(json.msg,{icon:2});
              }
            });

      }

    });
}