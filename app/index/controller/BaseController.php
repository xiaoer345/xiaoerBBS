<?php
namespace app\index\controller;

use think\Controller;
use think\Db;

class BaseController extends Controller{
    
    protected $user_id;
    protected $user;
    
    protected function _initialize(){
        //模板中替换字符
        $root = get_root();
        $viewReplaceStr = [
            '__STATIC__' => $root.'/static'
        ];
        config('view_replace_str',$viewReplaceStr);        
    }
}