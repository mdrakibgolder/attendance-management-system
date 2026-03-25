<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Create Employee</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="rounded-xl bg-white shadow-sm p-6">
                <form method="POST" action="{{ route('admin.employees.store') }}" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @csrf
                    @include('admin.employees.partials.form')
                    <div class="md:col-span-2 flex gap-3">
                        <button class="rounded-md bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700">Save</button>
                        <a href="{{ route('admin.employees.index') }}" class="rounded-md bg-gray-200 px-4 py-2 text-gray-700">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
