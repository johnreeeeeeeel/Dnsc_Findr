<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    protected $primaryKey = 'program_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['program_id', 'program_code', 'program_description', 'institute_id'];

    public function institute()
    {
        return $this->belongsTo(Institute::class, 'institute_id', 'institute_id');
    }
}