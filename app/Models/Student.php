<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    protected $primaryKey = 'student_id';

    public $timestamps = false;

    protected $fillable = [
        'student_no', 'first_name', 'middle_name', 'last_name', 'gender', 'email',
        'contact_no', 'program_id', 'year_level', 'status',
    ];

    protected function casts(): array
    {
        return ['date_registered' => 'datetime'];
    }

    protected function fullName(): Attribute
    {
        return Attribute::get(fn (): string => trim(implode(' ', array_filter([
            $this->first_name,
            $this->middle_name,
            $this->last_name,
        ]))));
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class, 'program_id', 'program_id');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class, 'student_id', 'student_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'student_id', 'student_id');
    }
}
