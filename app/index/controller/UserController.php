<?php
namespace app\index\controller;

use think\Db;
use app\index\controller\BaseController;

class UserController extends BaseController{
    
    protected function _initialize(){
        $this->user_id = session('id');
        if(!empty($this->user_id)){
            $this->user = Db::table('user')->where('id',$this->user_id)->find();
        }else{
            return $this->error('尚未登录，请先登录',url('index/login'));
        }
        if(!empty($this->user)){
            $this->assign('user',$this->user);
        }
    }
    
    //用户信息
    public function index(){      
        $list = Db::table('bbs')->where('user_id',$this->user_id)->paginate(10);
        $this->assign('list',$list);
        return $this->fetch('index');
    }
    //用户信息修改
    public function modify(){
        $info = $this->request->param();
        if($info){
            $data = [
                'user_login' => $info['username']?:$this->user['user_login'],
                'user_nickname' => $info['nickname']?:$this->user['user_nickname'],
                'user_email' => $info['email']?:$this->user['user_email']
            ];
            Db::table('user')->where('id',$this->user_id)->update($data);
            $ret = [
                'code' => 1,
                'msg' => '保存成功'
            ];
            return $ret;
        }else{
            $ret = [
                'code' => 0,
                'msg' => '保存失败',
            ];
            return $ret;           
        }
    }
    //用户头像上传
    public function avatar(){
        $file = $this->request->file('avatar');
        if($file){
            $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads'.DS.'avatar');
            if($info){
                $root = get_root();
                $path = $root. DS . 'uploads'.DS.'avatar'.DS.$info->getSaveName();
                Db::table('user')->where('id',$this->user_id)->update(['avatar'=>$path]);
                $ret = [
                    'code' => 1,
                    'msg' => '上传成功'
                ];
                return $ret;
            }else{
                $this->error('上传失败');
            }
        }
        else{
            $this->error('上传失败');
        }
    }
    //签到
    public function sign(){       
        $day = date('d',time());
        $left_time = strtotime(date('Y-m',time()).'-'.($day+1))-time();               
        $is_signed = $this->request->param('is_signed');
        if($is_signed){
            cookie('is_signed',true,$left_time);
            Db::table('user')->where('id',$this->user_id)->setInc('score');
            $ret = [
                'code' => 1,
                'msg' => '签到成功，积分+1',
            ];
            return $ret;
        }
        return $this->error('签到失败');
    }
}