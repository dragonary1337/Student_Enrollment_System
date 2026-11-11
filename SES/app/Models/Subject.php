<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    protected $primaryKey = 'subject_id';

    public $timestamps = false;

    protected $fillable = ['subject_code', 'subject_name', 'units', 'department_id'];

    protected function casts(): array
    {
        return ['units' => 'decimal:1'];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id', 'department_id');
    }

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class, 'subject_id', 'subject_id');
    }
}
