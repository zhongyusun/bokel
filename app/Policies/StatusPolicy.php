<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Models\Status;

class StatusPolicy
{
    use HandlesAuthorization;

    //删除策略
    public function destroy(User $user,Status $status){
        // if ($user->id === $status->user_id) {
        //     return true;
        // }
        return $user->id === $status->user_id;
    }

    // *
    //  * Create a new policy instance.
    //  *
    //  * @return void
     
    // public function __construct()
    // {
    //     //
    // }
}
