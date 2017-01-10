/**删除批量上传的图片**/
function delBatchUploadImg(obj){
	var c = FI.confirm({content:'您确定要删除商品图片吗?',yes:function(){
		$(obj).parent().remove("li");
		layer.close(c);
	}});
}
function lastGoodsCatCallback(opts){
	if(opts.isLast){
	    getSpecAttrs(opts.val);
	}else{
		$('#specsAttrBox').empty();
	}
}
/**初始化**/
function initEdit(){
	$('#tab').TabPanel({tab:0,callback:function(no){
		if(no==1){
			$('.j-specImg').children().each(function(){
				if(!$(this).hasClass('webuploader-pick'))$(this).css({width:'80px',height:'25px'});
			});
		}
		if(!initBatchUpload && no==2){
			initBatchUpload = true;
			var uploader = batchUpload({uploadPicker:'#batchUpload',uploadServer:FI.U('home/index/uploadPic'),formData:{dir:'goods',isWatermark:1,isThumb:1},uploadSuccess:function(file,response){
				var json = FI.toJson(response);
				if(json.status==1){
					$li = $('#'+file.id);
					$li.append('<input type="hidden" class="j-gallery-img" iv="'+json.savePath + json.thumb+'" v="' +json.savePath + json.name+'"/>');
					//$li.append('<span class="btn-setDefault">默认</span>' );
	                var delBtn = $('<span class="btn-del">删除</span>');
	                $li.append(delBtn);
	                delBtn.on('click',function(){
	                	delBatchUploadImg($(this),function(){
	                		uploader.removeFile(file);
	        				uploader.refresh();
	                	});
	    			});
	                $('.filelist li').css('border','1px solid rgb(59, 114, 165)');
				}else{
					FI.msg(json.msg,{icon:2});
				}
			}});
		}
		$('.btn-del').click(function(){
			delBatchUploadImg($(this),function(){
        		$(this).parent().remove();
        	});
		})
	}});
	FI.upload({
	  	  pick:'#goods_imgPicker',
	  	  formData: {dir:'goods',isWatermark:1,isThumb:1},
	  	  accept: {extensions: 'gif,jpg,jpeg,bmp,png',mimeTypes: 'image/*'},
	  	  callback:function(f){
	  		  var json = FI.toJson(f);
	  		  if(json.status==1){
	  			  $('#uploadMsg').empty().hide();
	              $('#preview').attr('src',FI.conf.ROOT+"/"+json.savePath+json.thumb);
	              $('#goods_img').val(json.savePath+json.name);
	              $('#msg_goods_img').hide();
	  		  }
		  },
		  progress:function(rate){
		      $('#uploadMsg').show().html('已上传'+rate+"%");
		  }
	});
	KindEditor.ready(function(K) {
		editor1 = K.create('textarea[name="goods_desc"]', {
		  height:'350px',
		  width:'800px',
		  uploadJson : FI.conf.ROOT+'/home/goods/editorUpload',
		  allowFileManager : false,
		  allowImageUpload : true,
		  items:[
			          'source', '|', 'undo', 'redo', '|', 'preview', 'print', 'template', 'code', 'cut', 'copy', 'paste',
			          'plainpaste', 'wordpaste', '|', 'justifyleft', 'justifycenter', 'justifyright',
			          'justifyfull', 'insertorderedlist', 'insertunorderedlist', 'indent', 'outdent', 'subscript',
			          'superscript', 'clearhtml', 'quickformat', 'selectall', '|', 'fullscreen', '/',
			          'formatblock', 'fontname', 'fontsize', '|', 'forecolor', 'hilitecolor', 'bold',
			          'italic', 'underline', 'strikethrough', 'lineheight', 'removeformat', '|','image','multiimage','table', 'hr', 'emoticons', 'baidumap', 'pagebreak',
			          'anchor', 'link', 'unlink', '|', 'about'
		  ],
		  afterBlur: function(){ this.sync(); }
		});
	});
	if(OBJ.goods_id>0){
		var goods_cat_ids = OBJ.goods_cat_id_path.split('_');
		getBrands('brand_id',goods_cat_ids[0],OBJ.brand_id);
		if(goods_cat_ids.length>1){
			var objId = goods_cat_ids[0];
			$('#cat_0').val(objId);
			var opts = {id:'cat_0',val:goods_cat_ids[0],childIds:goods_cat_ids,className:'j-goodsCats',afterFunc:'lastGoodsCatCallback'}
        	FI.ITSetGoodsCats(opts);
	    }
		getShopsCats('shop_cat_id2',OBJ.shop_cat_id1,OBJ.shop_cat_id2);
	}
	
}
/**获取本店分类**/
function getShopsCats(objId,pVal,objVal){
	$('#'+objId).empty();
	$.post(FI.U('home/shopcats/listQuery'),{parent_id:pVal},function(data,textStatus){
	     var json = FI.toJson(data);
	     var html = [],cat;
	     html.push("<option value='' >-请选择-</option>");
	     if(json.status==1 && json.list){
	    	 json = json.list;
			 for(var i=0;i<json.length;i++){
			     cat = json[i];
			     html.push("<option value='"+cat.cat_id+"' "+((objVal==cat.cat_id)?"selected":"")+">"+cat.cat_name+"</option>");
			 }
	     }
	     $('#'+objId).html(html.join(''));
	});
}
/**获取品牌**/
function getBrands(objId,cat_id,objVal){
	$('#'+objId).empty();
	$.post(FI.U('home/brands/listQuery'),{cat_id:cat_id},function(data,textStatus){
	     var json = FI.toJson(data);
	     var html = [],cat;
	     html.push("<option value='' >-请选择-</option>");
	     if(json.status==1 && json.list){
	    	 json = json.list;
			 for(var i=0;i<json.length;i++){
			     cat = json[i];
			     html.push("<option value='"+cat.brand_id+"' "+((objVal==cat.brand_id)?"selected":"")+">"+cat.brand_name+"</option>");
			 }
	     }
	     $('#'+objId).html(html.join(''));
	});
}
function toEdit(id,src){
	location.href = FI.U('home/goods/edit','id='+id+'&src='+src);
}
/**保存商品数据**/
function save(){
	$('#editform').isValid(function(v){
		if(v){
			var params = FI.getParams('.j-ipt');
			params.goods_cat_id = FI.ITGetGoodsCatVal('j-goodsCats');
			params.specNum = specNum;
			var specsName,specImg;
			$('.j-speccat').each(function(){
				specsName = 'specName_'+$(this).attr('cat')+'_'+$(this).attr('num');
				specImg = 'specImg_'+$(this).attr('cat')+'_'+$(this).attr('num');
				if($(this)[0].checked){
					params[specsName] = $.trim($('#'+specsName).val());
					params[specImg] = $.trim($('#'+specImg).attr('v'));
				}
			});
			var gallery = [];
			$('.j-gallery-img').each(function(){
				gallery.push($(this).attr('v'));
			});
			params.gallery = gallery.join(',');
			var specsIds = [];
			var specidsmap = [];
			$('.j-ws').each(function(){
				specsIds.push($(this).attr('v'));
				specidsmap.push(FI.blank($(this).attr('sid'))+":"+$(this).attr('v'));
			});
			var specmap = [];
			for(var key in id2SepcNumConverter){
				specmap.push(key+":"+id2SepcNumConverter[key]);
			}
			params.specsIds = specsIds.join(',');
			params.specidsmap = specidsmap.join(',');
			params.specmap = specmap.join(',');
			var loading = FI.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
		    $.post(FI.U('home/goods/'+((params.goods_id==0)?"toAdd":"toEdit")),params,function(data,textStatus){
		    	layer.close(loading);
		    	var json = FI.toJson(data);
		    	if(json.status=='1'){
		    		FI.msg(json.msg,{icon:1});
		    		location.href=FI.U('home/goods/'+src);
		    	}else{
		    		FI.msg(json.msg,{icon:2});
		    	}
		    });
		}
	});
}
var id2SepcNumConverter = {};
/**添加普通规格值**/
function addSpec(opts){
	var html = [];
	html.push('<div class="spec-item">',
	          '<input type="checkbox" class="j-speccat j-speccat_'+opts.cat_id+' j-spec_'+opts.cat_id+'_'+specNum+'" cat="'+opts.cat_id+'" num="'+specNum+'" onclick="javascript:addSpecSaleCol()" '+opts.checked+'/>',
	          '<input type="text" class="spec-ipt" id="specName_'+opts.cat_id+'_'+specNum+'" maxLength="50" value="'+FI.blank(opts.val)+'" onblur="batchChangeTxt(this.value,'+opts.cat_id+','+specNum+')"/>',
	          '<span class="item-del" onclick="delSpec(this,'+opts.cat_id+','+specNum+')"></span>',
	          '</div>');
	$(html.join('')).insertBefore('#specAddBtn_'+opts.cat_id);
	if(opts.item_id){
		id2SepcNumConverter[opts.item_id] = opts.cat_id+'_'+specNum;
	}
	
	specNum++;	
}
/**删除普通规格值**/
function delSpec(obj,cat_id,num){
	if($('.j-spec_'+cat_id+'_'+num)[0].checked){
		$('.j-spec_'+cat_id+'_'+num)[0].checked = false;
		addSpecSaleCol();
	}
	$(obj).parent().remove();
}
/**添加带图片的规格值**/
function addSpecImg(opts){
	var html = [];
	html.push('<tr>',
			    '<td>',
	            '<input type="checkbox" class="j-speccat j-speccat_'+opts.cat_id+' j-spec_'+opts.cat_id+'_'+specNum+'" cat="'+opts.cat_id+'" num="'+specNum+'" onclick="javascript:addSpecSaleCol()" '+opts.checked+'/>',
                '<input type="text" id="specName_'+opts.cat_id+'_'+specNum+'" maxLength="50" value="'+FI.blank(opts.val)+'" onblur="batchChangeTxt(this.value,'+opts.cat_id+','+specNum+')"/>',
                '</td>',
	            '<td id="uploadMsg_'+opts.cat_id+'_'+specNum+'">',
	            (opts.specImg)?'<img height="25"  width="25" id="specImg_'+opts.cat_id+'_'+specNum+'" src="'+FI.conf.ROOT+"/"+opts.specImg+'" v="'+opts.specImg+'"/>':"",
	            '</td><td><div id="specImgPicker_'+specNum+'" class="j-specImg">上传图片</div></td>'
	         );
	if($('#specTby').children().size()==0){
    	html.push('<td><input type="button" id="specImgBtn" value="新增" onclick="addSpecImg({cat_id:'+opts.cat_id+',checked:\'\'})"/></td>');
    }else{
    	html.push('<td><input type="button" id="specImgBtn" value="删除" onclick="delSpecImg(this,'+opts.cat_id+','+specNum+')"/></td>');
    }
    html.push('</tr>');
	$('#specTby').append(html.join(''));
	FI.upload({
		  num:specNum,
		  cat:opts.cat_id,
	  	  pick:'#specImgPicker_'+specNum,
	  	  formData: {dir:'goods',isThumb:1},
	  	  accept: {extensions: 'gif,jpg,jpeg,bmp,png',mimeTypes: 'image/*'},
	  	  callback:function(f){
	  		  var json = FI.toJson(f);
	  		  if(json.status==1){
	  			$('#uploadMsg_'+this.cat+"_"+this.num).html('<img id="specImg_'+this.cat+"_"+this.num+'" v="'+json.savePath+json.thumb+'" src="'+FI.conf.ROOT+"/"+json.savePath+json.thumb+'" height="25"  width="25"/>');
	  		  }
		  },
		  progress:function(rate){
		      $('#uploadMsg_'+this.cat+"_"+this.num).html('已上传'+rate+"%");
		  }
	});
	if(opts.item_id){
		id2SepcNumConverter[opts.item_id] = opts.cat_id+'_'+specNum;
	}
	specNum++;
}
/**删除带图片的规格值**/
function delSpecImg(obj,cat_id,num){
	if($('.j-spec_'+cat_id+'_'+num)[0].checked){
		$('.j-spec_'+cat_id+'_'+num)[0].checked = false;
		addSpecSaleCol();
	}
	$(obj).parent().parent().remove();
}
/**给销售规格表填上值**/
function fillSepcSale(){
	var ids = '',tmpids = [];
	for(var i=0;i<OBJ.saleSpec.length;i++){
		tmpids = [];
		ids = OBJ.saleSpec[i].spec_ids;
		ids = ids.split(':');
		for(var j=0;j<ids.length;j++){
			tmpids.push(id2SepcNumConverter[ids[j]]);
		}
		tmpids = tmpids.join('-');
		if(OBJ.saleSpec[i].is_default)$('#is_default_'+tmpids).attr('checked',true);
		$('#product_no_'+tmpids).val(OBJ.saleSpec[i].product_no);
		$('#market_price_'+tmpids).val(OBJ.saleSpec[i].market_price);
		$('#spec_price_'+tmpids).val(OBJ.saleSpec[i].spec_price);
		$('#spec_stock_'+tmpids).val(OBJ.saleSpec[i].spec_stock);
		$('#warn_stock_'+tmpids).val(OBJ.saleSpec[i].warn_stock);
		$('#sale_num_'+tmpids).val(OBJ.saleSpec[i].sale_num);
		$('#sale_num_'+tmpids).attr('sid',OBJ.saleSpec[i].id);
	}
}
/**生成销售规格表**/
function addSpecSaleCol(){
	//获取规格分类和规格值
	var cat_id,snum,specCols = {},obj = [];
	$('.j-speccat').each(function(){
		if($(this)[0].checked){
			cat_id = $(this).attr('cat');
			snum = $(this).attr('num');
			if(!specCols[cat_id]){
				specCols[cat_id] = [];
				specCols[cat_id].push({id:cat_id+"_"+snum,val:$.trim($('#specName_'+cat_id+"_"+snum).val())});
			}else{
				specCols[cat_id].push({id:cat_id+"_"+snum,val:$.trim($('#specName_'+cat_id+"_"+snum).val())});
			}
	    }
	});
	//创建表头
	$('.j-saleTd').remove();
	var html = [],specArray = [];;
	for(var key in specCols){
		html.push('<th class="j-saleTd">'+$('#specCat_'+key).html()+'</th>');
		specArray.push(specCols[key]);
	}
	if(html.length==0)return;
	$(html.join('')).insertBefore('#thCol');
	//组合规格值
	this.combined = function(doubleArrays){
        var len = doubleArrays.length;
        if (len >= 2) {
            var arr1 = doubleArrays[0];
            var arr2 = doubleArrays[1];
            var len1 = doubleArrays[0].length;
            var len2 = doubleArrays[1].length;
            var newlen = len1 * len2;
            var temp = new Array(newlen),ntemp;
            var index = 0;
            for (var i = 0; i < len1; i++) {
            	if(arr1[i].length){
            		for (var k = 0; k < len2; k++) {
            			ntemp = arr1[i].slice();
            			ntemp.push(arr2[k]);
		                temp[index] = ntemp;
		                index++;
            		}
            	}else{
	                for (var j = 0; j < len2; j++) {
	                    temp[index] = [arr1[i],arr2[j]];
	                    index++;
	                }
            	}
            }
            var newArray = new Array(len - 1);
            newArray[0] = temp;
            if (len > 2) {
                var _count = 1;
                for (var i = 2; i < len; i++) {
                    newArray[_count] = doubleArrays[i];
                    _count++;
                }
            }
            return this.combined(newArray);
        }else {
            return doubleArrays[0];
        }
    }
	
	var specsRows = this.combined(specArray);
	//生成规格值表
	html = [];
	var id=[],key=1,specHtml = [];
	var product_no = $('#product_no').val(),specProductNo = '';
	for(var i=0;i<specsRows.length;i++){
		id = [],specHtml = [];
		html.push('<tr class="j-saleTd">');
		
		if(specsRows[i].length){
			for(var j=0;j<specsRows[i].length;j++){
				id.push(specsRows[i][j].id);
				specHtml.push('<td class="j-td_'+specsRows[i][j].id+'">' + specsRows[i][j].val + '</td>');
	        }
		}else{
			id.push(specsRows[i].id);
			specHtml.push('<td>' + specsRows[i].val + '</td>');
		}
		id = id.join('-');
		if(OBJ.goods_id==0){
			specProductNo = product_no+'-'+key;
		}
		html.push('  <td><input type="radio" id="is_default_'+id+'" name="defaultSpec" class="j-ipt" value="'+id+'"/></td>');
		html.push(specHtml.join(''));
		html.push('  <td><input type="text" class="spec-sale-goodsNo j-ipt" id="product_no_'+id+'" value="'+specProductNo+'" onblur="checkProductNo(this)"></td>',
	              '  <td><input type="text" class="spec-sale-ipt j-ipt" id="market_price_'+id+'"></td>',
	              '  <td><input type="text" class="spec-sale-ipt j-ipt" id="spec_price_'+id+'"></td>',
	              '  <td><input type="text" class="spec-sale-ipt j-ipt" id="spec_stock_'+id+'"></td>',
	              '  <td><input type="text" class="spec-sale-ipt j-ipt" id="warn_stock_'+id+'"></td>',
	              '  <td class="j-ws" v="'+id+'" id="sale_num_'+id+'">0</td>',
	              '</tr>');
		key++;
	}
	$('#spec-sale-tby').append(html.join(''));
	//判断是否禁用商品价格和库存字段
	if($('#spec-sale-tby').html()!=''){
		$('#goods_stock').attr('disalbed',true);
		$('#shop_price').attr('disalbed',true);
		$('#market_price').attr('disalbed',true);
	}else{
		$('#goods_stock').attr('disalbed',false);
		$('#shop_price').attr('disalbed',false);
		$('#market_price').attr('disalbed',false);
	}
	//设置销售规格表值
	if(OBJ.saleSpec)fillSepcSale();
}
/**根据批量修改销售规格值**/
function batchChange(v,id){
	if($.trim(v)!=''){
		$('input[type=text][id^="'+id+'_"]').val(v);
	}
}
/**根据规格值修改 销售规格表 里的值**/
function batchChangeTxt(v,cat_id,num){
	$('.j-td_'+cat_id+"_"+num).each(function(){
		$(this).html(v);
	});
}
/**检测商品销售规格值是否重复**/
function checkProductNo(obj){
	v = $.trim(obj.value);
	var num = 0;
	$('input[type=text][id^="product_no_"]').each(function(){
		if(v==$.trim($(this).val()))num++;
	});
	if(num>1){
		FI.msg('已存在相同的货号',{icon:2});
		obj.value = '';
	}
}
/**获取商品规格和属性**/
function getSpecAttrs(goods_cat_id){
	$('#specsAttrBox').empty();
	specNum = 0;
	$.post(FI.U('home/goods/getSpecAttrs'),{goods_cat_id:goods_cat_id},function(data,textStatus){
		var json = FI.toJson(data);
		if(json.status==1 && json.data){
			var html = [],tmp,str;
			if(json.data.spec0 || json.data.spec1){
				html.push('<div class="spec-head">商品规格</div>');
				html.push('<div class="spec-body">');
				if(json.data.spec0){
					tmp = json.data.spec0;
					html.push('<div id="specCat_'+tmp.cat_id+'">'+tmp.cat_name+'</div>');
					html.push('<table><tbody id="specTby"></tbody></table>');
				}
				if(json.data.spec1){
					for(var i=0;i<json.data.spec1.length;i++){
						tmp = json.data.spec1[i];
						html.push('<div class="spec-line"></div>',
						          '<div id="specCat_'+tmp.cat_id+'">'+tmp.cat_name+'</div>',
						          '<div>',
						          '<input type="button" value="新增" id="specAddBtn_'+tmp.cat_id+'" onclick="javascript:addSpec({cat_id:'+tmp.cat_id+',checked:\'\'})"/>',
						          '</div>'
								);
					}
				}
				html.push('</div>');
				html.push('<div id="specSaleHead" class="spec-head">销售规格</div>',
				          '<table class="specs-sale-table">',
				          '  <thead id="spec-sale-hed">',
				          '   <tr>',
				          '     <th>推荐<br/>规格</th>',
				          '     <th id="thCol"><font color="red">*</font>货号</th>',
				          '     <th><font color="red">*</font>市场价<br/><input type="text" class="spec-sale-ipt" onblur="batchChange(this.value,\'market_price\')"></th>',
				          '     <th><font color="red">*</font>本店价<br/><input type="text" class="spec-sale-ipt" onblur="batchChange(this.value,\'spec_price\')"></th>',
				          '     <th><font color="red">*</font>库存<br/><input type="text" class="spec-sale-ipt" onblur="batchChange(this.value,\'spec_stock\')"></th>',
				          '     <th><font color="red">*</font>预警库存<br/><input type="text" class="spec-sale-ipt" onblur="batchChange(this.value,\'warn_stock\')"></th>',
				          '     <th>销量</th>',
				          '   </tr>',
				          '  </thead>',
				          '  <tbody id="spec-sale-tby"></tbody></table>'
						);
			}
			if(json.data.attrs){
				html.push('<div class="spec-head">商品属性</div>');
				html.push('<div class="spec-body">');
				html.push('<table class="attr-table">');
				for(var i=0;i<json.data.attrs.length;i++){
					tmp = json.data.attrs[i];
					html.push('<tr><th width="120" nowrap>'+tmp.attr_name+'：</th><td>');
					if(tmp.attr_type==1){		
						str = tmp.attr_val.split(',');
						for(var j=0;j<str.length;j++){
						    html.push('<label><input type="checkbox" class="j-ipt" name="attr_'+tmp.attr_id+'" value="'+str[j]+'"/>'+str[j]+'</label>');
						}
					}else if(tmp.attr_type==2){
						html.push('<select name="attr_'+tmp.attr_id+'" id="attr_'+tmp.attr_id+'" class="j-ipt">');
						html.push('<option value="0">请选择</option>');
						str = tmp.attr_val.split(',');
						for(var j=0;j<str.length;j++){
							html.push('<option value="'+str[j]+'">'+str[j]+'</option>');
						}
						html.push('</select>');
					}else{
						html.push('<input type="text" name="attr_'+tmp.attr_id+'" id="attr_'+tmp.attr_id+'" class="spec-sale-text j-ipt"/>');
					}
					html.push('</td></tr>');
				}
				html.push('</table>');
				html.push('</div>');
			}
			$('#specsAttrBox').html(html.join(''));
			//如果是编辑的话，第一次要设置之前设置的值
			if(OBJ.goods_id>0 && specNum==0){
				//设置规格值
				if(OBJ.spec0){
					for(var i=0;i<OBJ.spec0.length;i++){
					   addSpecImg({cat_id:OBJ.spec0[i].cat_id,checked:'checked',val:OBJ.spec0[i].item_name,item_id:OBJ.spec0[i].item_id,specImg:OBJ.spec0[i].item_img});
					}
				}
				if(OBJ.spec1){
					for(var i=0;i<OBJ.spec1.length;i++){
					    addSpec({cat_id:OBJ.spec1[i].cat_id,checked:'checked',val:OBJ.spec1[i].item_name,item_id:OBJ.spec1[i].item_id});
					}
				}
				addSpecSaleCol();
				//设置商品属性值
				var tmp = null;
				if(OBJ.attrs.length){
					for(var i=0;i<OBJ.attrs.length;i++){
						if(OBJ.attrs[i].attr_type==1){
							tmp = OBJ.attrs[i].attr_val.split(',');
							FI.setValue("attr_"+OBJ.attrs[i].attr_id,tmp);
						}else{
						    FI.setValue("attr_"+OBJ.attrs[i].attr_id,OBJ.attrs[i].attr_val);
						}
					}
				}
				
			}
			//给没有初始化的规格初始化一个输入框
			if(json.data.spec0 && !$('.j-speccat_'+json.data.spec0.cat_id)[0]){
				addSpecImg({cat_id:json.data.spec0.cat_id,checked:''});
			}
			if(json.data.spec1){
				for(var i=0;i<json.data.spec1.length;i++){
					if(!$('.j-speccat_'+json.data.spec1[i].cat_id)[0])addSpec({cat_id:json.data.spec1[i].cat_id,checked:''});
				}
			}
			
		}
	});
}

