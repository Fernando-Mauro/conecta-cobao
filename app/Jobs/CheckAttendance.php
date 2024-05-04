<?php

namespace App\Jobs;

use App\Models\Student;
use App\Models\StudentCheckIn;
use App\Models\StudentCheckOut;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckAttendance implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
        $students = Student::all();
        $today = Carbon::today();
        foreach($students as $student){
            $checkIns = StudentCheckIn::where('created_at', $today);
            $checkOuts = StudentCheckOut::where('created_at', $today);
            if(!$checkIns){
                
            }
        }
    }
}
