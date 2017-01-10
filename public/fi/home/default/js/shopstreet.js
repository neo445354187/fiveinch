//筛选分类
function screenCat(id){
	location.href=FI.U('home/shops/shopStreet','id='+id);
}
$(function(){
	var goods_num = $(this).find("div[class^='fi-shopstr-shopl']").length;
	for(var i=1;i<=goods_num;++i){
    	$("#js-goods"+i).als({
    		visible_items: 6,
    		scrolling_items: 1,
    		orientation: "horizontal",
    		circular: "yes",
    		autoscroll: "no",
    		start_from: 2
    	});
	}
	FI.dropDownLayer(".j-score",".j-scores");
});