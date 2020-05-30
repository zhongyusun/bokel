<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Auth;

class User extends Authenticatable
{
    use Notifiable;
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    //邮箱验证令牌验证
    public static function boot(){
        parent::boot();

        static::creating(function ($user){
            $user->activation_token = Str::random(10);
        });
    }

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    //头像
    public function gravatar($size = '100')
    {
        $hash = md5(strtolower(trim($this->attributes['email'])));
        return "http://www.gravatar.com/avatar/$hash?s=$size";
    }

    //定义用户和文章的关联
    public function statuses(){
        return $this->hasMany(Status::class);
    }

    //    调取该用户的文章，按照时间倒序排列
    //动态流首页显示时时动态
    public function feed(){
        //获取当前登录的人的关注的人的id合计
        //还有一点需要注意的是 $user->followings 与 $user->followings() 调用时返回的数据是不一样的， $user->followings 返回的是 Eloquent：集合 。而 $user->followings() 返回的是 数据库请求构建器 ，可以简单理解为 followings 返回的是数据集合，而 followings() 返回的是数据库查询语句
        $user_ids=$this->followings->pluck('id')->toArray();
        //将当前登录的人的id放入该合集中
        //使用 Laravel 提供的 查询构造器 whereIn 方法取出所有用户的微博动态并进行倒序排序；
//我们使用了 Eloquent 关联的 预加载 with 方法，预加载避免了 N+1 查找的问题，大大提高了查询效率。N+1 问题 的例子可以阅读此文档 Eloquent 模型关系预加载 。
        array_push($user_ids, $this->id);
        return Status::whereIn('user_id', $user_ids)
                              ->with('user')
                              ->orderBy('created_at', 'desc');
    }

    //多对多
    //用户和粉丝关联
    //自定义表名followers
    //获取粉丝列表
    public function followers(){
       return $this->belongsToMany(User::Class, 'followers', 'user_id', 'follower_id');
    }

    //通过 followers 来获取关注列表
     public function followings()
    {
        return $this->belongsToMany(User::Class, 'followers', 'follower_id', 'user_id');
    }


    //关注
    public function follow($user_ids){
        if ( ! is_array($user_ids)){
            $user_ids=compact(('user_ids'));
        }
        $this->followings()->sync($user_ids,false);
    }

    //取消关注
    public function unfollow($user_ids){
        if (! is_array($user_ids)) {
            $user_ids=compact('user_ids');
        }
        $this->followings()->detach($user_ids);
    }


    //是否关注
    public function isFollowing($user_id){
        return $this->followings->contains($user_id);
    }
}
