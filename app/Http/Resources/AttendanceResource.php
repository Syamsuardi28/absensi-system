<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'schedule_id' => $this->schedule_id,
            'schedule' => new ScheduleResource($this->whenLoaded('schedule')),
            'type' => $this->type?->value,
            'status' => $this->status?->value,
            'status_label' => $this->status?->label(),
            'scan_time' => $this->scan_time?->format('Y-m-d H:i:s'),
            'notes' => $this->notes,
            'recorded_by' => new UserResource($this->whenLoaded('recordedBy')),
            'created_at' => $this->created_at,
        ];
    }
}
