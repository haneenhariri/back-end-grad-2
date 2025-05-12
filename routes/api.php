<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\RateController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\UserAnswerController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserFavoritesController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\StudentProgressController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


Route::post('register', [UserController::class, 'register']);
Route::post('login', [UserController::class, 'login']);

Route::post('requests', [RequestController::class, 'store']);

Route::post('forgot-password', [PasswordController::class, 'sendResetCode']);
Route::post('reset-password', [PasswordController::class, 'resetPassword']);

Route::apiResource('courses', CourseController::class)->only('index');
Route::get('categories', [CategoryController::class, 'index']);


Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [UserController::class, 'logout']);
    Route::get('profile', [UserController::class, 'profile']);
    Route::post('profile', [UserController::class, 'editProfile']);

    Route::post('change-password', [PasswordController::class, 'update']);

    Route::middleware('role:admin')->group(function () {
        Route::get('requests/', [RequestController::class, 'index']);
        Route::post('requests/{request}/change-status', [RequestController::class, 'changeStatus']);

        Route::put('courses/{course}/change-status', [CourseController::class, 'changeStatus']);
        Route::get('pending-courses', [CourseController::class, 'pendingCourse']);

        Route::post('/accounts/charge', [AccountController::class, "chargeAccount"]);
        Route::get('payments/all', [AccountController::class, 'allPayment']);
        Route::get('/payments-for-user/{user}', [AccountController::class, 'getPaymentsForUser']);


        Route::get('/get-students', [UserController::class, 'getStudents']);
        Route::get('/get-instructors', [UserController::class, 'getInstructors']);
        Route::apiResource('users', UserController::class);
        Route::get('activity-log',[ActivityLogController::class,'index']);

    });

    Route::apiResource('courses', CourseController::class)->except('index');
    Route::apiResource('lessons', LessonController::class)->except('index');

    Route::post('files', [FileController::class, 'store']);
    Route::delete('files/{file}', [FileController::class, 'destroy']);

    Route::post('/buy-course/{course}', [UserController::class, 'paymentCourse']);
    Route::get('payments', [AccountController::class, 'getPayments']);

    Route::apiResource('rates', RateController::class)->except(['index', 'show']);
    Route::apiResource('comments', CommentController::class)->except(['index', 'show']);
    Route::apiResource('questions', QuestionController::class)->except('index');

    Route::get('/exam/{course}', [QuestionController::class, 'index']);

    Route::apiResource('/user-answers', UserAnswerController::class)->only('store');

    Route::get('user/{user}/answers/{course}', [UserAnswerController::class, 'index']);
    Route::put('add-mark/{userAnswer}', [UserAnswerController::class, 'update']);
    Route::get('test-result/{courseId}', [UserAnswerController::class, 'testResult']);

    Route::post('favorites/{courseId}', [UserFavoritesController::class, 'toggle']);
    Route::get('favorites', [UserFavoritesController::class, 'favoritesForUser']);
    Route::get('recommended-courses', [UserFavoritesController::class, 'recommendedCourses']);

    Route::get('student-instructors',[UserController::class,'studentInstructors']);
    Route::apiResource('messages',MessageController::class)->only(['store','update','destroy']);
    Route::get('chat/{user}',[MessageController::class,'viewChat']);

    Route::post('student-progress/update', [StudentProgressController::class, 'updateProgress']);
    Route::get('student-progress/course/{courseId}', [StudentProgressController::class, 'getCourseProgress']);
    Route::get('student-progress/all-courses', [StudentProgressController::class, 'getAllCoursesProgress']);
});







