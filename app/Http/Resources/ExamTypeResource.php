<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExamTypeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'type' => $this->type,
            'type_label' => $this->getTypeLabel(),
            'max_marks' => $this->max_marks,
            'description' => $this->description,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }

    private function getTypeLabel(): string
    {
        return match ($this->type) {
            'unit_test' => 'Unit Test',
            'terminal' => 'Terminal',
            'annual' => 'Annual',
            'board_prep' => 'Board Prep',
            default => ucfirst(str_replace('_', ' ', $this->type)),
        };
    }
}
