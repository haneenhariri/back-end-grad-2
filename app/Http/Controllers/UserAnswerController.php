<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserAnswer\StoreRequest;
use App\Http\Requests\UserAnswer\UpdateRequest;
use App\Http\Resources\QuestionResource;
use App\Http\Resources\UserAnswerResource;
use App\Models\User;
use App\Models\UserAnswer;
use App\Services\UserAnswerService;

class UserAnswerController extends Controller
{
    protected $userAnswerService;

    public function __construct(UserAnswerService $userAnswerService)
    {
        $this->userAnswerService = $userAnswerService;
        $this->middleware('role:student')->only('store');
        $this->middleware('role:instructor')->only(['index', 'update']);

    }

    /**
     * Display a listing of the resource.
     */
    public function index(User $user, $course)
    {
        $answers = $user->questions()->where('course_id', $course)->where('type', 'code')
            ->withPivot(['answer', 'mark'])
            ->get();
        return self::success(QuestionResource::collection($answers));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        $data = $request->validationData();
        $this->userAnswerService->store($data);
        return self::success(null, 'added successfully');
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequest $request, UserAnswer $userAnswer)
    {
        $userAnswer = $this->userAnswerService->update($request->mark, $userAnswer);
        return self::success(new UserAnswerResource($userAnswer));
    }

    public function testResult($courseId)
    {
        $result = $this->userAnswerService->testResult($courseId);
        return self::success($result);
    }


}
