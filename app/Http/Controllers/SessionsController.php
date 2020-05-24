<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use Mail;

class SessionsController extends Controller
{
	//构造器
	//只让未登录用户访问登录页面：
	public function __construct(){

		$this->middleware('guest',[
			'only' =>['create'],
		]);
	}


	//登录页面
    public function create(){

    	return view('sessions.create');
    }

    //处理登录数据
    public function store(Request $request){
    	//登录验证
    	$credentials = $this->validate($request,[
    		'email'=>'required|email|max:255',
    		'password' => 'required'
    	]);


    	//判断登录用户的邮箱和密码
    	if (Auth::attempt($credentials,$request->has('remember'))){
            if (Auth::user()->activated) {
                session()->flash('success','欢迎回来！');
                //跳转到之前操作的页面，友好的转向
                $fallback = route('users.show',Auth::user());

                return redirect()->intended($fallback);
            }else{
                Auth::logout();
               session()->flash('warning', '你的账号未激活，请检查邮箱中的注册邮件进行激活。');
               return redirect('/');
            }
    			

       	}else{
    			session()->flash('danger','很抱歉，您的邮箱和密码不匹配');
    			return redirect()->back()->withInput();
    	}
    	return;
    }


    //退出操作
    public function destroy(){
    	Auth::logout();
    	session()->flash('success','您已成功退出');
    	return redirect('login');
    }
}
