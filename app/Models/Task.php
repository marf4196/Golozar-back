<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'date',
        'user_id',
        'worker_id',
        'task_category_id'
    ];

    public function medicines()
    {
        return $this->belongsToMany(Medicine::class, 'task_medicine_relationships', 'task_id', 'medicine_id')->withPivot('quantity');
    }
}
