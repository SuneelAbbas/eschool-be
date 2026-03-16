<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Institute extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'logo',
        'address',
        'contact_email',
        'contact_phone',
        'type',
        'city',
        'no_of_students',
        'description',
        'status',
        'user_id',
        'plan_id',
    ];
}
