<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReportCardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'exam_id' => $this->exam_id,
            'exam' => new ExamResource($this->whenLoaded('exam')),
            'student_id' => $this->student_id,
            'student' => new StudentResource($this->whenLoaded('student')),
            'total_marks' => (float) $this->total_marks,
            'marks_obtained' => (float) $this->marks_obtained,
            'percentage' => (float) $this->percentage,
            'grade' => $this->grade,
            'section_position' => $this->section_position,
            'grade_position' => $this->grade_position,
            'remarks' => $this->remarks,
            'subject_results' => $this->subject_results,
            'is_pass' => $this->grade !== 'F',
            'generated_at' => $this->generated_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
