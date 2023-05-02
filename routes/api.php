<?php

use App\Http\Controllers\AuthController;
use App\Models\Medicine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Morilog\Jalali\Jalalian;

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

//Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//    return $request->user();
//});
Route::get('test', function () {
    dd(\App\Models\Task::all());
    return;
    $medicine = Medicine::findOrFail(1);
    $medicine->update(['quantity' => $medicine->quantity - 2]);
    dd($medicine, $medicine->fresh());
    dd(
//        Jalalian::fromFormat('Y-m-d H:i:s', '1402-10-05 17:20:00')->format('Y-m-d H:i:s'),
        Jalalian::fromFormat('Y-m-d H:i:s', '1402-10-05 17:20:00')->toCarbon()
    );
});
Route::controller(AuthController::class)->prefix('auth')->group(function () {
    Route::post('login', 'login');
    Route::post('register', 'register');
    Route::post('logout', 'logout');
    Route::post('refresh', 'refresh');
});
Route::middleware('auth:api')->group(function () {
    Route::get('tasks', [\App\Http\Controllers\TaskController::class, 'getTasks']);
    Route::get('teeth', [\App\Http\Controllers\ToothController::class, 'getTeeth']);
});

Route::prefix('staff')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('login', [\App\Http\Controllers\Worker\AuthController::class, 'login']);
        Route::post('refresh', [\App\Http\Controllers\Worker\AuthController::class, 'refresh']);
        Route::post('logout', [\App\Http\Controllers\Worker\AuthController::class, 'logout']);
    });
    Route::middleware('auth:workersAPI')
        ->group(function () {
            Route::prefix('medicines')->group(function () {
                Route::get('', [\App\Http\Controllers\Worker\MedicineController::class, 'index']);
            });
            Route::prefix('users')->group(function () {
                Route::get('', [\App\Http\Controllers\Worker\UserController::class, 'index']);
            });
            Route::prefix('co-workers')->group(function () {
                Route::get('', [\App\Http\Controllers\Worker\WorkerController::class, 'getCoWorkers']);
                Route::post('', [\App\Http\Controllers\Worker\WorkerController::class, 'createCoWorker']);
                Route::get('{coWorker}', [\App\Http\Controllers\Worker\WorkerController::class, 'getCoWorker']);
                Route::put('{coWorker}', [\App\Http\Controllers\Worker\WorkerController::class, 'updateCoWorker']);
                Route::delete('{coWorker}', [\App\Http\Controllers\Worker\WorkerController::class, 'deleteCoWorker']);
            });
            Route::prefix('tasks')->group(function () {
                Route::get('categories', [\App\Http\Controllers\Worker\TaskController::class, 'getCategories']);
                Route::get('', [\App\Http\Controllers\Worker\TaskController::class, 'index']);
                Route::post('', [\App\Http\Controllers\Worker\TaskController::class, 'create']);
                Route::get('{task}', [\App\Http\Controllers\Worker\TaskController::class, 'get']);
                Route::put('{task}', [\App\Http\Controllers\Worker\TaskController::class, 'update']);
                Route::delete('{task}', [\App\Http\Controllers\Worker\TaskController::class, 'delete']);
            });
        });
});

Route::prefix('admin')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('login', [\App\Http\Controllers\Admin\AuthController::class, 'login']);
        Route::post('logout', [\App\Http\Controllers\Admin\AuthController::class, 'logout']);
        Route::post('refresh', [\App\Http\Controllers\Admin\AuthController::class, 'refresh']);
    });
    Route::
    middleware('auth:adminAPI')->
//    prefix('')->
    group(function () {
        Route::prefix('workers')->group(function () {
            Route::get('', [\App\Http\Controllers\Admin\WorkerController::class, 'index']);
            Route::post('', [\App\Http\Controllers\Admin\WorkerController::class, 'create']);
            Route::get('{worker}', [\App\Http\Controllers\Admin\WorkerController::class, 'get']);
            Route::put('{worker}', [\App\Http\Controllers\Admin\WorkerController::class, 'update']);
            Route::delete('{worker}', [\App\Http\Controllers\Admin\WorkerController::class, 'delete']);
        });
        Route::prefix('medicines')->group(function () {

            Route::get('', [\App\Http\Controllers\Admin\MedicineController::class, 'index']);
            Route::post('', [\App\Http\Controllers\Admin\MedicineController::class, 'create']);
            Route::get('{medicine}', [\App\Http\Controllers\Admin\MedicineController::class, 'get']);
            Route::put('{medicine}', [\App\Http\Controllers\Admin\MedicineController::class, 'update']);
            Route::delete('{medicine}', [\App\Http\Controllers\Admin\MedicineController::class, 'delete']);

        });
        Route::prefix('tasks')->group(function () {
            Route::prefix('categories')->group(function () {
                Route::get('', [\App\Http\Controllers\Admin\TaskController::class, 'taskCategoriesIndex']);
                Route::get('{taskCategory}', [\App\Http\Controllers\Admin\TaskController::class, 'getTaskCategory']);
                Route::post('', [\App\Http\Controllers\Admin\TaskController::class, 'createTaskCategory']);
                Route::put('{taskCategory}', [\App\Http\Controllers\Admin\TaskController::class, 'updateTaskCategory']);
                Route::delete('{taskCategory}', [\App\Http\Controllers\Admin\TaskController::class, 'deleteTaskCategory']);
            });
        });
        Route::prefix('admins')->group(function () {
            Route::get('', [\App\Http\Controllers\Admin\AdminController::class, 'index']);
            Route::post('', [\App\Http\Controllers\Admin\AdminController::class, 'create']);
            Route::get('{admin}', [\App\Http\Controllers\Admin\AdminController::class, 'get']);
            Route::put('{admin}', [\App\Http\Controllers\Admin\AdminController::class, 'update']);
            Route::delete('{admin}', [\App\Http\Controllers\Admin\AdminController::class, 'delete']);
        });
    });
});
