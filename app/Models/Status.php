<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    //定义文章和用户的关联
    public function user(){
    	return $this->belongsTo(User::class);
    }
}
