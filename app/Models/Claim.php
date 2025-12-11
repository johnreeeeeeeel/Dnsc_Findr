<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Claim extends Model
{
    public $timestamps = false;

    protected $primaryKey = 'claim_reference_number';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'claim_reference_number',
        'item_id',
        'user_id',
        'claim_details_reference_number',
        'claim_status',
        'request_date',
    ];

    // Relationship
    public function details()
    {
        return $this->belongsTo(ClaimDetails::class, 'claim_details_reference_number', 'claim_details_reference_number');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function item()
    {
        return $this->belongsTo(FoundItem::class, 'item_id', 'item_id');
    }
}
