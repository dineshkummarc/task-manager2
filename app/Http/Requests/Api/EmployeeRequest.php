<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class EmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('employee')?->id;

        return [
            'name' => ['required', 'string', 'max:255'], 'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'phone' => ['nullable', 'string', 'max:25'], 'password' => [$userId ? 'nullable' : 'required', Password::min(6)->mixedCase()->numbers()->symbols()],
            'department_id' => ['nullable', 'exists:departments,id'], 'project_id' => ['nullable', 'exists:projects,id'],
            'job_responsibility_id' => ['nullable', 'exists:job_responsibilities,id'], 'job_responsibility' => ['nullable', 'string', 'max:255'],
            'roles' => ['required', 'array', 'min:1'], 'roles.*' => ['string', 'exists:roles,name'],
            'permissions' => ['nullable', 'array'], 'permissions.*' => ['string', 'exists:permissions,name'],
        ];
    }
}
