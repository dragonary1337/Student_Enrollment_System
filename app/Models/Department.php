<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    protected $primaryKey = 'department_id';

    public $timestamps = false;

    protected $fillable = ['department_code', 'department_name'];

    public function programs(): HasMany
    {
        return $this->hasMany(Program::class, 'department_id', 'department_id');
    }

    public function subjects(): HasMany
    {
        return $this->hasMany(Subject::class, 'department_id', 'department_id');
    }

    public function instructors(): HasMany
    {
        return $this->hasMany(Instructor::class, 'department_id', 'department_id');
    }
}
