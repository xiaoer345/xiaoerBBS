<?php
namespace app\index\controller;

use app\index\model\UserModel;
use think\Db;
use app\index\controller\BaseController;

class IndexController extends BaseController
{
    
    protected function _initialize(){
        $this->user_id = session('id');
        if(!empty($this->user_id)){
            $this->user = Db::table('user')->where('id',$this->user_id)->find();
        }
        if(!empty($this->user)){
            $this->assign('user',$this->user);
        }
    }
    
    public function index()
    {   
        //所有帖子
        $list = Db::table('bbs')->alias('a')->join('board b','a.board_id=b.id')
                ->join('user c','a.user_id=c.id')->field('a.id,title,name,user_login,browser,back')->paginate(10);
                
        //热帖
        $hot = Db::table('bbs')->order('browser desc')->limit(6)->field('id,title')->select();
        //版块信息
        $board = Db::table('bbs')->alias('a')->join('board b','a.board_id=b.id')
                 ->field('b.id,b.name,b.pcard,count(a.id) as count')->group('board_id')->select();
                 
        $this->assign('board',$board);
        $this->assign('hot',$hot);
        $this->assign('list',$list);
        return $this->fetch('index');
    }
    
    //登录页
    public function login(){
        $this->view->engine->layout(false);
        return $this->fetch('login');       
    }
    
    //注册页
    public function signup(){
        $this->view->engine->layout(false);
        return $this->fetch('signup');
    }
    
    //用户登录验证
    public function doLogin(){
        $captcha = $this->request->param('captcha');
        if(!captcha_check($captcha)){
            $this->error('验证码错误');
        }
        $name = $this->request->param('username');
        $pass = $this->request->param('password');
        if(strpos($name,'@')>0){
            $where['user_email'] = $name; 
        }else{
            $where['user_login'] = $name;
        }
        $res = Db::table('user')->where($where)->find();
        if(!empty($res)){
            if(password($pass)==$res['user_pass']){
                session('id',$res['id']);
                session('name',$res['user_login']);
                $res['last_login_ip'] = $this->request->ip();
                $res['last_login_time'] = time();
                
                Db::table('user')->update($res);
                cookie('username',$name,3600 * 24 * 30);               
                $this->success('登陆成功',url('index/index'));
            }else{
                $this->error('密码错误');
            }
        }else{
            $this->error('用户不存在');
        }
    }
    
    //用户注册提交
    public function doSignUp(){
        
        $info = $this->request->param();
        $res_name = Db::table('user')->where('user_login',$info['name'])->find();
        $res_email = Db::table('user')->where('user_email',$info['email'])->find();
        if(!empty($res_name)){
            $this->error('这个用户名太受欢迎了，换一个吧！',null,'name');
        }
        if(!empty($res_email)){
            $this->error('邮箱已被注册，换一个吧！',null,'email');
        }
        
        $user = new UserModel();               
        $pass = password($info['password']);
        if(!$this->validate($info, 'User')){
            $this->error('注册信息不符合规则!',url('index/signup'));
        }
        $data = [
            'user_login' => $info['name'],
            'user_pass'  => $pass,
            'user_email' => $info['email'],
            'create_time' => time(),
        ];
        if($user->save($data)){
            cookie('is_signed',false,time()-1000);
            $this->success('注册成功!',url('index/login'));
        }
    }
    //退出
    public function logout(){
        session('id',null);
        $this->redirect(url('index/index'));
    }
}