function saleByPage(p){
	$('#list').html('<tr><td colspan="11"><img src="'+FI.conf.ROOT+'/fi/home/default/img/loading.gif">正在加载数据...</td></tr>');
	var params = {};
	params = FI.getParams('.s-query');
	params.key = $.trim($('#key').val());
	params.page = p;
	$.post(FI.U('home/goods/saleByPage'),params,function(data,textStatus){
	    var json = FI.toJson(data);
	    if(json.status==1 && json.Rows){
	       	var gettpl = document.getElementById('tblist').innerHTML;
	       	laytpl(gettpl).render(json.Rows, function(html){
	       		$('#list').html(html);
	       		$('.j-lazyGoodsImg').lazyload({ effect: "fadeIn",failurelimit : 10,skip_invisible : false,threshold: 200,placeholder:window.conf.ROOT+'/'+window.conf.GOODS_LOGO});//商品默认图片
	       	});
	       	if(json.Total>1){
	       		laypage({
		        	 cont: 'pager', 
		        	 pages:json.TotalPage, 
		        	 curr: json.CurrentPage,
		        	 skin: '#e23e3d',
		        	 groups: 3,
		        	 jump: function(e, first){
		        		    if(!first){
		        		    	saleByPage(e.curr);
		        		    }
		        	    } 
		        });
	       	}else{
	       		$('#pager').empty();
	       	}
       	}  
	});
}
function auditByPage(p){
	$('#list').html('<tr><td colspan="11"><img src="'+FI.conf.ROOT+'/fi/home/default/img/loading.gif">正在加载数据...</td></tr>');
	var params = {};
	params = FI.getParams('.s-query');
	params.key = $.trim($('#key').val());
	params.page = p;
	$.post(FI.U('home/goods/auditByPage'),params,function(data,textStatus){
	    var json = FI.toJson(data);
	    if(json.status==1 && json.Rows){
	       	var gettpl = document.getElementById('tblist').innerHTML;
	       	laytpl(gettpl).render(json.Rows, function(html){
	       		$('#list').html(html);
	       		$('.j-lazyGoodsImg').lazyload({ effect: "fadeIn",failurelimit : 10,skip_invisible : false,threshold: 200,placeholder:window.conf.ROOT+'/'+window.conf.GOODS_LOGO});//商品默认图片
	       	});
	       	if(json.Total>1){
	       		laypage({
		        	 cont: 'pager', 
		        	 pages:json.TotalPage, 
		        	 curr: json.CurrentPage,
		        	 skin: '#e23e3d',
		        	 groups: 3,
		        	 jump: function(e, first){
		        		 if(!first){
		        		      saleByPage(e.curr);
		        		 }
		        	 } 
		        });
	       	}else{
	       		$('#pager').empty();
	       	}
       	}  
	});
}
function storeByPage(p){
	$('#list1').html('<tr><td colspan="11"><img src="'+FI.conf.ROOT+'/fi/home/default/img/loading.gif">正在加载数据...</td></tr>');
	var params = {};
	params = FI.getParams('.s-query');
	params.key = $.trim($('#key1').val());
	params.page = p;
	$.post(FI.U('home/goods/storeByPage'),params,function(data,textStatus){
	    var json = FI.toJson(data);
	    if(json.status==1 && json.Rows){
	       	var gettpl = document.getElementById('tblist1').innerHTML;
	       	laytpl(gettpl).render(json.Rows, function(html){
	       		$('#list1').html(html);
	       		$('.j-lazyGoodsImg').lazyload({ effect: "fadeIn",failurelimit : 10,skip_invisible : false,threshold: 200,placeholder:window.conf.ROOT+'/'+window.conf.GOODS_LOGO});//商品默认图片
	       	});
	       	if(json.Total>1){
	       		laypage({
		        	 cont: 'pager1', 
		        	 pages:json.TotalPage, 
		        	 curr: json.CurrentPage,
		        	 skin: '#e23e3d',
		        	 groups: 3,
		        	 jump: function(e, first){
		        		 if(!first){
		        			 storeByPage(e.curr);
		        		 }
		        	 } 
		        });
	       	}else{
	       		$('#pager1').empty();
	       	}
       	}  
	});
}
function illegalByPage(p){
	$('#list2').html('<tr><td colspan="4"><img src="'+FI.conf.ROOT+'/fi/home/default/img/loading.gif">正在加载数据...</td></tr>');
	var params = {};
	params = FI.getParams('.s-query');
	params.key = $.trim($('#key2').val());
	params.page = p;
	$.post(FI.U('home/goods/illegalByPage'),params,function(data,textStatus){
	    var json = FI.toJson(data);
	    if(json.status==1 && json.Rows){
	       	var gettpl = document.getElementById('tblist2').innerHTML;
	       	laytpl(gettpl).render(json.Rows, function(html){
	       		$('#list2').html(html);
	       		$('.j-lazyGoodsImg').lazyload({ effect: "fadeIn",failurelimit : 10,skip_invisible : false,threshold: 200,placeholder:window.conf.ROOT+'/'+FI.conf.GOODS_LOGO});
		    });
	       	if(json.Total>1){
	       		laypage({
		        	 cont: 'pager2', 
		        	 pages:json.TotalPage, 
		        	 curr: json.CurrentPage,
		        	 skin: '#e23e3d',
		        	 groups: 3,
		        	 jump: function(e, first){
		        		 if(!first){
		        		      saleByPage(e.curr);
		        		 }
		        	 } 
		        });
	       	}else{
	       		$('#pager2').empty();
	       	}
       	}  
	});
}
function del(id,func){
	var c = FI.confirm({content:'您确定要删除商品吗?',yes:function(){
		layer.close(c);
		var load = FI.load({msg:'正在删除，请稍后...'});
		$.post(FI.U('home/goods/del'),{id:id},function(data,textStatus){
			layer.close(load);
		    var json = FI.toJson(data);
		    if(json.status==1){
		    	switch(func){
		    	   case 'store':storeByPage(0);break;
		    	   case 'sale':saleByPage(0);break;
		    	   case 'audit':auditByPage(0);break;
		    	   case 'illegal':illegalByPage(0);break;
		    	}
		    }else{
		    	FI.msg(json.msg,{icon:2});
		    }
		});
	}});
}

