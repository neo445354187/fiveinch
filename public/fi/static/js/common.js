var FI = FI || {};
FI.v = '1.0.0';
FI.checkBrowser = function(){
	return {
		mozilla : /firefox/.test(navigator.userAgent.toLowerCase()),
		webkit : /webkit/.test(navigator.userAgent.toLowerCase()), 
	    opera : /opera/.test(navigator.userAgent.toLowerCase()), 
	    msie : /msie/.test(navigator.userAgent.toLowerCase())
	}
}
FI.pageHeight = function(){
	if(FI.checkBrowser().msie){ 
		return document.compatMode == "CSS1Compat"? document.documentElement.clientHeight : 
		document.body.clientHeight; 
	}else{ 
		return self.innerHeight; 
	} 
};
//返回当前页面宽度 
FI.pageWidth = function(){ 
	if(FI.checkBrowser().msie){ 
		return document.compatMode == "CSS1Compat"? document.documentElement.clientWidth : 
		document.body.clientWidth; 
	}else{ 
		return self.innerWidth; 
	} 
};
FI.TreeSelector = function(item,data,rootId,defaultValue){ 
    this._data = data; 
    this._item = item; 
    this._rootId = rootId; 
    if(defaultValue)this.defaultValue = defaultValue;
} 
FI.TreeSelector.prototype.createTree = function(){ 
    var len =this._data.length; 
    for( var i= 0;i<len;i++){ 
         if ( this._data[i].pid == this._rootId){ 
              this._item.options.add(new Option(" "+this._data[i].text,this._data[i].id)); 
              for(var j=0;j<len;j++){ 
                   this.createSubOption(len,this._data[i],this._data[j]); 
              } 
         } 
    }
    if(this.defaultValue)this._item.value = this.defaultValue;
} 

FI.TreeSelector.prototype.createSubOption = function(len,current,next){ 
    var blank = ".."; 
    if ( next.pid == current.id){ 
         intLevel =0; 
         var intlvl =this.getLevel(this._data,this._rootId,current); 
         for(a=0;a<intlvl;a++) 
              blank += ".."; 
              blank += "├-"; 
              this._item.options.add(new Option(blank + next.text,next.id)); 
              for(var j=0;j<len;j++){ 
                   this.createSubOption(len,next,this._data[j]); 
              } 
         } 
    } 
    FI.TreeSelector.prototype.getLevel = function(datasources,topId,currentitem){ 

    var pid =currentitem.pid; 
    if( pid !=topId) 
    { 
         for(var i =0 ;i<datasources.length;i++) 
         { 
              if( datasources[i].id == pid) 
              { 
                   intLevel ++; 
                   this.getLevel(datasources,topId,datasources[i]); 
              } 
         } 
    } 
    return intLevel; 
}
//多选下拉框移动元素
FI.multSelect = function(opts){
    var e1 = document.getElementById(opts.left);
    var e2 = document.getElementById(opts.right);
    for(var i=0;i<e1.options.length;i++){
    	if(e1.options[i].selected){
    	    var e = e1.options[i];
    	    e2.options.add(new Option(e.text, e.value));
    	    e1.remove(i);
    	    i=i-1
    	}
    }
    document.getElementById(opts.val).value=getValue(document.getElementById(opts.vtarget));

    function getValue(geto){
        var ids = [];
    	for(var i=0;i<geto.options.length;i++){
    		ids.push(geto.options[i].value);
    	}
    	return ids.join(',');
    }
}
// 只能輸入數字，且第一數字不能為0
FI.digitalOnly = function(obj) {
 	// 先把非数字的都替换掉
 	obj.value=obj.value.replace(/\D/g, "");
}
/**
 * 获取版本
 */
FI.getFIVersion = function(url){
	$.post(url,{},function(data,textStatus){
		var json = {};
		try{
	      if(typeof(data )=="object"){
			json = data;
	      }else{
			json = eval("("+data+")");
	      }
		}catch(e){}
	   if(json){
		   if(json.version && json.version!='same'){
			   $('.fi-version-tips').show();
			   $('#fi_version').html(json.version);
			   $('#fi_down').attr('href',json.downloadUrl);
		   }
		   if(json.accredit=='no'){
			   $('.fi-accredit-tips').show();
		   }
		   if(json.licenseStatus)$('#licenseStatus').html(json.licenseStatus);
	   }
	});
}
 /******************** 
 * 取窗口滚动条高度  
 ******************/  
 FI.getScrollTop = function()  
 {  
     var scrollTop=0;  
     if(document.documentElement&&document.documentElement.scrollTop)  
     {  
         scrollTop=document.documentElement.scrollTop;  
     }  
     else if(document.body)  
     {  
         scrollTop=document.body.scrollTop;  
     }  
     return scrollTop;  
 }  
   
 /******************** 
 * 取文档内容实际高度  
 *******************/  
 FI.getScrollHeight = function()  
 {  
     return Math.max(document.body.scrollHeight,document.documentElement.scrollHeight);  
 }

 //只能輸入數字
 FI.isNumberKey = function(evt){
 	var charCode = (evt.which) ? evt.which : event.keyCode;
 	if (charCode > 31 && (charCode < 48 || charCode > 57)){
 		return false;
 	}else{		
 		return true;
 	}
 }  

 //只能輸入數字和小數點
 FI.isNumberdoteKey = function(evt){
 	var e = evt || window.event; 
 	var srcElement = e.srcElement || e.target;
 	
 	var charCode = (evt.which) ? evt.which : event.keyCode;			
 	if (charCode > 31 && ((charCode < 48 || charCode > 57) && charCode!=46)){
 		return false;
 	}else{
 		if(charCode==46){
 			var s = srcElement.value;			
 			if(s.length==0 || s.indexOf(".")!=-1){
 				return false;
 			}			
 		}		
 		return true;
 	}
 }

 //只能輸入數字和字母
 FI.isNumberCharKey = function(evt){
 	var e = evt || window.event; 
 	var srcElement = e.srcElement || e.target;	
 	var charCode = (evt.which) ? evt.which : event.keyCode;

 	if((charCode>=48 && charCode<=57) || (charCode>=65 && charCode<=90) || (charCode>=97 && charCode<=122) || charCode==8){
 		return true;
 	}else{		
 		return false;
 	}
 }

