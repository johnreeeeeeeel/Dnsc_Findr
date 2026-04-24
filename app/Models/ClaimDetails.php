<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ClaimDetails extends Model
{
    public $timestamps = false;

    protected $primaryKey = 'claim_details_reference_number';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['claim_details_reference_number', 'description', 'location_lost', 'date_lost', 'time_lost'];

    public function claim()
    {
        return $this->hasOne(Claim::class, 'claim_details_reference_number', 'claim_details_reference_number');
    }
}
