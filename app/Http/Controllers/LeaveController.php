<?php

namespace App\Http\Controllers;

use App\Mail\LeaveStatusUpdatedMail;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class LeaveController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $myLeaves = LeaveRequest::with('leaveType')
            ->where('user_id', $user->id)
            ->latest('created_at')
            ->paginate(12, ['*'], 'my_page');

        $teamLeaves = collect();

        if ($user->isManager() || $user->isAdmin()) {
            $teamLeaves = LeaveRequest::with(['leaveType', 'user'])
                ->where('status', 'pending')
                ->when($user->isManager() && ! $user->isAdmin(), function ($query) use ($user) {
                    $query->whereHas('user', fn ($q) => $q->where('manager_id', $user->id));
                })
                ->orderBy('start_date')
                ->paginate(12, ['*'], 'team_page');
        }

        return view('leaves.index', [
            'leaveTypes' => LeaveType::orderBy('name')->get(),
            'myLeaves' => $myLeaves,
            'teamLeaves' => $teamLeaves,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'leave_type_id' => ['required', 'exists:leave_types,id'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['required', 'string', 'min:10'],
        ]);

        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);

        LeaveRequest::create([
            'user_id' => Auth::id(),
            'leave_type_id' => $validated['leave_type_id'],
            'manager_id' => Auth::user()->manager_id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_days' => $startDate->diffInDays($endDate) + 1,
            'reason' => $validated['reason'],
            'status' => 'pending',
        ]);

        return back()->with('success', 'Leave request submitted for approval.');
    }

    public function updateStatus(Request $request, LeaveRequest $leaveRequest): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:approved,rejected'],
            'manager_remark' => ['nullable', 'string', 'max:1000'],
        ]);

        $actor = Auth::user();

        if ($actor->isManager() && ! $actor->isAdmin() && $leaveRequest->user->manager_id !== $actor->id) {
            abort(403, 'You are not allowed to approve this leave request.');
        }

        $leaveRequest->fill([
            'status' => $validated['status'],
            'manager_id' => $actor->id,
            'manager_remark' => $validated['manager_remark'] ?? null,
            'decided_at' => now(),
        ])->save();

        Mail::to($leaveRequest->user->email)->send(new LeaveStatusUpdatedMail($leaveRequest));

        return back()->with('success', 'Leave request status updated.');
    }
}
