<?php

namespace App\Http\Requests\SchoolYear;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSchoolYearRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['sometimes', 'date', 'after:start_date'],
            'is_active' => ['boolean'],
        ];
    }
}
