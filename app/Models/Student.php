<?php

namespace App\Models;

use Database\Factories\StudentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Student extends Model
{
    /** @use HasFactory<StudentFactory> */
    use HasFactory;

    protected $fillable = ['user_id', 'student_number', 'name', 'email', 'phone', 'program', 'year_level', 'status'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
