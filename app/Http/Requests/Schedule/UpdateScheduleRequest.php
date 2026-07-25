<?php

namespace App\Http\Requests\Schedule;

use Illuminate\Foundation\Http\FormRequest;

class UpdateScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $schedule = $this->route('schedule');

        return [
            'class_id' => ['sometimes', 'exists:school_classes,id'],
            'subject_id' => ['sometimes', 'exists:subjects,id'],
            'teacher_id' => ['sometimes', 'exists:teachers,id'],
            'day' => ['sometimes', 'in:senin,selasa,rabu,kamis,jumat,sabtu'],
            'start_time' => ['sometimes', 'date_format:H:i'],
            'end_time' => ['sometimes', 'date_format:H:i', function ($attribute, $value, $fail) use ($schedule) {
                $start = $this->input('start_time', $schedule?->start_time);
                if ($start && $value <= $start) {
                    $fail('Jam selesai harus setelah jam mulai.');
                }
            }],
        ];
    }
}
