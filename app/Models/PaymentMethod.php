<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentMethod extends Model
{
    protected $primaryKey = 'payment_method_id';

    public $timestamps = false;

    protected $fillable = ['method_name', 'method_available'];

    protected function casts(): array
    {
        return ['method_available' => 'boolean'];
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'payment_method_id', 'payment_method_id');
    }
}
