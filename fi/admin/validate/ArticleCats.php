<?php 
namespace fi\admin\validate;
use think\Validate;
/**
 * 权限验证器
 */
class ArticleCats extends Validate{
	protected $rule = [
	    ['cat_name'  ,'require|max:30','请输入文章分类名称|文章分类名称不能超过10个字符'],
	    ['cat_sort'  ,'require|max:16','请输入排序号|排序号不能超过8个字符'],
    ];

    protected $scene = [
        'add'   =>  ['cat_name','cat_sort'],
        'edit'  =>  ['cat_name','cat_sort'],
    ]; 
}