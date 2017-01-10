<?php 
namespace fi\admin\validate;
use think\Validate;
/**
 * 权限验证器
 */
class Articles extends Validate{
	protected $rule = [
	    ['article_title'  ,'require|max:48','请输入文章标题|文章标题不能超过16个字符'],
		['article_key'  ,'require|max:300','请输入关键字|关键字不能超过100个字符'],
	    ['article_content'  ,'require','请输入文章内容']
    ];

    protected $scene = [
        'add'   =>  ['article_title','article_key','article_content'],
        'edit'  =>  ['article_title','article_key','article_content']
    ]; 
}