// 批量 上架/下架
function changeSale(i,func){
	var ids = FI.getChks('.chk');
	if(ids==''){
		FI.msg('请先选择商品!', {icon: 5});
		return;
	}
	var params = {};
	params.ids = ids;
	params.is_sale = i;
	$.post(FI.U('home/goods/changeSale'), params, function(data,textStatus){
		var json = FI.toJson(data);
		if(json.status=='1'){
			FI.msg('操作成功',{icon:1},(function(){
			   $('#all').prop('checked',false);
			   switch(func){
	    	       case 'store':storeByPage(0);break;
	    	       case 'sale':saleByPage(0);break;
	    	       case 'audit':auditByPage(0);break;
	    	  }
			}));
	    }else if(json.status=='-2'){
	    	FI.msg(json.msg, {icon: 5});
	    }else if(json.status=='2'){
	    	FI.msg(json.msg, {icon: 5},function(){
	    		switch(func){
		    	   case 'store':storeByPage(0);break;
		    	   case 'sale':saleByPage(0);break;
		    	   case 'audit':auditByPage(0);break;
		    	}
	    	});
	    }else if(json.status=='-3'){
	    	FI.msg(json.msg, {icon: 5,time:3000});
	    }else{
	    	FI.msg('操作失败!', {icon: 5});
	    }
	});
}

