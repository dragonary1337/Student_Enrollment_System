<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Instructor extends Model
{
    protected $primaryKey = 'instructor_id';

    public $timestamps = false;

    protected $fillable = ['first_name', 'middle_name', 'last_name', 'email', 'department_id'];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id', 'department_id');
    }

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class, 'instructor_id', 'instructor_id');
    }
}
