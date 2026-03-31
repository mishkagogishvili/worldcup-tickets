<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use App\Models\Reservation;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class ExpireReservations implements ShouldQueue, ShouldBeUnique
{
    use Queueable;

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

            if ($reservation && $reservation->status === 'pending') {
                $reservation->ticketCategory()
                    ->lockForUpdate()
                    ->increment('available_count', $reservation->quantity);
                $reservation->update(['status' => 'expired']);
            }
        });
    }
}