// 批量设置 精品/新品/推荐/热销
function changeGoodsStatus(isWhat,func){
	var ids = FI.getChks('.chk');
	if(ids==''){
		FI.msg('请先选择商品!', {icon: 5});
		return;
	}
	var params = {};
	params.ids = ids;
	params.is = isWhat;
	$.post(FI.U('home/goods/changeGoodsStatus'),params,function(data,textStatus){
		var json = FI.toJson(data);
		if(json.status=='1'){
			FI.msg('设置成功',{icon:1},function(){
				   $('#all').prop('checked',false);
				   switch(func){
		    	   case 'store':storeByPage(0);break;
		    	   case 'sale':saleByPage(0);break;
		    	   case 'audit':auditByPage(0);break;
		    	  }
			});
		}else{
			FI.msg('设置失败',{icon:5});
		}
	});
}

// 双击设置 
function changSaleStatus(isWhat, obj, id){
	var params = {};
	status = $(obj).attr('status');
	params.status = status;
	params.id = id;
	switch(isWhat){
	   case 'r':params.is = "is_recom";break;
	   case 'b':params.is = "is_best";break;
	   case 'n':params.is = "is_new";break;
	   case 'h':params.is = "is_hot";break;
	}
	var load = FI.load({msg:'请稍后...'});
	$.post(FI.U('home/goods/changSaleStatus'),params,function(data,textStatus){
		layer.close(load);
		var json = FI.toJson(data);
		if(json.status==1){
			if(status==0){
				$(obj).attr('status',1);
				$(obj).removeClass('wrong').addClass('right');
			}else{
				$(obj).attr('status',0);
				$(obj).removeClass('right').addClass('wrong');
			}
		}else{
			FI.msg('操作失败',{icon:5});
		}
	});
}

