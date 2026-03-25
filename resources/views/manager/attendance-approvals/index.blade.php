<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Team Attendance Approvals</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 rounded-lg border border-green-200 bg-green-50 p-4 text-green-700">{{ session('success') }}</div>
            @endif

            <div class="rounded-xl bg-white shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-gray-600">
                            <tr>
                                <th class="px-4 py-3 text-left">Employee</th>
                                <th class="px-4 py-3 text-left">Department</th>
                                <th class="px-4 py-3 text-left">Check In</th>
                                <th class="px-4 py-3 text-left">Check Out</th>
                                <th class="px-4 py-3 text-left">Status</th>
                                <th class="px-4 py-3 text-left">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse ($attendances as $attendance)
                                <tr>
                                    <td class="px-4 py-3">{{ $attendance->user->name }}</td>
                                    <td class="px-4 py-3">{{ $attendance->user->department->name ?? '-' }}</td>
                                    <td class="px-4 py-3">{{ optional($attendance->check_in_at)->format('h:i A') ?? '-' }}</td>
                                    <td class="px-4 py-3">{{ optional($attendance->check_out_at)->format('h:i A') ?? '-' }}</td>
                                    <td class="px-4 py-3 capitalize">{{ $attendance->status }}</td>
                                    <td class="px-4 py-3">
                                        <form method="POST" action="{{ route('manager.attendance-approvals.approve', $attendance) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button class="rounded bg-indigo-600 px-3 py-1 text-white">Approve</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-4 py-6 text-center text-gray-500">No pending attendance approvals.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-4">{{ $attendances->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
