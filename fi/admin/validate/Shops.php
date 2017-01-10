<?php 
namespace fi\admin\validate;
use think\Validate;
/**
 * 店铺验证器
 */
class Shops extends Validate{
	protected $rule = [
	    ['shop_sn','checkShopSn:1','请输入店铺编号|店铺编号不能超过20个字符'],
	    ['shop_name'  ,'require|max:40','请输入店铺名称|店铺名称不能超过20个字符'],
        ['shop_keeper'  ,'require|max:100','请输入店主姓名|店主姓名不能超过50个字符'],
        ['telephone'  ,'require|max:40','请输入店主联系手机|店主联系手机不能超过20个字符'],
        ['shop_company'  ,'require|max:100','请输入公司名称|公司名称不能超过50个字符'],
        ['shop_tel'  ,'require|max:40','请输入店铺联系电话|店铺联系电话不能超过20个字符'],
        ['is_self'  ,'in:0,1','无效的自营店类型'],
        ['shop_img'  ,'require','请上传店铺图标'],
		['shop_qq'  ,'require|max:100','请输入客服QQ|客服QQ不能超过50个字符'],
        ['area_id'  ,'require','请选择店铺所在区域'],
        ['shop_address'  ,'require','请输入店铺详细地址'],
        ['is_invoice'  ,'in:0,1','无效的发票类型'],
        ['invoice_remarks','checkInvoiceRemark:1','请输入发票说明'],
        ['shop_ative'  ,'in:0,1','无效的营业状态'],
        ['bank_id'  ,'require','请选择结算银行'],
        ['bank_no'  ,'require','请选择银行账号'],
        ['bank_username'  ,'require|max:100','请输入持卡人名称|持卡人名称长度不能能超过50个字符'],
        ['shop_status'  ,'in:-1,1','无效的店铺状态'],
        ['status_desc'  ,'checkStatusDesc:1','请输入店铺停止原因']
    ];

    protected $scene = [
        'add'   =>  ['shop_name','shop_keeper','telephone','shop_company','shop_tel','is_self','shop_img','shop_qq',
                     'area_id','shop_address','is_invoice','shop_ative','bank_id','bank_no','bank_username','shop_ative'],
        'edit'  =>  ['shop_name','shop_keeper','telephone','shop_company','shop_tel','is_self','shop_img','shop_qq',
                     'area_id','shop_address','is_invoice','shop_ative','bank_id','bank_no','bank_username','shop_ative']
    ]; 
    
    protected function checkShopSn($value){
    	$shop_id = Input('post.shop_id/d',0);
    	$key = Input('post.shop_sn');
    	if($shop_id>0){
    		if($key=='')return '请输入店铺编号';
    		$isChk = model('Shops')->checkShopSn($key,$shop_id);
    		if($isChk)return '对不起，该店铺编号已存在';
    	}
    	return true;
    }
    
    protected function checkInvoiceRemark($value){
    	$is_invoice = Input('post.is_invoice/d',0);
    	$key = Input('post.invoice_remarks');
    	return ($is_invoice==1 && $key=='')?'请输入发票说明':true;
    }
    
    protected function checkStatusDesc($value){
    	$shop_status = Input('post.shop_status/d',0);
    	$key = Input('post.status_desc');
    	return ($shop_status==-1 && $key=='')?'请输入店铺停止原因':true;
    }
}