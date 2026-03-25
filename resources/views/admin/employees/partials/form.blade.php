<div>
    <label class="block text-sm text-gray-600">Name</label>
    <input type="text" name="name" class="mt-1 w-full rounded-md border-gray-300" value="{{ old('name', $employee->name ?? '') }}" required>
    @error('name') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
</div>

<div>
    <label class="block text-sm text-gray-600">Email</label>
    <input type="email" name="email" class="mt-1 w-full rounded-md border-gray-300" value="{{ old('email', $employee->email ?? '') }}" required>
    @error('email') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
</div>

<div>
    <label class="block text-sm text-gray-600">Employment Code</label>
    <input type="text" name="employment_code" class="mt-1 w-full rounded-md border-gray-300" value="{{ old('employment_code', $employee->employment_code ?? '') }}">
</div>

<div>
    <label class="block text-sm text-gray-600">Role</label>
    <select name="role" class="mt-1 w-full rounded-md border-gray-300" required>
        @foreach ($roles as $role)
            <option value="{{ $role->name }}" @selected(old('role', $employee->roles->first()->name ?? 'Employee') === $role->name)>{{ $role->name }}</option>
        @endforeach
    </select>
</div>

<div>
    <label class="block text-sm text-gray-600">Department</label>
    <select name="department_id" class="mt-1 w-full rounded-md border-gray-300">
        <option value="">Select department</option>
        @foreach ($departments as $department)
            <option value="{{ $department->id }}" @selected((string) old('department_id', $employee->department_id ?? '') === (string) $department->id)>{{ $department->name }}</option>
        @endforeach
    </select>
</div>

<div>
    <label class="block text-sm text-gray-600">Manager</label>
    <select name="manager_id" class="mt-1 w-full rounded-md border-gray-300">
        <option value="">No manager</option>
        @foreach ($managers as $manager)
            <option value="{{ $manager->id }}" @selected((string) old('manager_id', $employee->manager_id ?? '') === (string) $manager->id)>{{ $manager->name }}</option>
        @endforeach
    </select>
</div>

<div>
    <label class="block text-sm text-gray-600">Status</label>
    <select name="is_active" class="mt-1 w-full rounded-md border-gray-300" required>
        <option value="1" @selected((string) old('is_active', isset($employee) ? (int) $employee->is_active : 1) === '1')>Active</option>
        <option value="0" @selected((string) old('is_active', isset($employee) ? (int) $employee->is_active : 1) === '0')>Inactive</option>
    </select>
</div>

<div>
    <label class="block text-sm text-gray-600">Password {{ isset($employee) ? '(leave blank to keep current)' : '' }}</label>
    <input type="password" name="password" class="mt-1 w-full rounded-md border-gray-300" {{ isset($employee) ? '' : 'required' }}>
    @error('password') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
</div>
