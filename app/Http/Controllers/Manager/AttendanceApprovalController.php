<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class AttendanceApprovalController extends Controller
{
    public function index()
    {
        $manager = Auth::user();

        $attendances = Attendance::with('user.department')
            ->whereDate('attendance_date', now()->toDateString())
            ->whereNull('manager_approved_at')
            ->whereHas('user', fn ($query) => $query->where('manager_id', $manager->id))
            ->orderBy('check_in_at')
            ->paginate(20);

        return view('manager.attendance-approvals.index', [
            'attendances' => $attendances,
        ]);
    }

    public function approve(Attendance $attendance): RedirectResponse
    {
        $manager = Auth::user();

        if ($attendance->user->manager_id !== $manager->id) {
            abort(403, 'You can only approve attendance for your own team.');
        }

        $attendance->fill([
            'manager_approved_by' => $manager->id,
            'manager_approved_at' => now(),
        ])->save();

        return back()->with('success', 'Attendance approved successfully.');
    }
}
