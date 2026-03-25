<?php

namespace App\Jobs;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class MarkIncompleteAttendances implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $yesterday = Carbon::yesterday()->toDateString();

        Attendance::whereDate('attendance_date', $yesterday)
            ->whereNotNull('check_in_at')
            ->whereNull('check_out_at')
            ->update([
                'status' => 'incomplete',
                'updated_at' => now(),
            ]);
    }
}
