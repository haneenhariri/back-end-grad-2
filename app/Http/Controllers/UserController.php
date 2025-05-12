<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\EditProfileRequest;
use App\Http\Requests\User\LoginRequest;
use App\Http\Requests\User\RegisterRequest;
use App\Http\Requests\User\StoreRequest;
use App\Http\Requests\User\updateRequest;
use App\Http\Resources\UserResource;
use App\Models\Course;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function register(RegisterRequest $request)
    {
        $user = $this->userService->register($request->validated());
        return self::success($user);
    }

    public function login(LoginRequest $request)
    {
        $data = $this->userService->login($request->validationData());
        return self::success($data);
    }

    public function logout()
    {
        \auth()->user()->tokens()->delete();
        return self::success();
    }

    public function profile()
    {
        $user = auth()->user();
        if ($user->hasRole('student')) {
            $user = $user->load('courses');
        }
        if ($user->hasRole('instructor')) {
            $user = $user->load('coursesForInstructor');
        }
        return new UserResource($user->load('account'));
    }

    public function editProfile(EditProfileRequest $request)
    {
        $user = $this->userService->editProfile($request->validationData());
        return self::success(new UserResource($user));
    }

    public function paymentCourse(Course $course)
    {
        $this->userService->paymentCourse($course);
        return self::success(null, 'payment successfully');
    }

    public function store(StoreRequest $request)
    {
        $this->userService->store($request->validated());
        return self::success(null, 'created successfully');
    }

    public function update(updateRequest $request, User $user)
    {
        $user = $this->userService->update($request->validated(), $user);
        return self::success(new UserResource($user), 'updated successfully');
    }

    public function index()
    {
        $users = User::oldest('name')->with('roles')->get();
        return self::success(UserResource::collection($users));
    }

    public function show(User $user)
    {
        $user->hasRole('instructor') ? $user->load(['instructor', 'coursesForInstructor']) :
            ($user->hasRole('student') ? $user->load('courses') : $user);
        return self::success(new UserResource($user));
    }

    public function destroy(User $user)
    {
        $user->delete();
        return self::success(null, 'deleted successfully');
    }

    public function getStudents()
    {
        $users = User::role('student')->with('courses:id,title')->oldest('name')->get();
        return self::success(UserResource::collection($users));
    }

    public function getInstructors()
    {
        $users = User::role('instructor')->oldest('name')->with('instructor')->get();
        return self::success(UserResource::collection($users));
    }

    public function studentInstructors(){
        $user = \auth()->user();
        $users = match (true){
            $user->hasRole('instructor')=>$user->coursesForInstructor()->with('users')->get()->pluck('users')->flatten()->unique(),
            default =>$user->courses()->with('instructor')->get()->pluck('instructor')->unique()

        };
        return self::success(UserResource::collection($users));
    }
}
