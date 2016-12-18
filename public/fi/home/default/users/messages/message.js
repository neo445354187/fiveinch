$(function(){
  queryByList();
});
function queryByList(p){
     var params = {};
     params.page = p;
     var load = FI.load({msg:'正在加载信息，请稍后...'})
     $.post(FI.U('Home/Messages/pageQuery'),params,function(data,textStatus){
    	 layer.close(load);
        var json = FI.toJson(data);
	    if(json.data){
		  json = json.data;
	      var gettpl = document.getElementById('msg').innerHTML;
	      //复选框为未选中状态
	      $('#all').attr('checked',false);
	      laytpl(gettpl).render(json.Rows, function(html){
	          $('#msg_box').html(html);
	      });
	      if(json.TotalPage>1){
            laypage({
               cont: 'fi-page', 
               pages:json.TotalPage, 
               curr: json.CurrentPage,
               skin: '#e23e3d',
               groups: 3,
               jump: function(e, first){
                    if(!first){
                      queryByList(e.curr);
                    }
                  } 
            });
          }else{
            $('#fi-page').empty();
          }
	  }
  });
}

function showMsg(id){
  location.href=FI.U('home/messages/showMsg','msgId='+id);
}

function delMsg(obj,id){
FI.confirm({content:"您确定要删除该消息吗？", yes:function(tips){
  var ll = FI.load('数据处理中，请稍候...');
  $.post(FI.U('Home/messages/del'),{id:id},function(data,textStatus){
    layer.close(ll);
      layer.close(tips);
    var json = FI.toJson(data);
    if(json.status=='1'){
      FI.msg('操作成功!', {icon: 1}, function(){
         queryByList();
      });
    }else{
      FI.msg('操作失败!', {icon: 5});
    }
  });
}});
}
function batchDel(){
FI.confirm({content:"您确定要删除该消息吗？", yes:function(tips){
    var ids = FI.getChks('.chk');
    if(ids==''){
      FI.msg('请先选择消息!', {icon: 5});
      return;
    }
    var params = {};
    params.ids = ids;
    var load = FI.load({msg:'请稍后...'});
    $.post(FI.U('home/messages/batchDel'),params,function(data,textStatus){
      layer.close(load);
      var json = FI.toJson(data);
      if(json.status=='1'){
        FI.msg('操作成功',{icon:1},function(){
             queryByList();
        });
      }else{
        FI.msg('操作失败',{icon:5});
      }
    });
}});
}
function batchRead(){
FI.confirm({content:"您确定要将这些消息标记为已读吗？", yes:function(tips){
    var ids = FI.getChks('.chk');
    if(ids==''){
      FI.msg('请先选择消息!', {icon: 5});
      return;
    }
    var params = {};
    params.ids = ids;
    var load = FI.load({msg:'请稍后...'});
    $.post(FI.U('home/messages/batchRead'),params,function(data,textStatus){
      layer.close(load);
      var json = FI.toJson(data);
      if(json.status=='1'){
        FI.msg('操作成功',{icon:1},function(){
             queryByList();
        });
      }else{
        FI.msg('操作失败',{icon:5});
      }
    });
}});
}
