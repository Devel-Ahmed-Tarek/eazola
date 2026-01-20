<?php

namespace App\Models;

use App\Enums\StatusEnums;
use Illuminate\Database\Eloquent\Model;

class PaymentGateway extends Model
{
    protected $table = 'payment_gateways';
    protected $fillable = ['name','image','description','status','test_mode','credentials'];

    protected $casts = [
        'test_mode' => 'integer',
        'status' => 'integer'
    ];

    public function scopeActive($query)
    {
        return $query->where('status', StatusEnums::PUBLISH);
    }

    public function scopeInActive($query)
    {
        return $query->where('status', StatusEnums::DRAFT);
    }
}
