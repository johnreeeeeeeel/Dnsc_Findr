<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    protected $table = 'feedbacks';
    protected $primaryKey = 'feedback_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'feedback_id',
        'user_id',
        'is_anonymous',
        'service_used',
        'rating_system',
        'message_system',
        'rating_service',
        'message_service',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'is_anonymous' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($feedback) {
            do {
                $id = 'FB' . str_pad(rand(0, 99999999), 8, '0', STR_PAD_LEFT);
            } while (self::where('feedback_id', $id)->exists());

            $feedback->feedback_id = $id;
        });
    }
}
