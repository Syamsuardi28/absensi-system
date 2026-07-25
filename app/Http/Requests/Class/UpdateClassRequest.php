<?php

namespace App\Http\Requests\Class;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClassRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'school_year_id' => ['sometimes', 'exists:school_years,id'],
            'homeroom_teacher_id' => ['nullable', 'exists:teachers,id'],
        ];
    }
}
