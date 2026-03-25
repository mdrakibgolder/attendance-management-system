<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Leave Management</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="rounded-lg border border-green-200 bg-green-50 p-4 text-green-700">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-red-700">
                    <ul class="list-disc ms-6">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="rounded-xl bg-white shadow-sm p-6">
                <h3 class="text-lg font-semibold mb-4">Apply for Leave</h3>
                <form method="POST" action="{{ route('leaves.store') }}" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @csrf
                    <div>
                        <label class="block text-sm text-gray-600">Leave Type</label>
                        <select name="leave_type_id" class="mt-1 w-full rounded-md border-gray-300" required>
                            <option value="">Select type</option>
                            @foreach ($leaveTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600">Start Date</label>
                        <input type="date" name="start_date" class="mt-1 w-full rounded-md border-gray-300" required>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600">End Date</label>
                        <input type="date" name="end_date" class="mt-1 w-full rounded-md border-gray-300" required>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm text-gray-600">Reason</label>
                        <textarea name="reason" rows="4" class="mt-1 w-full rounded-md border-gray-300" required></textarea>
                    </div>
                    <div class="md:col-span-2">
                        <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700">Submit Leave Request</button>
                    </div>
                </form>
            </div>

            <div class="rounded-xl bg-white shadow-sm overflow-hidden">
                <div class="p-4 border-b"><h3 class="text-lg font-semibold">My Leave Requests</h3></div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-gray-600">
                            <tr>
                                <th class="px-4 py-3 text-left">Type</th>
                                <th class="px-4 py-3 text-left">Period</th>
                                <th class="px-4 py-3 text-left">Days</th>
                                <th class="px-4 py-3 text-left">Status</th>
                                <th class="px-4 py-3 text-left">Manager Remark</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse ($myLeaves as $leave)
                                <tr>
                                    <td class="px-4 py-3">{{ $leave->leaveType->name }}</td>
                                    <td class="px-4 py-3">{{ $leave->start_date->format('d M Y') }} - {{ $leave->end_date->format('d M Y') }}</td>
                                    <td class="px-4 py-3">{{ $leave->total_days }}</td>
                                    <td class="px-4 py-3 capitalize">{{ $leave->status }}</td>
                                    <td class="px-4 py-3">{{ $leave->manager_remark ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-4 py-6 text-center text-gray-500">No leave requests found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-4">{{ $myLeaves->links() }}</div>
            </div>

            @if (auth()->user()->isManager() || auth()->user()->isAdmin())
                <div class="rounded-xl bg-white shadow-sm overflow-hidden">
                    <div class="p-4 border-b"><h3 class="text-lg font-semibold">Pending Team Leave Requests</h3></div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50 text-gray-600">
                                <tr>
                                    <th class="px-4 py-3 text-left">Employee</th>
                                    <th class="px-4 py-3 text-left">Type</th>
                                    <th class="px-4 py-3 text-left">Period</th>
                                    <th class="px-4 py-3 text-left">Reason</th>
                                    <th class="px-4 py-3 text-left">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @forelse ($teamLeaves as $leave)
                                    <tr>
                                        <td class="px-4 py-3">{{ $leave->user->name }}</td>
                                        <td class="px-4 py-3">{{ $leave->leaveType->name }}</td>
                                        <td class="px-4 py-3">{{ $leave->start_date->format('d M') }} - {{ $leave->end_date->format('d M') }}</td>
                                        <td class="px-4 py-3">{{ $leave->reason }}</td>
                                        <td class="px-4 py-3">
                                            <form method="POST" action="{{ route('leaves.status', $leave) }}" class="flex flex-wrap gap-2">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="approved">
                                                <button class="rounded bg-emerald-600 px-3 py-1 text-white">Approve</button>
                                            </form>
                                            <form method="POST" action="{{ route('leaves.status', $leave) }}" class="mt-2 flex gap-2">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="rejected">
                                                <input type="text" name="manager_remark" placeholder="Reason" class="rounded border-gray-300 text-xs">
                                                <button class="rounded bg-rose-600 px-3 py-1 text-white">Reject</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="px-4 py-6 text-center text-gray-500">No pending requests.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if ($teamLeaves instanceof \Illuminate\Contracts\Pagination\Paginator)
                        <div class="p-4">{{ $teamLeaves->links() }}</div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
