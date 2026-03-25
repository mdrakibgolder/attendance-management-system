<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceOverride;
use App\Models\LeaveRequest;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceReportController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'employee_id' => ['nullable', 'exists:users,id'],
            'status' => ['nullable', 'in:present,incomplete,absent,on_leave'],
            'month' => ['nullable', 'date_format:Y-m'],
        ]);

        $dateFrom = isset($validated['date_from']) ? Carbon::parse($validated['date_from'])->startOfDay() : Carbon::today()->startOfMonth();
        $dateTo = isset($validated['date_to']) ? Carbon::parse($validated['date_to'])->endOfDay() : Carbon::today()->endOfDay();

        $attendances = Attendance::with(['user.department', 'managerApprovedBy'])
            ->whereBetween('attendance_date', [$dateFrom->toDateString(), $dateTo->toDateString()])
            ->when($validated['employee_id'] ?? null, fn ($query, $employeeId) => $query->where('user_id', $employeeId))
            ->when($validated['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->orderByDesc('attendance_date')
            ->paginate(20)
            ->withQueryString();

        $month = isset($validated['month']) ? Carbon::parse($validated['month'] . '-01') : Carbon::now()->startOfMonth();

        return view('admin.attendances.index', [
            'attendances' => $attendances,
            'employees' => User::orderBy('name')->get(),
            'monthlySummary' => $this->monthlySummary($month),
            'selectedMonth' => $month->format('Y-m'),
            'filters' => [
                'date_from' => $dateFrom->toDateString(),
                'date_to' => $dateTo->toDateString(),
                'employee_id' => $validated['employee_id'] ?? '',
                'status' => $validated['status'] ?? '',
            ],
        ]);
    }

    public function override(Request $request, Attendance $attendance): RedirectResponse
    {
        $validated = $request->validate([
            'field_name' => ['required', 'in:attendance_date,check_in_at,check_out_at,status,total_work_minutes,notes'],
            'new_value' => ['nullable', 'string'],
            'reason' => ['required', 'string', 'min:10'],
        ]);

        $field = $validated['field_name'];
        $oldValue = $attendance->{$field};
        $newValue = $validated['new_value'];

        if (in_array($field, ['attendance_date'], true) && $newValue) {
            $newValue = Carbon::parse($newValue)->toDateString();
        }

        if (in_array($field, ['check_in_at', 'check_out_at'], true) && $newValue) {
            $newValue = Carbon::parse($newValue);
        }

        if ($field === 'total_work_minutes' && $newValue !== null) {
            $newValue = (int) $newValue;
        }

        $attendance->{$field} = $newValue;

        if ($attendance->check_in_at && $attendance->check_out_at) {
            $attendance->total_work_minutes = $attendance->check_in_at->diffInMinutes($attendance->check_out_at);
        }

        if ($attendance->check_in_at) {
            $threshold = Carbon::parse($attendance->attendance_date)->setTime(9, 0, 0);
            $attendance->is_late = $attendance->check_in_at->greaterThan($threshold);
            $attendance->late_minutes = $attendance->is_late ? $threshold->diffInMinutes($attendance->check_in_at) : 0;
        }

        $attendance->save();

        AttendanceOverride::create([
            'attendance_id' => $attendance->id,
            'overridden_by' => Auth::id(),
            'field_name' => $field,
            'old_value' => $oldValue ? (string) $oldValue : null,
            'new_value' => $newValue ? (string) $newValue : null,
            'reason' => $validated['reason'],
        ]);

        return back()->with('success', 'Attendance record overridden successfully.');
    }

    private function monthlySummary(Carbon $month): array
    {
        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();

        $records = Attendance::with('user')
            ->whereBetween('attendance_date', [$start, $end])
            ->get()
            ->groupBy('user_id');

        $approvedLeaves = LeaveRequest::where('status', 'approved')
            ->whereDate('start_date', '<=', $end)
            ->whereDate('end_date', '>=', $start)
            ->get()
            ->groupBy('user_id');

        $expectedWorkingDays = collect(CarbonPeriod::create($start, $end))
            ->filter(fn (Carbon $day) => $day->isWeekday())
            ->count();

        return User::orderBy('name')
            ->get()
            ->map(function (User $user) use ($records, $approvedLeaves, $expectedWorkingDays) {
                $employeeRecords = $records->get($user->id, collect());
                $leaveDays = (int) $approvedLeaves->get($user->id, collect())->sum('total_days');
                $presentDays = $employeeRecords->where('status', 'present')->count();
                $lateDays = $employeeRecords->where('is_late', true)->count();

                return [
                    'employee' => $user,
                    'present_days' => $presentDays,
                    'late_days' => $lateDays,
                    'absent_days' => max(0, $expectedWorkingDays - $presentDays - $leaveDays),
                    'total_work_hours' => round(((int) $employeeRecords->sum('total_work_minutes')) / 60, 2),
                ];
            })
            ->toArray();
    }
}
