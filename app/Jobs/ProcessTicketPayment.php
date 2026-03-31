<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\Reservation;
use App\Models\Ticket;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ProcessTicketPayment implements ShouldQueue, ShouldBeUnique
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public Reservation $reservation)
    {
        //
    }

    public function uniqueId(): string
    {
        return $this->reservation->id;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        DB::transaction(function () {
            $reservation = Reservation::where('id', $this->reservation->id)
                ->lockForUpdate()
                ->first();
            if (!$reservation || $reservation->status !== 'pending') {
                return;
            }

            $paymentSuccess = rand(0, 1);
            $category = $reservation->ticketCategory()->lockForUpdate()->first();

            if ($paymentSuccess) {
                $reservation->update(['status' => 'confirmed']);
                for ($i = 0; $i < $reservation->quantity; $i++) {
                    Ticket::create([
                        'user_id'        => $reservation->user_id,
                        'reservation_id' => $reservation->id,
                        'match_id'       => $category->match_id,
                        'category_id'    => $reservation->ticket_category_id,
                        'seat_number'    => $category->seat_count - $category->available_count + 1,
                        'qr_code'        => Str::uuid(),
                    ]);
                }
            } else {
                $reservation->update(['status' => 'cancelled']);
                $category->increment('available_count', $reservation->quantity);
            }
        });
    }
}
