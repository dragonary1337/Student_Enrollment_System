<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Section extends Model
{
    protected $primaryKey = 'section_id';

    public $timestamps = false;

    protected $fillable = [
        'section_code', 'subject_id', 'instructor_id', 'school_year', 'semester',
        'schedule_day', 'start_time', 'end_time', 'room', 'max_capacity',
    ];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id', 'subject_id');
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class, 'instructor_id', 'instructor_id');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class, 'section_id', 'section_id');
    }
}