//双击修改
function toEditGoodsBase(fv,goods_id,flag){	
	if((fv==2 || fv==3) && flag==1){
		FI.msg('该商品存在商品属性，不能直接修改，请进入编辑页修改', {icon: 5});
		return;
	}else{
		$("#ipt_"+fv+"_"+goods_id).show();
		$("#span_"+fv+"_"+goods_id).hide();
		$("#ipt_"+fv+"_"+goods_id).focus();
		$("#ipt_"+fv+"_"+goods_id).val($("#span_"+fv+"_"+goods_id).html());
	}
	
}
function endEditGoodsBase(fv,goods_id){
	$('#span_'+fv+'_'+goods_id).html($('#ipt_'+fv+'_'+goods_id).val());
	$('#span_'+fv+'_'+goods_id).show();
    $('#ipt_'+fv+'_'+goods_id).hide();
}
function editGoodsBase(fv,goods_id){

	var vtext = $('#ipt_'+fv+'_'+goods_id).val();
	if($.trim(vtext)==''){
		if(fv==2){
			FI.msg('价格不能为空', {icon: 5});
		}else if(fv==3){
			FI.msg('库存不能为空', {icon: 5});
		}		
        return;
	}
	var params = {};
	(fv==2)?params.shop_price=vtext:params.goods_stock=vtext;
	params.goods_id = goods_id;
	$.post(FI.U('Home/Goods/editGoodsBase'),params,function(data,textStatus){
		var json = FI.toJson(data);
		if(json.status>0){
			$('#img_'+fv+'_'+goods_id).fadeTo("fast",100);
			endEditGoodsBase(fv,goods_id);
			$('#img_'+fv+'_'+goods_id).fadeTo("slow",0);
		}else{
			FI.msg('修改失败!', {icon: 5}); 
		}
	});
}

