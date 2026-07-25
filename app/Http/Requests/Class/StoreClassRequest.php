<?php

namespace App\Http\Requests\Class;

use Illuminate\Foundation\Http\FormRequest;

class StoreClassRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'school_year_id' => ['required', 'exists:school_years,id'],
            'homeroom_teacher_id' => ['nullable', 'exists:teachers,id'],
        ];
    }
}
