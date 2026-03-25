<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today();

        $todaysAttendance = Attendance::where('user_id', $user->id)
            ->whereDate('attendance_date', $today)
            ->first();

        $checkedInToday = collect();
        $adminStats = [];

        if ($user->isAdmin()) {
            $checkedInToday = Attendance::with(['user.department'])
                ->whereDate('attendance_date', $today)
                ->whereNotNull('check_in_at')
                ->whereNull('check_out_at')
                ->orderBy('check_in_at')
                ->get();

            $adminStats = [
                'present_count' => Attendance::whereDate('attendance_date', $today)->where('status', 'present')->count(),
                'late_count' => Attendance::whereDate('attendance_date', $today)->where('is_late', true)->count(),
                'incomplete_count' => Attendance::whereDate('attendance_date', $today)->where('status', 'incomplete')->count(),
                'checked_in_live' => $checkedInToday->count(),
            ];
        }

        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();

        $monthly = Attendance::where('user_id', $user->id)
            ->whereBetween('attendance_date', [$monthStart, $monthEnd])
            ->get();

        $workingMinutes = (int) $monthly->sum('total_work_minutes');
        $presentDays = $monthly->where('status', 'present')->count();
        $lateDays = $monthly->where('is_late', true)->count();

        $leaveDays = LeaveRequest::where('user_id', $user->id)
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $monthEnd)
            ->whereDate('end_date', '>=', $monthStart)
            ->get()
            ->sum('total_days');

        $expectedWorkingDays = collect(CarbonPeriod::create($monthStart, $monthEnd))
            ->filter(fn (Carbon $date) => $date->isWeekday())
            ->count();

        $absentDays = max(0, $expectedWorkingDays - $presentDays - (int) $leaveDays);

        return view('dashboard', [
            'todaysAttendance' => $todaysAttendance,
            'checkedInToday' => $checkedInToday,
            'adminStats' => $adminStats,
            'monthlySummary' => [
                'present_days' => $presentDays,
                'late_days' => $lateDays,
                'absent_days' => $absentDays,
                'working_hours' => round($workingMinutes / 60, 2),
            ],
        ]);
    }
}
