<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;


    protected $primaryKey = 'user_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['user_id', 'lastname', 'firstname', 'middlename', 'sex', 'dob', 'usertype', 'institute', 'program', 'username', 'email', 'password', 'temp_password_hash'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function institute()
    {
        return $this->belongsTo(Institute::class, 'institute', 'institute_code');
    }

    public function program()
    {
        return $this->belongsTo(Program::class, 'program', 'program_code');
    }

    public function hasTempPassword(): bool
    {
        return $this->temp_password_hash !== null;
    }

    public function clearTempPassword(): void
    {
        $this->temp_password_hash = null;
        $this->save();
    }
    
}
