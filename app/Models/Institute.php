<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Institute extends Model
{
    protected $primaryKey = 'institute_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['institute_id', 'institute_code', 'institute_description'];

    public function programs()
    {
        return $this->hasMany(Program::class, 'institute_id', 'institute_id');
    }
}