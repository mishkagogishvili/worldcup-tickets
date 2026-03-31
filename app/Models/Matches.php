<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Matches extends Model
{
    use HasFactory;
    protected $fillable = [
        'home_team',
        'away_team',
        'match_date',
        'stadium',
        'capacity'
    ];

    protected $casts = [
        'match_date' => 'datetime',
        'home_team' => 'array',
        'away_team' => 'array',
        'stadium' => 'array',
    ];

    public function ticketCategories(): HasMany
    {
        return $this->hasMany(TicketCategory::class, 'match_id');
    }

    protected static function booted(): void
    {
        static::created(function (Matches $match) {
            $categories = [
                ['name' => 'Category 1', 'price' => 500, 'percent' => 0.10],
                ['name' => 'Category 2', 'price' => 300, 'percent' => 0.30],
                ['name' => 'Category 3', 'price' => 150, 'percent' => 0.60],
            ];
            $seat_count_total = 0;
            foreach ($categories as $key => $category) {
                if ($key === count($categories) - 1) {
                    $seat_count = $match->capacity - $seat_count_total;
                } else {
                    $seat_count = (int) round($match->capacity * $category['percent']);
                    $seat_count_total += $seat_count;
                }

                $match->ticketCategories()->create([
                    'name' => $category['name'],
                    'price' => $category['price'],
                    'seat_count' => $seat_count,
                    'available_count' => $seat_count,
                ]);
            }
        });
    }
}
