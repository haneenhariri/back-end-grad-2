<?php

namespace App\Http\Controllers;

use App\Http\Resources\CategoryResource;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index()
    {
        $data = Category::whereNull('category_id')->with('subCategory')->get();
        return self::success(CategoryResource::collection($data));
    }
}
