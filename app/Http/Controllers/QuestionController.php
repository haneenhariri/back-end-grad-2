<?php

namespace App\Http\Controllers;

use App\Http\Requests\Question\StoreRequest;
use App\Http\Requests\Question\UpdateRequest;
use App\Http\Resources\QuestionResource;
use App\Models\Course;
use App\Models\Question;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:instructor')->only(['store','update','destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Course $course)
    {
        $questions = $course->questions;
        return self::success(QuestionResource::collection($questions));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        Question::create($request->validationData());
        return self::success(null,'added successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(Question $question)
    {
        return self::success(new QuestionResource($question));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequest $request, Question $question)
    {
        $data = array_filter($request->validationData());
        $question->update($data);
        return self::success(new QuestionResource($question),'updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Question $question)
    {
        $question->delete();
        return self::success(null,'deleted successfully');
    }
}
