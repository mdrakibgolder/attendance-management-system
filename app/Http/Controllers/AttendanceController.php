<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->string('month')->toString();
        $monthStart = $month ? Carbon::parse($month . '-01')->startOfMonth() : Carbon::now()->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();

        $attendances = Attendance::where('user_id', Auth::id())
            ->whereBetween('attendance_date', [$monthStart, $monthEnd])
            ->latest('attendance_date')
            ->paginate(20)
            ->withQueryString();

        return view('attendances.index', [
            'attendances' => $attendances,
            'selectedMonth' => $monthStart->format('Y-m'),
        ]);
    }

    public function checkIn(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $now = now();
        $today = Carbon::today();

        $attendance = Attendance::firstOrCreate(
            [
                'user_id' => Auth::id(),
                'attendance_date' => $today,
            ],
            [
                'status' => 'present',
                'checked_in_by' => Auth::id(),
            ]
        );

        if ($attendance->check_in_at) {
            return back()->withErrors(['attendance' => 'You have already checked in for today.']);
        }

        $threshold = $today->copy()->setTime(9, 0, 0);
        $lateMinutes = $now->greaterThan($threshold) ? $threshold->diffInMinutes($now) : 0;

        $attendance->fill([
            'check_in_at' => $now,
            'check_in_ip' => $request->ip(),
            'check_in_latitude' => $validated['latitude'] ?? null,
            'check_in_longitude' => $validated['longitude'] ?? null,
            'is_late' => $lateMinutes > 0,
            'late_minutes' => $lateMinutes,
            'status' => 'present',
            'checked_in_by' => Auth::id(),
        ])->save();

        return back()->with('success', 'Check-in recorded successfully.');
    }

    public function checkOut(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $attendance = Attendance::where('user_id', Auth::id())
            ->whereDate('attendance_date', Carbon::today())
            ->first();

        if (! $attendance || ! $attendance->check_in_at) {
            return back()->withErrors(['attendance' => 'You must check in first.']);
        }

        if ($attendance->check_out_at) {
            return back()->withErrors(['attendance' => 'You have already checked out for today.']);
        }

        $checkOutAt = now();
        $workMinutes = $attendance->check_in_at->diffInMinutes($checkOutAt);

        $attendance->fill([
            'check_out_at' => $checkOutAt,
            'check_out_ip' => $request->ip(),
            'check_out_latitude' => $validated['latitude'] ?? null,
            'check_out_longitude' => $validated['longitude'] ?? null,
            'checked_out_by' => Auth::id(),
            'total_work_minutes' => $workMinutes,
            'status' => 'present',
        ])->save();

        return back()->with('success', 'Check-out recorded successfully.');
    }
}
