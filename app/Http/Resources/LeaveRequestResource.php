<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaveRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'type' => $this->type?->value,
            'type_label' => $this->type?->label(),
            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'reason' => $this->reason,
            'attachment_path' => $this->attachment_path,
            'status' => $this->status?->value,
            'status_label' => $this->status?->label(),
            'approved_by' => new UserResource($this->whenLoaded('approvedBy')),
            'rejection_note' => $this->rejection_note,
            'approved_at' => $this->approved_at?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at,
        ];
    }
}
