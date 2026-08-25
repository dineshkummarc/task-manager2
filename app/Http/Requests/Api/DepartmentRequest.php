<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['name' => ['required', 'string', 'max:255'], 'code' => ['required', 'string', 'max:255', Rule::unique('departments', 'code')->ignore($this->route('department'))], 'project_id' => ['required', 'exists:projects,id'], 'responsibilities' => ['nullable', 'array'], 'responsibilities.*' => ['string', 'max:255']];
    }
}
