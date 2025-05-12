<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Category extends Model
{
    use HasFactory,HasTranslations;

    public $translatable = ['name'];

    protected $fillable = [
        'name',
        'category_id'
    ];

    public function courses()
    {
        return $this->hasMany(Course::class);
    }

    public function mainCategory(){
        return $this->belongsTo(Category::class,'category_id','id');
    }
    public function subCategory(){
        return $this->hasMany(Category::class,'category_id','id');
    }



}
