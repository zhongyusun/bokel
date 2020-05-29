<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Auth;

class FollowersController extends Controller
{
    public function __construct(){
    	$this->middleware('auth');
    }

//关注
    public function store(User $user){
    	//调用关注策略
    	$this->authorize('follow', $user);
    	// $user是将要关注的人的用户的信息dd($user);
    	// 判断当前用户是否关注该用户
    	if (!Auth::user()->isFollowing($user->id)) {
    		Auth::user()->follow($user->id);
    	}
    	return redirect()->route('users.show',$user->id);
    }
    //取消关注
    public function destroy(User $user){
    	$this->authorize('follow',$user);

    	if (Auth::user()->isFollowing($user->id)) {
            Auth::user()->unfollow($user->id);
        }
        return redirect()->route('users.show',$user->id);
    }
}
