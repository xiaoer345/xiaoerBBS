<?php
namespace app\index\controller;

use app\index\controller\BaseController;
use app\index\model\BbsModel;
use think\Db;


class PostController extends BaseController{
    
    protected function _initialize(){
        $this->user_id = session('id');
        if(!empty($this->user_id)){
            $this->user = Db::table('user')->where('id',$this->user_id)->find();
        }
        if(!empty($this->user)){
            $this->assign('user',$this->user);
        }
    }
    
    public function index(){
        $this->view->engine->layout(false);
        return $this->fetch('post');
    }
    public function post(){
        if(empty($this->user_id)){
            return $this->error('尚未登录，请先登录',url('index/login'));
        }
        return $this->success('成功',url('post/index'));
    }
    //查看帖子
    public function bbs(){        
        $bbs_id = $this->request->param('id');        
        //帖子
        $bbs = Db::table('bbs')->where('id',$bbs_id)->find();
        $this->assign('bbs',$bbs);
        
        //帖子回复
        $list = Db::table('comment')->where('bbs_id',$bbs_id)->alias('a')->join('user b','a.user_id=b.id')->field('content,sendtime,user_login,avatar')->select();
        $this->assign('list',$list);        
        $user = Db::table('user')->where('id',$bbs['user_id'])->find();
        Db::table('bbs')->where('id',$bbs_id)->setInc('browser');
        $this->assign('user_mem',$user);       
        return $this->fetch('bbs');
    }
    //评论
    public function comment(){
        if(empty($this->user_id)){
            return $this->error('尚未登录，请先登录',url('index/login'));
        }
        $info = $this->request->param();
        $data = [
            'bbs_id' => $info['id'],
            'content' => $info['content'],
            'user_id' => $this->user_id,
            'sendtime' => time()
        ];
        Db::table('comment')->insert($data);
        Db::table('bbs')->where('id',$info['id'])->setInc('back');
        Db::table('user')->where('id',$this->user_id)->setInc('score');
        $ret = [
            'code' => 1,
            'msg' => '评论成功，积分+1',
        ];
        return $ret;
    }
        
    public function doPost(){
        $board = [
            'Javascript' => 1,
            'PHP' => 2,
            '随想' => 3
        ];
        $bbs = new BbsModel();
        
        $post = $this->request->param();
        $valid = false;
        if(!empty($post['content'])&&strlen($post['content'])>3){
            $valid = true;
        }
        
        if($valid){
            $data = [
                'title' => $post['title'],
                'content' => $post['content'],
                'user_id' => $this->user_id,
                'sendtime' => time(),
                'istop' => 0,
                'isgood' => 0,
                'board_id' => $board[$post['class']],
            ];
            $bbs->save($data);
            Db::table('user')->where('id',$this->user_id)->setInc('score',3);
            return $this->success('发布成功,积分+3',url('index/index'));
        }else{
            return $this->error('内容请至少输入六个字');
        }
    }
    //wangEditor图片上传处理
    public function upload(){
        $file = $this->request->file('file');
        if($file){
            $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
            if($info){
                $root = get_root();
                $path = $root. DS . 'uploads'.DS.$info->getSaveName();
                $ret = [
                    'errno' => 0,
                    'data' => [$path]
                ];               
                return json_encode($ret);
            }else{
                // 上传失败获取错误信息
                echo $file->getError();
            }
        }
    }
    //ueditor图片上传处理
    public function uploadue(){
        $action = $this->request->param('action');
        switch($action){
            case 'config':
                $result = file_get_contents(ROOT_PATH.'public/static/js/ueditor/config.json');
                break;
            case 'uploadimage':
                $file = $this->request->file('upfile');
                if($file){
                    $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
                    $res = $info->getInfo();
                    $res['state'] = 'SUCCESS';
                    $res['url'] = $info->getSaveName();
                    $result = json_encode($res);
                }
                break;
            default:
                break;
        }
        return $result;
    }
}