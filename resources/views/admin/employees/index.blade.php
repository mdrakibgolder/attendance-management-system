<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Employee Management</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('success'))
                <div class="rounded-lg border border-green-200 bg-green-50 p-4 text-green-700">{{ session('success') }}</div>
            @endif

            <div class="flex justify-end">
                <a href="{{ route('admin.employees.create') }}" class="rounded-md bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700">Add Employee</a>
            </div>

            <div class="rounded-xl bg-white shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-gray-600">
                            <tr>
                                <th class="px-4 py-3 text-left">Name</th>
                                <th class="px-4 py-3 text-left">Email</th>
                                <th class="px-4 py-3 text-left">Role</th>
                                <th class="px-4 py-3 text-left">Department</th>
                                <th class="px-4 py-3 text-left">Manager</th>
                                <th class="px-4 py-3 text-left">Status</th>
                                <th class="px-4 py-3 text-left">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse ($employees as $employee)
                                <tr>
                                    <td class="px-4 py-3">{{ $employee->name }}</td>
                                    <td class="px-4 py-3">{{ $employee->email }}</td>
                                    <td class="px-4 py-3">{{ $employee->roles->pluck('name')->implode(', ') }}</td>
                                    <td class="px-4 py-3">{{ $employee->department->name ?? '-' }}</td>
                                    <td class="px-4 py-3">{{ $employee->manager->name ?? '-' }}</td>
                                    <td class="px-4 py-3">{{ $employee->is_active ? 'Active' : 'Inactive' }}</td>
                                    <td class="px-4 py-3">
                                        <a href="{{ route('admin.employees.edit', $employee) }}" class="text-indigo-600 hover:underline">Edit</a>
                                        <form method="POST" action="{{ route('admin.employees.destroy', $employee) }}" class="inline-block ms-3" onsubmit="return confirm('Delete this employee?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="text-rose-600 hover:underline">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-4 py-6 text-center text-gray-500">No employees found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-4">{{ $employees->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
