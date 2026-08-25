<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DailyReportEntryRequest extends FormRequest
{
    public const SLOT_STARTS = [
        '09:30:00', '10:30:00', '11:30:00', '12:30:00', '13:30:00',
        '14:30:00', '15:30:00', '16:30:00', '17:30:00',
    ];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'report_date' => ['required', 'date'],
            'slot_start' => ['required', 'date_format:H:i:s', Rule::in(self::SLOT_STARTS)],
            'task_title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'project_id' => ['nullable', 'integer', 'exists:projects,id'],
        ];
    }
}
