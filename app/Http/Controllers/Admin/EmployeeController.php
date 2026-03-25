<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = User::with(['department', 'manager', 'roles'])
            ->orderBy('name')
            ->paginate(15);

        return view('admin.employees.index', [
            'employees' => $employees,
        ]);
    }

    public function create()
    {
        return view('admin.employees.create', [
            'departments' => Department::orderBy('name')->get(),
            'managers' => User::role('Manager')->orderBy('name')->get(),
            'roles' => Role::whereIn('name', ['Admin', 'Manager', 'Employee'])->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'employment_code' => ['nullable', 'string', 'max:50', 'unique:users,employment_code'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'manager_id' => ['nullable', 'exists:users,id'],
            'is_active' => ['required', 'boolean'],
            'role' => ['required', 'in:Admin,Manager,Employee'],
            'password' => ['required', Password::defaults()],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'employment_code' => $validated['employment_code'] ?? null,
            'department_id' => $validated['department_id'] ?? null,
            'manager_id' => $validated['manager_id'] ?? null,
            'is_active' => (bool) $validated['is_active'],
            'password' => Hash::make($validated['password']),
        ]);

        $user->syncRoles([$validated['role']]);

        return redirect()->route('admin.employees.index')->with('success', 'Employee created successfully.');
    }

    public function edit(User $employee)
    {
        return view('admin.employees.edit', [
            'employee' => $employee,
            'departments' => Department::orderBy('name')->get(),
            'managers' => User::role('Manager')->whereKeyNot($employee->id)->orderBy('name')->get(),
            'roles' => Role::whereIn('name', ['Admin', 'Manager', 'Employee'])->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, User $employee): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $employee->id],
            'employment_code' => ['nullable', 'string', 'max:50', 'unique:users,employment_code,' . $employee->id],
            'department_id' => ['nullable', 'exists:departments,id'],
            'manager_id' => ['nullable', 'exists:users,id'],
            'is_active' => ['required', 'boolean'],
            'role' => ['required', 'in:Admin,Manager,Employee'],
            'password' => ['nullable', Password::defaults()],
        ]);

        $employee->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'employment_code' => $validated['employment_code'] ?? null,
            'department_id' => $validated['department_id'] ?? null,
            'manager_id' => $validated['manager_id'] ?? null,
            'is_active' => (bool) $validated['is_active'],
        ]);

        if (! empty($validated['password'])) {
            $employee->password = Hash::make($validated['password']);
        }

        $employee->save();
        $employee->syncRoles([$validated['role']]);

        return redirect()->route('admin.employees.index')->with('success', 'Employee updated successfully.');
    }

    public function destroy(User $employee): RedirectResponse
    {
        $employee->delete();

        return redirect()->route('admin.employees.index')->with('success', 'Employee deleted successfully.');
    }
}
