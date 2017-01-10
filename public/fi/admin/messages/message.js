var grid;
var h;
function initGrid(){
	$('.fi-tab-2').height(h-25);
	grid = $("#maingrid").ligerGrid({
		url:FI.U('admin/Messages/pageQuery'),
		pageSize:FI.pageSize,
		pageSizeOptions:FI.pageSizeOptions,
		height:'99%',
        width:'100%',
        minColToggle:6,
        rownumbers:true,
        columns: [
	        { display: '消息类型', name: 'msg_type', isSort: false,render:function(rowdata, rowindex, value){
	        	return (value==0)?'手工发送':'系统发送';
	        }},
	        { display: '发送者', name: 'stName', isSort: false},
	        { display: '接收者', name: 'login_name', isSort: false,render:function(rowdata, rowindex, value){
	        	return (value!=null)?value:rowdata['shop_name'];
	        }},
	        { display: '消息内容', name: 'msg_content', isSort: false},
	        { display: '阅读状态', name: 'msg_status', isSort: false,render:function(rowdata, rowindex, value){
	        	return (value==0)?'未读':'已读';
	        }},
	        { display: '有效状态', name: 'status', isSort: false,render:function(rowdata, rowindex, value){
	        	return (value==-1)?'已删除':'有效';
	        }},
	        { display: '发送时间', name: 'create_time', isSort: false},
	        { display: '操作', name: 'op',isSort: false,render: function (rowdata, rowindex, value){
	        	var h="";
	            if(FI.GRANT.SCXX_00)h += "<a href='javascript:showFullMsg(" + rowdata['id'] + ")'>查看</a> ";
	            if(FI.GRANT.SCXX_03)h += "<a href='javascript:toDel(" + rowdata['id'] + ")'>删除</a> "; 
	            return h;
	        }}
        ]
    });
}



function showFullMsg(id){
	parent.showBox({title:'内容详情',type:2,content:FI.U('admin/messages/showFullMsg','id='+id),area: ['800px', '500px'],btn:['关闭']});

}

function toDel(id){
	var box = FI.confirm({content:"您确定要删除该记录吗?",yes:function(){
	           var loading = FI.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
	           	$.post(FI.U('admin/messages/del'),{id:id},function(data,textStatus){
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

//切换卡
$(function (){ 
var flag = true;
h = FI.pageHeight();
$('.l-tab-content').height(h-32);
$('.l-tab-content-item').height(h-32);
$('.l-tab-content-item').css('overflow-y','auto');
tab = $("#fi-tabs").ligerTab({
         height: '99%',
         changeHeightOnResize:true,
         showSwitchInTab : false,
         showSwitch: false,
         onAfterSelectTabItem:function(n){
           if(n=='tabitem1'){
              if(flag){
                initGrid();
                flag = false;
              }else{
                grid.reload();
              }
           }
         }
});

//编辑器
KindEditor.ready(function(K) {
editor1 = K.create('textarea[name="msg_content"]', {
  uploadJson : FI.conf.ROOT+'/admin/messages/editorUpload',
  height:'350px',
  allowFileManager : false,
  allowImageUpload : true,
  items:[
          'source', '|', 'undo', 'redo', '|', 'preview', 'print', 'template', 'code', 'cut', 'copy', 'paste',
          'plainpaste', 'wordpaste', '|', 'justifyleft', 'justifycenter', 'justifyright',
          'justifyfull', 'insertorderedlist', 'insertunorderedlist', 'indent', 'outdent', 'subscript',
          'superscript', 'clearhtml', 'quickformat', 'selectall', '|', 'fullscreen', '/',
          'formatblock', 'fontname', 'fontsize', '|', 'forecolor', 'hilitecolor', 'bold',
          'italic', 'underline', 'strikethrough', 'lineheight', 'removeformat', '|','image','table', 'hr', 'emoticons', 'baidumap', 'pagebreak',
          'anchor', 'link', 'unlink', '|', 'about'
  ],
  afterBlur: function(){ this.sync(); }
});
});






});


function sendToTheUser(t){
        if($('#theUser').prop('checked')){
          $('#user_query').show();
          $('#send_to').show();
        }else{
          $('#user_query').hide();
          $('#send_to').hide();
        }
        
     }
     //账号模糊查找
     function userQuery(){
      var key = $('#login_name').val();
      var html = '';
      $.post(FI.U('admin/messages/userQuery'),{'login_name':key},function(text,dataStatus){
          $(text).each(function(k,v){
            html += '<option value="'+v.user_id+'">'+v.login_name+'</option>';
          });
          $('#ltarget').html(html);
      });
      
     }
     //发送消息
     function sendMsg(){
        var params = FI.getParams('.ipt');
        var loading = FI.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
        $.post(FI.U('admin/messages/add'),params,function(data,textStatus){
          layer.close(loading);
          var json = FI.toAdminJson(data);
          if(json.status=='1'){
              FI.msg("操作成功",{icon:1});
              $('#ltarget').html('');
              $('#rtarget').html('');
              $('#login_name').val('');
              editor1.html('');

          }else{
                FI.msg(json.msg,{icon:2});
          }
        });
     }


function msgQuery(){
    var query = FI.getParams('.query');
      grid.set('url',FI.U('admin/messages/pageQuery',query));
  }



		