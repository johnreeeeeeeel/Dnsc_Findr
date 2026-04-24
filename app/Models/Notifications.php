<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notifications extends Model
{
    protected $table = 'notifications';

    protected $fillable = ['user_id', 'title', 'message', 'is_read'];

    public $timestamps = true;
}
