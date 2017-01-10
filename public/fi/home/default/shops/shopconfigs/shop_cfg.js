function save(){
/* 表单验证 */
$('#shopCfg').validator({
            fields: {
                shop_keywords: {
                  rule:"required",
                  msg:{required:"请输入关键字"},
                  tip:"请输入关键字",
                },
                
            },

          valid: function(form){
            var params = FI.getParams('.ipt');
            // 图片路径
            var shop_ads = [];
            $('.j-gallery-img').each(function(){
              shop_ads.push($(this).attr('v'));
            });
            params.shop_ads = shop_ads.join(',');
            // 图片轮播广告路径
            var shop_ads_url = [];
            $('.cfg-img-url').each(function(){
              shop_ads_url.push($(this).val());
            });
            params.shop_ads_url = shop_ads_url.join(',');

            var loading = FI.msg('正在提交数据，请稍后...', {icon: 16,time:60000});

            $.post(FI.U('home/shopconfigs/editShopCfg'),params,function(data,textStatus){
              layer.close(loading);
              var json = FI.toJson(data);
              if(json.status=='1'){
                  FI.msg("操作成功",{icon:1});
              }else{
                    FI.msg(json.msg,{icon:2});
              }
            });

      }

    });




}





$(function(){
  //店铺顶部广告图上传
  FI.upload({
      pick:'#shop_bannerPicker',
      formData: {dir:'shopconfigs'},
      accept: {extensions: 'gif,jpg,jpeg,bmp,png',mimeTypes: 'image/*'},
      callback:function(f){
        var json = FI.toJson(f);
        if(json.status==1){
          $('#uploadMsg').empty().hide();
          var shopbanner = json.savePath+json.thumb; //保存到数据库的路径
          $('#shop_banner').val(shopbanner);
          $('#shop_bannerPreview').parent().show();
          $('.del-banner').show();
          $('#shop_bannerPreview').attr('src',FI.conf.ROOT+'/'+json.savePath+json.thumb);
        }else{
            FI.msg(json.msg,{icon:2});
        }
    },
    progress:function(rate){
        $('#uploadMsg').show().html('已上传'+rate+"%");
    }
  });


/********** 轮播广告图片上传 **********/
var uploader = batchUpload({uploadPicker:'#batchUpload',uploadServer:FI.U('home/index/uploadPic'),formData:{dir:'shopconfigs'},uploadSuccess:function(file,response){
        var json = FI.toJson(response);
        if(json.status==1){
          $li = $('#'+file.id);
          $li.append('<input type="hidden" class="j-gallery-img" iv="'+json.savePath + json.thumb+'" v="' +json.savePath + json.name+'"/>');
          var delBtn = $('<span class="btn-del">删除</span>');
          $li.append(delBtn);
          $li.append('<input class="cfg-img-url" type="text" value="" style="width:170px;" placeholder="广告路径">' );
          $li.css('height','212px');
          $li.find('.success').remove();
                  delBtn.on('click',function(){
                      delBatchUploadImg($(this),function(){
                      uploader.removeFile(file);
                      uploader.refresh();
                    });
            });
                  $('.filelist li').css('border','1px solid #f7375c');
        }else{
          FI.msg(json.msg,{icon:2});
        }
      }});
// 删除广告图片
$('.btn-del').click(function(){
      delBatchUploadImg($(this),function(){
            $(this).parent().remove();
          });
    })

function delBatchUploadImg(obj){
  var c = FI.confirm({content:'您确定要删除商品图片吗?',yes:function(){
    $(obj).parent().remove("li");
    layer.close(c);
  }});
}


});