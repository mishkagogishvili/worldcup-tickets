<?php

namespace App\Http\Controllers;

use App\Jobs\ExpireReservations;
use App\Models\Matches;
use App\Models\TicketCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Contracts\Cache\LockTimeoutException;
use App\Jobs\ProcessTicketPayment;
use App\Models\Reservation;

class ReservationController extends Controller
{
    public function store(Request $request)
    {
        if ($request->user()->cannot('create', Reservation::class)) {
            return response()->json(['message' => 'Unauthorized action'], 403);
        }
        $request->validate([
            'match_id' => 'required|exists:matches,id',
            'category_id' => 'required|exists:ticket_categories,id',
            'quantity' => 'required|integer|min:1|max:4',
        ]);

        $user = $request->user();
        return Cache::lock('reserve_user_' . $user->id, 5)->get(function () use ($request, $user) {
            try {
                return Cache::lock('category_' . $request->category_id, 5)->block(3, function () use ($request, $user) {
                    return DB::transaction(function () use ($request, $user) {
                        $category = TicketCategory::where('id', $request->category_id)
                            ->where('match_id', $request->match_id)
                            ->lockForUpdate()
                            ->first();

                        if (!$category || $category->available_count < $request->quantity) {
                            return response()->json(['message' => 'Not enough seats available'], 422);
                        }
                        $reservation = $user->reservations()->create([
                            'ticket_category_id' => $request->category_id,
                            'quantity' => $request->quantity,
                            'total_price' => $category->price * $request->quantity,
                            //'status' => 'pending',
                            'expires_at' => now()->addMinutes(10),
                        ]);
                        $category->decrement('available_count', $request->quantity);
                        ExpireReservations::dispatch($reservation)->delay(now()->addMinutes(10))->onQueue('default')->afterCommit();
                        return response()->json([
                            'reservation_id' => $reservation->id,
                            'message' => 'Seats reserved successfully'
                        ], 201);
                    });
                });
            } catch (LockTimeoutException $e) {
                return response()->json(['message' => 'Please wait'], 423);
            }
        });
    }

    public function pay(Request $request, $id)
    {
        $reservation = Reservation::findOrFail($id);
        if ($request->user()->cannot('update', $reservation)) {
            return response()->json(['message' => 'Unauthorized action'], 403);
        }
        if ($reservation->status !== 'pending' || $reservation->expires_at < now()) {
            return response()->json(['message' => 'Reservation cannot be paid'], 422);
        }
        ProcessTicketPayment::dispatch($reservation)->onQueue('critical');
        return response()->json([
            'message' => 'გადახდის პროცესი დაიწყო. გთხოვთ დაელოდოთ დასტურს.'
        ], 202);
    }

    public function cancel(Request $request, $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $reservation = Reservation::where('id', $id)->lockForUpdate()->firstOrFail();
            if ($request->user()->cannot('delete', $reservation)) {
                return response()->json(['message' => 'Unauthorized action'], 403);
            }
            if ($reservation->status !== 'pending' || $reservation->expires_at < now()) {
                return response()->json(['message' => 'Reservation cannot be cancelled'], 422);
            }
            $reservation->ticketCategory()->lockForUpdate()->increment('available_count', $reservation->quantity);
            $reservation->update(['status' => 'cancelled']);
            return response()->json([
                'message' => 'Reservation cancelled successfully'
            ], 200);
        });
    }
}
