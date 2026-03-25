<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Attendance Reports</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="rounded-lg border border-green-200 bg-green-50 p-4 text-green-700">{{ session('success') }}</div>
            @endif

            <div class="rounded-xl bg-white shadow-sm p-4">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-3">
                    <input type="date" name="date_from" value="{{ $filters['date_from'] }}" class="rounded-md border-gray-300">
                    <input type="date" name="date_to" value="{{ $filters['date_to'] }}" class="rounded-md border-gray-300">
                    <select name="employee_id" class="rounded-md border-gray-300">
                        <option value="">All employees</option>
                        @foreach ($employees as $employee)
                            <option value="{{ $employee->id }}" @selected((string) $filters['employee_id'] === (string) $employee->id)>{{ $employee->name }}</option>
                        @endforeach
                    </select>
                    <select name="status" class="rounded-md border-gray-300">
                        <option value="">All status</option>
                        @foreach (['present', 'incomplete', 'absent', 'on_leave'] as $status)
                            <option value="{{ $status }}" @selected($filters['status'] === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                    <button class="rounded-md bg-indigo-600 px-4 py-2 text-white">Apply Filters</button>
                </form>
            </div>

            <div class="rounded-xl bg-white shadow-sm p-4">
                <h3 class="font-semibold mb-3">Monthly Summary</h3>
                <form method="GET" class="flex gap-3 mb-4">
                    <input type="month" name="month" value="{{ $selectedMonth }}" class="rounded-md border-gray-300">
                    <button class="rounded-md bg-slate-700 px-3 py-2 text-white">Load Summary</button>
                </form>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-gray-600">
                            <tr>
                                <th class="px-4 py-3 text-left">Employee</th>
                                <th class="px-4 py-3 text-left">Present</th>
                                <th class="px-4 py-3 text-left">Late</th>
                                <th class="px-4 py-3 text-left">Absent</th>
                                <th class="px-4 py-3 text-left">Working Hours</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @foreach ($monthlySummary as $row)
                                <tr>
                                    <td class="px-4 py-3">{{ $row['employee']->name }}</td>
                                    <td class="px-4 py-3">{{ $row['present_days'] }}</td>
                                    <td class="px-4 py-3">{{ $row['late_days'] }}</td>
                                    <td class="px-4 py-3">{{ $row['absent_days'] }}</td>
                                    <td class="px-4 py-3">{{ $row['total_work_hours'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="rounded-xl bg-white shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-gray-600">
                            <tr>
                                <th class="px-4 py-3 text-left">Date</th>
                                <th class="px-4 py-3 text-left">Employee</th>
                                <th class="px-4 py-3 text-left">In/Out</th>
                                <th class="px-4 py-3 text-left">Status</th>
                                <th class="px-4 py-3 text-left">Late</th>
                                <th class="px-4 py-3 text-left">Override</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse ($attendances as $attendance)
                                <tr>
                                    <td class="px-4 py-3">{{ $attendance->attendance_date->format('d M Y') }}</td>
                                    <td class="px-4 py-3">{{ $attendance->user->name }}</td>
                                    <td class="px-4 py-3">{{ optional($attendance->check_in_at)->format('h:i A') }} / {{ optional($attendance->check_out_at)->format('h:i A') }}</td>
                                    <td class="px-4 py-3 capitalize">{{ $attendance->status }}</td>
                                    <td class="px-4 py-3">{{ $attendance->is_late ? 'Yes' : 'No' }}</td>
                                    <td class="px-4 py-3">
                                        <form method="POST" action="{{ route('admin.attendance-reports.override', $attendance) }}" class="space-y-2">
                                            @csrf
                                            @method('PATCH')
                                            <select name="field_name" class="rounded border-gray-300 text-xs">
                                                @foreach (['attendance_date','check_in_at','check_out_at','status','total_work_minutes','notes'] as $field)
                                                    <option value="{{ $field }}">{{ $field }}</option>
                                                @endforeach
                                            </select>
                                            <input type="text" name="new_value" placeholder="New value" class="rounded border-gray-300 text-xs">
                                            <input type="text" name="reason" placeholder="Reason (required)" class="rounded border-gray-300 text-xs" required>
                                            <button class="rounded bg-amber-600 px-3 py-1 text-white text-xs">Override</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-4 py-6 text-center text-gray-500">No attendance records found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-4">{{ $attendances->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
