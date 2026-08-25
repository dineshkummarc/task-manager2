<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $task = $this->route('task');

        return $this->user() !== null && $task !== null && (int) $task->assigned_to === (int) $this->user()->id;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(['pending', 'in_progress', 'blockage', 'completed'])],
            'block_reason' => ['nullable', 'string', 'max:5000', 'required_if:status,blockage'],
            'completion_note' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
