<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExamResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'exam_type_id' => $this->exam_type_id,
            'exam_type' => new ExamTypeResource($this->whenLoaded('examType')),
            'grade_id' => $this->grade_id,
            'grade' => new GradeResource($this->whenLoaded('grade')),
            'section_id' => $this->section_id,
            'section' => new SectionResource($this->whenLoaded('section')),
            'title' => $this->title,
            'description' => $this->description,
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'total_marks' => $this->total_marks,
            'status' => $this->status,
            'status_label' => $this->getStatusLabel(),
            'exam_subjects' => ExamSubjectResource::collection($this->whenLoaded('examSubjects')),
            'results_count' => $this->when(isset($this->results_count), $this->results_count),
            'students_count' => $this->when(isset($this->students_count), $this->students_count),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }

    private function getStatusLabel(): string
    {
        return match ($this->status) {
            'scheduled' => 'Scheduled',
            'ongoing' => 'Ongoing',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            default => ucfirst($this->status),
        };
    }
}
