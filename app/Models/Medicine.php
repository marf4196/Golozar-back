<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medicine extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'quantity'
    ];

    public function categories()
    {
        return $this->belongsToMany(MedicineCategory::class, 'medicine_category_relationships', 'medicine_id', 'category_id');
    }

//    public function usedStocks($exceptTaskID = null)
//    {
//        if (!$exceptTaskID) return $this->belongsToMany(Task::class, 'task_medicine_relationships', 'medicine_id', 'task_id')->withPivot('quantity');
//        return $this->belongsToMany(Task::class, 'task_medicine_relationships', 'medicine_id', 'task_id')->withPivot('quantity')->where('tasks.id', '!=', $exceptTaskID);
//    }
//
//    public function currentStocks($exceptTaskID = null)
//    {
//        if (!$exceptTaskID) return $this->stocks->sum('quantity') - $this->usedStocks->sum('quantity');
//        return $this->stocks->sum('quantity') - $this->usedStocks($exceptTaskID)->sum('quantity');
//    }
}
