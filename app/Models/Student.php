<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'firstName',
        'lastName',
        'email',
        'registration_date',
        'registration_number',
        'roll_no',
        'grade_id',
        'section_id',
        'gender',
        'mobile_number',
        'parents_name',
        'parents_mobile_number',
        'date_of_birth',
        'blood_group',
        'address',
        'upload',
        'school_id',
    ];
}
