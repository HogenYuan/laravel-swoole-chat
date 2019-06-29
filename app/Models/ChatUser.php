<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatUser extends Model
{
    //
    public $fillable = ['sid', 'nickname', 'password', 'createtime'];
}
