<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SchoolClassResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'school_year_id' => $this->school_year_id,
            'school_year' => new SchoolYearResource($this->whenLoaded('schoolYear')),
            'homeroom_teacher_id' => $this->homeroom_teacher_id,
            'homeroom_teacher' => new TeacherResource($this->whenLoaded('homeroomTeacher')),
            'students_count' => $this->when($this->students_count !== null, $this->students_count),
            'created_at' => $this->created_at,
        ];
    }
}
