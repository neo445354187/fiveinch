<?php
namespace fi\common\model;
/**
 * 积分业务处理器
 */
class UserScores extends Base{
     /**
      * 获取列表
      */
      public function pageQuery($user_id){
      	  $type = (int)input('post.type');
          $where = ['user_id'=>(int)$user_id];
          if($type!=0)$where['score_type'] = $type;
          $page = $this->where($where)->order('score_id desc')->paginate()->toArray();
          foreach ($page['Rows'] as $key => $v){
          	  $page['Rows'][$key]['data_src'] = FILangScore($v['data_src']);
          }
          return $page;
      }
}
