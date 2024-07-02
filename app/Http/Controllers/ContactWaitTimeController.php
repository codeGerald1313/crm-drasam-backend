<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\ContactWaitTime;

class ContactWaitTimeController extends Controller
{
    public function calculateAverageWaitTimeInLast24Hours()
    {
        $currentDateTime = Carbon::now();

        $twentyFourHoursAgo = $currentDateTime->subDay()->startOfDay();

        $currentDateTime = Carbon::now()->endOfDay();

        $totalWaitTime = ContactWaitTime::where('created_at', '>=', $twentyFourHoursAgo)
            ->where('created_at', '<=', $currentDateTime)
            ->sum('wait_time');

        $assignmentCount = Assignment::where('created_at', '>=', $twentyFourHoursAgo)
            ->where('created_at', '<=', $currentDateTime)
            ->count();

        if ($assignmentCount > 0) {
            $averageWaitTime = $totalWaitTime / $assignmentCount;
        } else {
            $averageWaitTime = 0;
        }
        $averageWaitTime = number_format($averageWaitTime, 1);

        return response()->json(['average_wait_time' => $averageWaitTime . ' s']);
    }
}

