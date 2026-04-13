<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExamResultResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'exam_id' => $this->exam_id,
            'exam' => new ExamResource($this->whenLoaded('exam')),
            'student_id' => $this->student_id,
            'student' => new StudentResource($this->whenLoaded('student')),
            'subject_id' => $this->subject_id,
            'subject' => new SubjectResource($this->whenLoaded('subject')),
            'marks_obtained' => (float) $this->marks_obtained,
            'max_marks' => (float) $this->max_marks,
            'percentage' => (float) $this->percentage,
            'grade' => $this->grade,
            'remarks' => $this->remarks,
            'is_pass' => $this->grade !== 'F',
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
