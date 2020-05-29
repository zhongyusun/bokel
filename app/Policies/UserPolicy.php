<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    //更新策略
    public function update(User $currentUser, User $user){

        return $currentUser->id===$user->id;
    }

    //删除策略
    public function destroy(User $currentUser, User $user){
        //只有当前用户拥有管理员权限且删除的用户不是自己时才显示链接。
        return $currentUser->is_admin && $currentUser->id !== $user->id;
    }


    //用户关注策略
    public function follow(User $currentUser,User $user){
        return $currentUser->id !== $user->id;
    }



    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }
}
