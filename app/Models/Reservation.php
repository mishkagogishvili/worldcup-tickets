<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    protected $fillable = [
        'user_id',
        'ticket_category_id',
        'quantity',
        'status',
        'expires_at',
        'idempotency_key',
        'total_price'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ticketCategory(): BelongsTo
    {
        return $this->belongsTo(TicketCategory::class);
    }
}
