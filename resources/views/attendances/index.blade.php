<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">My Attendance History</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <div class="rounded-xl bg-white shadow-sm p-4">
                <form method="GET" class="flex flex-wrap gap-3 items-end">
                    <div>
                        <label for="month" class="block text-sm text-gray-600">Month</label>
                        <input type="month" id="month" name="month" value="{{ $selectedMonth }}" class="mt-1 rounded-md border-gray-300" />
                    </div>
                    <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700">Filter</button>
                </form>
            </div>

            <div class="rounded-xl bg-white shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-gray-600">
                            <tr>
                                <th class="px-4 py-3 text-left">Date</th>
                                <th class="px-4 py-3 text-left">Check In</th>
                                <th class="px-4 py-3 text-left">Check Out</th>
                                <th class="px-4 py-3 text-left">Late</th>
                                <th class="px-4 py-3 text-left">Status</th>
                                <th class="px-4 py-3 text-left">Work Hours</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse ($attendances as $attendance)
                                <tr>
                                    <td class="px-4 py-3">{{ $attendance->attendance_date->format('d M Y') }}</td>
                                    <td class="px-4 py-3">{{ optional($attendance->check_in_at)->format('h:i A') ?? '-' }}</td>
                                    <td class="px-4 py-3">{{ optional($attendance->check_out_at)->format('h:i A') ?? '-' }}</td>
                                    <td class="px-4 py-3">{{ $attendance->is_late ? 'Yes (' . $attendance->late_minutes . ' min)' : 'No' }}</td>
                                    <td class="px-4 py-3 capitalize">{{ $attendance->status }}</td>
                                    <td class="px-4 py-3">{{ $attendance->total_work_minutes ? round($attendance->total_work_minutes / 60, 2) : '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-6 text-center text-gray-500">No attendance records found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-4">{{ $attendances->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
