<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Mail;
use Auth;


class UserController extends Controller
{

	// 除了'show','create','store'，其他只能登录过后才能访问
	public function __construct(){
		$this->middleware('auth',[
			'except' => ['show','create','store','index','confirmEmail']
		]);


		// 会被跳转到 Laravel 默认指定的页面 /home ，因我们并没有此页面，所以会报错 404 找不到页面。我们需要修改下中间件里的 redirect() 方法调用，并加上友好的消息提醒：
		// app/Http/Middleware/RedirectIfAuthenticated.php
		// 只有游客可以访问注册页面
		$this->middleware('guest',[
			'only'=>['create'],
		]);
	}

	//用户列表
	public function index(){
		$users=User::paginate(10);
		return view('users.index',compact('users'));
	}

	//注册页面
    public function create(){

    	return view('users.create');
    }


    //个人中心页面
    public function show(User $user){

    	return view('users.show',compact('user'));
    }



    //处理注册数据
    public function store(Request $request){
        //注册数据验证
         $this->validate($request,[
            'name'=>'required|unique:users|max:50',
            'email'=>'required|email|unique:users|max:255',
            'password'=>'required|confirmed|min:6'
         ]); 

         $user = User::create([
         	'name' => $request->name,
         	'email' => $request->email,
         	'password' => bcrypt($request->password),
         ]);

         $this->sendEmailConfirmationTo($user);
          session()->flash('success', '验证邮件已发送到你的注册邮箱上，请注意查收。');

        return redirect('/');
    }

    //邮箱页面排版
    protected function sendEmailConfirmationTo($user){
        $view = 'emails.confirm';
        $data = compact('user');
        $from = 'summer@example.com';
        $name = 'Summer';
        $to = $user->email;
        $subject = "感谢注册 Weibo 应用！请确认你的邮箱。";

        Mail::send($view, $data, function ($message) use ($from, $name, $to, $subject) {
            $message->from($from, $name)->to($to)->subject($subject);
        });
    }

    //邮箱激活处理
    public function confirmEmail($token){
        $user = User::where('activation_token',$token)->firstOrFail();
        $user->activated = true;
        $user->activation_token = null;
        $user->save();
        Auth::login($user);
        session()->flash('success', '恭喜你，激活成功！');
        return redirect()->route('users.show', [$user]);
    }

    //编辑页面
    public function edit(User $user){
    	//策略，用户只能更新自己的数据
    	$this->authorize('update', $user);
    	return view('users.edit',compact('user'));
    }

    //处理更新数据
    public function update(User $user,Request $request){
    	//策略，用户只能更新自己的数据
    	$this->authorize('update', $user);
    	//更新数据验证
    	$this->validate($request,[
    		'name'=>'required|max:50',
    		'password'=>'required|confirmed|min:6'
    	]);
    	$date=[];
    	$date['name']=$request->name;
    	if ($request->password) {
    		$date['password']=bcrypt($request->password);
    	}

    	$user->update($date);

    	session()->flash('success','个人资料更新成功');

    	return redirect()->route('users.show',$user->id);

    }


    //删除用户
    public function destroy(User $user){
        //删除策略,app/Policies/UserPolicy.php
        $this->authorize('destroy', $user);
        $user->delete();
        session('success','成功删除用户');
        return back();
    }
}
