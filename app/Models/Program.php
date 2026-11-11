<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Program extends Model
{
    protected $primaryKey = 'program_id';

    public $timestamps = false;

    protected $fillable = ['program_code', 'program_name', 'department_id'];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id', 'department_id');
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'program_id', 'program_id');
    }
}
