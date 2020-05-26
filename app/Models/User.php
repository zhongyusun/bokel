<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

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

    //调取该用户的文章，按照时间倒序排列
    public function feed(){
        return $this->statuses()->orderBy('created_at','desc');
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
