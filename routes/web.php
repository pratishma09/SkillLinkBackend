<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use App\Http\Controllers\ProjectController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Email Verification Routes
Route::get('/email/verify/{id}/{hash}', function ($id, $hash) {
    $user = User::findOrFail($id);

    if ($user->hasVerifiedEmail()) {
        return view('auth.verify-email', ['status' => 'already-verified']);
    }

    if ($user->markEmailAsVerified()) {
        event(new Verified($user));
        
        // Update user status
        $user->update([
            'is_verified' => true,
        ]);

        return view('auth.verify-email', ['status' => 'verified']);
    }

    return view('auth.verify-email', ['status' => 'error']);
})->name('verification.verify');

Route::middleware(['auth:sanctum'])->group(function () {
    // Public project routes
    Route::get('/projects', [ProjectController::class, 'index']);
    Route::get('/projects/{project}', [ProjectController::class, 'show']);
    
    // Company only routes
    Route::middleware(['role:company'])->group(function () {
        Route::post('/projects', [ProjectController::class, 'store']);
        Route::put('/projects/{project}', [ProjectController::class, 'update']);
        Route::delete('/projects/{project}', [ProjectController::class, 'destroy']);
        Route::get('/my-projects', [ProjectController::class, 'myProjects']);
    });
});