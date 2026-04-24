<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OsdsAppointment extends Model
{
    protected $primaryKey = 'appointment_reference_number';
    public $incrementing = false;
    protected $keyType = 'string';

    // COMPLETELY disable Laravel's timestamp management
    public $timestamps = false;

    // DELETE THESE TWO LINES — they are the root cause!
    // const CREATED_AT = 'sent_at';
    // const UPDATED_AT = null;

    protected $fillable = [
        'appointment_reference_number',
        'user_id',
        'schedule_date',
        'schedule_time',
        'purpose',
        'status',
        'response_message',
        'reschedule_date',
        'reschedule_time',
        'sent_at', // ← MUST be here!
    ];

    protected $casts = [
        'schedule_date'    => 'date:Y-m-d',
        'reschedule_date'  => 'date:Y-m-d',
        'schedule_time'    => 'datetime:H:i',
        'reschedule_time'  => 'datetime:H:i',
        'sent_at'          => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public static function generateReference(): string
    {
        do {
            $ref = 'APPT-' . strtoupper(substr(uniqid(), -8));
        } while (self::where('appointment_reference_number', $ref)->exists());

        return $ref;
    }

    // Auto-fill reference + sent_at on create
    protected static function booted()
    {
        static::creating(function ($appt) {
            if (!$appt->appointment_reference_number) {
                $appt->appointment_reference_number = self::generateReference();
            }
            if (!$appt->sent_at) {
                $appt->sent_at = now();
            }
            if (!$appt->status) {
                $appt->status = 'Pending';
            }
        });
    }
}