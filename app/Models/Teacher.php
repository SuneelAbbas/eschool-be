<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'join_date',
        'cnic_number',
        'subject',
        'gender',
        'mobile_number',
        'date_of_birth',
        'blood_group',
        'address',
        'academic_qualification',
        'school_id',
    ];
}