FI.isChinese = function(obj,isReplace){
 	var pattern = /[\u4E00-\u9FA5]|[\uFE30-\uFFA0]/i
 	if(pattern.test(obj.value)){
 		if(isReplace)obj.value=obj.value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]/ig,"");
 		return true;
 	}
 	return false;
 }   
 
Number.prototype.toFixed = function(exponent){ 
    return parseInt(this * Math.pow(10, exponent)+0.5 )/Math.pow(10,exponent);
}
 
//用户名判断 （可输入"_",".","@", 数字，字母）
 FI.isUserName = function(evt){
 	var evt = evt || window.event; 
 	var charCode = (evt.which) ? evt.which : evt.keyCode;
 	if((charCode==95 || charCode==46 || charCode==64) || (charCode>=48 && charCode<=57) || (charCode>=65 && charCode<=90) || (charCode>=97 && charCode<=122) || charCode==8){
 		return true;
 	}else{		
 		return false;
 	}
 }
 
FI.isEmail =function(v){
		var tel = new RegExp("^\\w+((-\\w+)|(\\.\\w+))*\\@[A-Za-z0-9]+((\\.|-)[A-Za-z0-9]+)*\\.[A-Za-z0-9]+$");
		return(tel.test(v));
}   
//判断是否电话
FI.isTel = function(v){
	 var tel = new RegExp("^[[0-9]{3}-|\[0-9]{4}-]?(\[0-9]{8}|[0-9]{7})?$");
	 return(tel.test(v));
}
FI.isPhone = function(v){
	 var tel = new RegExp("^[1][0-9]{10}$");
	 return(tel.test(v));
}
//判断url
FI.isUrl = function(str){
    if(str==null||str=="") return false;
    var result=str.match(/^http:\/\/[A-Za-z0-9]+\.[A-Za-z0-9]+[\/=\?%\-&_~`@[\]\’:+!]*([^<>\"])*$/);
    if(result==null)return false;
    return true;
}
//比较时间差
FI.getTimeDiff = function(startTime,endTime,diffType){
    //将xxxx-xx-xx的时间格式，转换为 xxxx/xx/xx的格式
    startTime = startTime.replace(/-/g, "/");
    endTime = endTime.replace(/-/g, "/");
    //将计算间隔类性字符转换为小写
    diffType = diffType.toLowerCase();
    var sTime = new Date(startTime); //开始时间
    var eTime = new Date(endTime); //结束时间
    //作为除数的数字
    var divNum = 1;
    switch (diffType) {
        case "second":
             divNum = 1000;
             break;
        case "minute":
             divNum = 1000 * 60;
             break;
        case "hour":
             divNum = 1000 * 3600;
             break;
        case "day":
             divNum = 1000 * 3600 * 24;
             break;
        default:
             break;
     }
     return parseInt((eTime.getTime() - sTime.getTime()) / parseInt(divNum));
}
/**
 * 截取字符串
 * is_mark 判断是否需要后面省略号
 */

FI.cutStr = function (str,len,is_mark)
{
	if(!str || str=='')return '';
	var strlen = 0;
	var s = "";
	for(var i = 0;i < str.length;i++)
	{
		if(strlen >= len){
			return is_mark ? (s + "...") : s;
		}
		if(str.charCodeAt(i) > 128)
			strlen += 2;
		else
			strlen++;
		s += str.charAt(i);
	}
	return s;
}
FI.checkChks = function(obj,cobj){
	$(cobj).each(function(){
		$(this)[0].checked = obj.checked;
	})
}
FI.getChks = function(obj){
	var ids = [];
	$(obj).each(function(){
		if($(this)[0].checked)ids.push($(this).val());
	});
	return ids;
}
FI.showHide = function(t,str){
	var s = str.split(',');
	if(t){
		for(var i=0;i<s.length;i++){
		   $(s[i]).show();
		}
	}else{
		for(var i=0;i<s.length;i++){
		   $(s[i]).hide();
		}
	}
	s = null;
}
FI.blank = function(str,defaultVal){
	if(str=='0000-00-00')str = '';
	if(str=='0000-00-00 00:00:00')str = '';
	if(!str)str = '';
	if(typeof(str)=='null')str = '';
	if(typeof(str)=='undefined')str = '';
	if(str=='' && defaultVal)str = defaultVal;
	return str;
}
FI.limitDecimal = function(obj,len){
	var s = obj.value;
 	if(s.indexOf(".")>-1){
	 	if((s.length - s.indexOf(".")-1)>len){
	 		obj.value = s.substring(0,s.indexOf(".")+len+1);
	 	}
	}
 	s = null;
}
FI.getParams = function(obj){
	var params = {};
	var chk = {},s;
	$(obj).each(function(){
		if($(this)[0].type=='hidden' || $(this)[0].type=='number' || $(this)[0].type=='tel' || $(this)[0].type=='password' || $(this)[0].type=='select-one' || $(this)[0].type=='textarea' || $(this)[0].type=='text'){
			params[$(this).attr('id')] = $.trim($(this).val());
		}else if($(this)[0].type=='radio'){
			if($(this).attr('name')){
				params[$(this).attr('name')] = $('input[name='+$(this).attr('name')+']:checked').val();
			}
		}else if($(this)[0].type=='checkbox'){
			if($(this).attr('name') && !chk[$(this).attr('name')]){
				s = [];
				chk[$(this).attr('name')] = 1;
				$('input[name='+$(this).attr('name')+']:checked').each(function(){
					s.push($(this).val());
				});
				params[$(this).attr('name')] = s.join(',');
			}
		}
	});
	chk=null,s=null;
	return params;
}
FI.setValue = function(name, value){
	var first = name.substr(0,1), input, i = 0, val;
	if("#" === first || "." === first){
		input = $(name);
	} else {
		input = $("[name='" + name + "']");
	}

	if(input.eq(0).is(":radio")) { //单选按钮
		input.filter("[value='" + value + "']").each(function(){this.checked = true});
	} else if(input.eq(0).is(":checkbox")) { //复选框
		if(!$.isArray(value)){
			val = new Array();
			val[0] = value;
		} else {
			val = value;
		}
		for(i = 0, len = val.length; i < len; i++){
			input.filter("[value='" + val[i] + "']").each(function(){this.checked = true});
		}
	} else {  //其他表单选项直接设置值
		input.val(value);
	}
}
FI.setValues = function(obj){
	var input,value,val;
    for(var key in obj){
    	if($('#'+key)[0]){
    		FI.setValue('#'+key,obj[key]);
    	}else if($("[name='" + key + "']")[0]){
    		FI.setValue(key,obj[key]);
    	}
    }
}

$(function(){
	/**
	 * 获取Fi基础配置
	 * @type {object}
	 */
	FI.conf = window.conf;
	/* 基础对象检测 */
	FI.conf || $.error("Fi基础配置没有正确加载！");
	/**
	 * 解析URL
	 * @param  {string} url 被解析的URL
	 * @return {object}     解析后的数据
	 */
	FI.parse_url = function(url){
		var parse = url.match(/^(?:([a-z]+):\/\/)?([\w-]+(?:\.[\w-]+)+)?(?::(\d+))?([\w-\/]+)?(?:\?((?:\w+=[^#&=\/]*)?(?:&\w+=[^#&=\/]*)*))?(?:#([\w-]+))?$/i);
		parse || $.error("url格式不正确！");
		return {
			"scheme"   : parse[1],
			"host"     : parse[2],
			"port"     : parse[3],
			"path"     : parse[4],
			"query"    : parse[5],
			"fragment" : parse[6]
		};
	}

	FI.parse_str = function(str){
		var value = str.split("&"), vars = {}, param;
		for(var i=0;i<value.length;i++){
			param = value[i].split("=");
			vars[param[0]] = param[1];
		}
		return vars;
	}
	FI.U = function(url, vars){
		if(!url || url=='')return '';
		var info = this.parse_url(url), path = [], reg;
		/* 验证info */
		info.path || $.error("url格式错误！");
		url = info.path;
		/* 解析URL */
		path = url.split("/");
		path = [path.pop(), path.pop(), path.pop()].reverse();
		path[1] || $.error("FI.U(" + url + ")没有指定控制器");

		/* 解析参数 */
		if(typeof vars === "string"){
			vars = this.parse_str(vars);
		}
		/* 解析URL自带的参数 */
		info.query && $.extend(vars, this.parse_str(info.query));
		if(false !== FI.conf.SUFFIX){
			url += "." + FI.conf.SUFFIX;
		}
		if($.isPlainObject(vars)){
			url += "?" + $.param(vars);
		}
		//url = url.replace(new RegExp("%2F","gm"),"+");
		url = "/"+url;
		return url;
	}
});