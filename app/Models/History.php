<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class History extends Model
{
    protected $fillable = [
        'type','name','avatar','addtime','msg'
    ];
    protected $table = 'webim_history';
}