function benchDel(func,flag){
	if(flag==1){
		var ids = FI.getChks('.chk1');
	}else{
		var ids = FI.getChks('.chk');
	}
	
	if(ids==''){
		FI.msg('请先选择商品!', {icon: 5});
		return;
	}
	var params = {};
	params.ids = ids;
	var load = FI.load({msg:'请稍后...'});
	$.post(FI.U('home/goods/batchDel'),params,function(data,textStatus){
		layer.close(load);
		var json = FI.toJson(data);
		if(json.status=='1'){
			FI.msg('操作成功',{icon:1},function(){
				   $('#all').prop('checked',false);
				   switch(func){
		    	   case 'store':storeByPage(0);break;
		    	   case 'sale':saleByPage(0);break;
		    	   case 'audit':auditByPage(0);break;
		    	  }
			});
		}else{
			FI.msg('操作失败',{icon:5});
		}
	});
}

function getCat(val){
  if(val==''){
  	$('#cat2').html("<option value='' >-请选择-</option>");
  	return;
  }
  $.post(FI.U('home/shopcats/listQuery'),{parent_id:val},function(data,textStatus){
       var json = FI.toJson(data);
       var html = [],cat;
       html.push("<option value='' >-请选择-</option>");
       if(json.status==1 && json.list){
         json = json.list;
       for(var i=0;i<json.length;i++){
           cat = json[i];
           html.push("<option value='"+cat.cat_id+"'>"+cat.cat_name+"</option>");
        }
       }
       $('#cat2').html(html.join(''));
  });
}