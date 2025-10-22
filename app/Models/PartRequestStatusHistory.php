<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartRequestStatusHistory extends Model
{
    protected $fillable = [
        'part_request_id',
        'status',
        'notes',
        'user_id',
    ];

    public function partRequest(): BelongsTo
    {
        return $this->belongsTo(PartRequest::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
