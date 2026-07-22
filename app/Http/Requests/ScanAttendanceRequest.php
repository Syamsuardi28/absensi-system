<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ScanAttendanceRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'qr_token' => ['required', 'string', 'max:255'],
            'schedule_id' => ['nullable', 'exists:schedules,id'],
        ];
    }
}
