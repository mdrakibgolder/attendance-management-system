<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Attendance Dashboard
        </h2>
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

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="rounded-xl bg-white shadow-sm p-5">
                    <p class="text-sm text-gray-500">Present Days (Month)</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $monthlySummary['present_days'] }}</p>
                </div>
                <div class="rounded-xl bg-white shadow-sm p-5">
                    <p class="text-sm text-gray-500">Late Days (Month)</p>
                    <p class="text-2xl font-bold text-amber-600">{{ $monthlySummary['late_days'] }}</p>
                </div>
                <div class="rounded-xl bg-white shadow-sm p-5">
                    <p class="text-sm text-gray-500">Absent Days (Month)</p>
                    <p class="text-2xl font-bold text-rose-600">{{ $monthlySummary['absent_days'] }}</p>
                </div>
                <div class="rounded-xl bg-white shadow-sm p-5">
                    <p class="text-sm text-gray-500">Working Hours (Month)</p>
                    <p class="text-2xl font-bold text-sky-700">{{ $monthlySummary['working_hours'] }}</p>
                </div>
            </div>

            <div class="rounded-xl bg-white shadow-sm p-6 space-y-4">
                <h3 class="text-lg font-semibold text-gray-900">Today's Attendance</h3>
                <p class="text-sm text-gray-600">
                    @if ($todaysAttendance?->check_in_at)
                        Checked in at {{ $todaysAttendance->check_in_at->format('h:i A') }}
                    @else
                        You have not checked in today.
                    @endif
                    @if ($todaysAttendance?->check_out_at)
                        | Checked out at {{ $todaysAttendance->check_out_at->format('h:i A') }}
                    @endif
                </p>

                <div class="flex flex-wrap gap-3">
                    <form method="POST" action="{{ route('attendance.check-in') }}" class="geo-form">
                        @csrf
                        <input type="hidden" name="latitude">
                        <input type="hidden" name="longitude">
                        <button type="submit" class="rounded-md bg-emerald-600 px-4 py-2 text-white hover:bg-emerald-700">Check In</button>
                    </form>

                    <form method="POST" action="{{ route('attendance.check-out') }}" class="geo-form">
                        @csrf
                        <input type="hidden" name="latitude">
                        <input type="hidden" name="longitude">
                        <button type="submit" class="rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">Check Out</button>
                    </form>
                </div>
            </div>

            @if (auth()->user()->isAdmin())
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="rounded-xl bg-white shadow-sm p-5">
                        <p class="text-sm text-gray-500">Present Today</p>
                        <p class="text-2xl font-bold">{{ $adminStats['present_count'] }}</p>
                    </div>
                    <div class="rounded-xl bg-white shadow-sm p-5">
                        <p class="text-sm text-gray-500">Late Today</p>
                        <p class="text-2xl font-bold text-amber-600">{{ $adminStats['late_count'] }}</p>
                    </div>
                    <div class="rounded-xl bg-white shadow-sm p-5">
                        <p class="text-sm text-gray-500">Incomplete Today</p>
                        <p class="text-2xl font-bold text-rose-600">{{ $adminStats['incomplete_count'] }}</p>
                    </div>
                    <div class="rounded-xl bg-white shadow-sm p-5">
                        <p class="text-sm text-gray-500">Currently Checked In</p>
                        <p class="text-2xl font-bold text-sky-700">{{ $adminStats['checked_in_live'] }}</p>
                    </div>
                </div>

                <div class="rounded-xl bg-white shadow-sm overflow-hidden">
                    <div class="p-4 border-b">
                        <h3 class="text-lg font-semibold">Live Checked-In Employees</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50 text-gray-600">
                                <tr>
                                    <th class="px-4 py-3 text-left">Employee</th>
                                    <th class="px-4 py-3 text-left">Department</th>
                                    <th class="px-4 py-3 text-left">Check In Time</th>
                                    <th class="px-4 py-3 text-left">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @forelse ($checkedInToday as $record)
                                    <tr>
                                        <td class="px-4 py-3">{{ $record->user->name }}</td>
                                        <td class="px-4 py-3">{{ $record->user->department->name ?? 'N/A' }}</td>
                                        <td class="px-4 py-3">{{ optional($record->check_in_at)->format('h:i A') }}</td>
                                        <td class="px-4 py-3">
                                            <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">Checked In</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-6 text-center text-gray-500">No one is currently checked in.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <script>
        document.querySelectorAll('.geo-form').forEach((form) => {
            form.addEventListener('submit', (event) => {
                const latInput = form.querySelector('input[name="latitude"]');
                const lngInput = form.querySelector('input[name="longitude"]');

                if (!navigator.geolocation) {
                    return;
                }

                event.preventDefault();
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        latInput.value = position.coords.latitude;
                        lngInput.value = position.coords.longitude;
                        form.submit();
                    },
                    () => {
                        form.submit();
                    },
                    { enableHighAccuracy: true, timeout: 5000 }
                );
            }, { once: true });
        });
    </script>
</x-app-layout>
