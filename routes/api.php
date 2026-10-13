<?php

use App\Http\Controllers\Api\EskizCallbackController;
use App\Http\Controllers\Api\LeadApiController;
use App\Http\Controllers\MobileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('login', [MobileController::class, 'login']);
Route::group(['middleware' => 'auth:sanctum'], function () {
    //auth
    Route::get('me', [MobileController::class, 'me']);
    Route::post('logout', [MobileController::class, 'logout']);

    //branches
    Route::post('branches/change-current', [MobileController::class, 'changeCurrentBranch']);
    Route::get('branches', [MobileController::class, 'branches']);

    //dashboard data
    Route::get('dashboard/calendar', [MobileController::class, 'calendar']);
    Route::get('dashboard/rooms/{room}/timeline', [MobileController::class, 'roomTimeline']);
    Route::get('dashboard', [MobileController::class, 'dashboard']);

    //groups
    Route::get('groups', [MobileController::class, 'groups']);
    Route::get('groups/{group}/card-data', [MobileController::class, 'groupCardData']);
    Route::get('groups/{group}/students', [MobileController::class, 'groupStudents']);
    Route::get('groups/{group}/attendance', [MobileController::class, 'groupAttendance']);
    Route::get('groups/{group}', [MobileController::class, 'showGroup']);
    Route::get('groups/{group}/get-attendance', [MobileController::class, 'getAttendance']);
    Route::put('groups/{group}/attendance-update', [MobileController::class, 'updateAttendance']);
    Route::post('groups/{group}/attendance', [MobileController::class, 'storeAttendance']);

    // Lead routes - Protected by authentication
    Route::get('leads/boards', [LeadApiController::class, 'getBoards']);
    Route::post('leads', [LeadApiController::class, 'store'])->middleware('permission:lead-index');
    Route::put('leads/update-order', [LeadApiController::class, 'updateOrder'])->middleware('permission:lead-index');
    Route::get('leads/load-more', [LeadApiController::class, 'loadMore']);
    Route::get('/leads/{id}', [LeadApiController::class, 'show'])->name('api.leads.show');
});

Route::post('sms/eskiz/callback', EskizCallbackController::class)->name('sms.eskiz.callback');

Route::get('app-version', function () {
    return response()->json([
        'version' => '1.0.9',
    ]);
});

//courses
Route::get('courses', [\App\Http\Controllers\Api\CourseController::class, 'index']);
Route::get('courses/{course}', [\App\Http\Controllers\Api\CourseController::class, 'show']);

//users
Route::get('teachers', [\App\Http\Controllers\Api\UserController::class, 'index']);
Route::get('teachers/{user}', [\App\Http\Controllers\Api\UserController::class, 'show']);
