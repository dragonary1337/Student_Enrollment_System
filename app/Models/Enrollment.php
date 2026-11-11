<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Enrollment extends Model
{
    protected $primaryKey = 'enrollment_id';

    public $timestamps = false;

    protected $fillable = ['student_id', 'section_id', 'status', 'processed_by', 'enrollment_datetime'];

    protected function casts(): array
    {
        return ['enrollment_datetime' => 'datetime'];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'section_id', 'section_id');
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
