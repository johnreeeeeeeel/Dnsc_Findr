<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FoundItem extends Model
{
    protected $table = 'found_items';
    protected $primaryKey = 'item_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'item_id',
        'item_name',
        'item_category',
        'description',
        'photo_url',
        'location_found',
        'date_found',
        'time_found',
        'status',
        'post_status',      
        'posted_by',
    ];

    public $timestamps = true;

    public function postedBy()
    {
        return $this->belongsTo(User::class, 'posted_by', 'user_id');
    }
}