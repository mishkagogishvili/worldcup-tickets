<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketCategory extends Model
{
    protected $fillable = [
        'match_id',
        'name',
        'price',
        'seat_count',
        'available_count'
    ];

    public function match(): BelongsTo
    {
        return $this->belongsTo(Matches::class, 'match_id');
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'category_id');
    }
}
