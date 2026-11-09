<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceToken extends Model
{
    protected $fillable = [
        'user_id',
        'token',
        'device_name',
        'platform',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}