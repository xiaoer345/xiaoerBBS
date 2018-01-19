<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
/**
 * 获取网站根目录
 * @return string 网站根目录
 */
function get_root()
{
    $request = \think\Request::instance();
    $root    = $request->root();
    $root    = str_replace('/index.php', '', $root);
    if (defined('APP_NAMESPACE') && APP_NAMESPACE == 'api') {
        $root = preg_replace('/\/api$/', '', $root);
        $root = rtrim($root, '/');
    }
    
    return $root;
}
/**
 * 密码加密方法
 * @param string $pw 要加密的原始密码
 * @param string $authCode 加密字符串
 * @return string
 */
function password($pw, $authCode = '')
{
    if (empty($authCode)) {
        $authCode = \think\Config::get('database.authcode');
    }
    $result = "###" . md5(md5($authCode . $pw));
    return $result;
}

function cat_url($url,$param){
    if(empty($param)){
        return $url;
    }
    $res = $url.'?id='.$param;
    return $res;
}


