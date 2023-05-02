<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tooth extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'worker_id',
        'tooth_index',
    ];

    public function services()
    {
        return $this->belongsToMany(DentalService::class, 'tooth_service_relationships', 'tooth_id', 'service_id');
    }

    public function worker()
    {
        return $this->belongsTo(Worker::class);
    }
}
