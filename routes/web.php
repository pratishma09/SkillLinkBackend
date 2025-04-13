<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Student\ProjectApplicationController;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use App\Http\Controllers\Company\ProjectController;
use App\Http\Controllers\Company\ProfileController;
use App\Http\Controllers\Admin\ProjectCategoryController;
use App\Http\Controllers\Visitor\CountController;
use App\Http\Controllers\Company\ProjectApplicantController;

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

// Public project routes
Route::get('/projects', [ProjectController::class, 'index']);
Route::get('/projects/{project}', [ProjectController::class, 'show']);
Route::get('/projects/counts', [ProjectController::class, 'getCounts']);

Route::get('/colleges/count/total', [CountController::class, 'totalColleges']);
Route::get('/projects/count/total', [CountController::class, 'totalProjects']);
Route::get('/companies/count/total', [CountController::class, 'totalCompanies']);
Route::get('/all/colleges', [CountController::class, 'colleges']);
Route::get('/all/companies', [CountController::class, 'companies']);

Route::get('/categories', [ProjectCategoryController::class, 'index']);

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


// Route::get('/categories', [ProjectCategoryController::class, 'index']);


Route::middleware(['auth:sanctum'])->group(function () {
    
    // Company only routes
    Route::middleware(['role:company'])->group(function () {
        Route::post('/projects', [ProjectController::class, 'store']);
        Route::put('/projects/{project}', [ProjectController::class, 'update']);
        Route::delete('/projects/{project}', [ProjectController::class, 'destroy']);
        Route::get('/my-projects', [ProjectController::class, 'myProjects']);

        Route::get('/projects/{project}/applicants', [ProjectApplicantController::class, 'getProjectApplications']);
        Route::put('/projects/{project}/applicants/{applicant}/status', [ProjectApplicantController::class, 'updateApplicantStatus']);
    });

    // Admin routes
    Route::middleware(['role:admin'])->group(function () {
        Route::post('/category', [ProjectCategoryController::class, 'store']);
        Route::put('/category/{category}', [ProjectCategoryController::class, 'update']);
        Route::delete('/category/{category}', [ProjectCategoryController::class, 'destroy']);
    });

    Route::middleware(['role:company,college'])->group(function () {
    Route::get('/profiles', [ProfileController::class, 'index']);
    Route::get('/profiles/{profile}', [ProfileController::class, 'show']);
    Route::post('/profiles', [ProfileController::class, 'store']);
    Route::put('/profiles/{profile}', [ProfileController::class, 'update']);
    Route::delete('/profiles/{profile}', [ProfileController::class, 'destroy']);
    Route::get('/users/{user}/profile', [ProfileController::class, 'getProfileByUser']);
    });

    Route::middleware(['role:student'])->group(function(){
            // Project Applications
    Route::post('/projects/{project}/apply', [ProjectApplicationController::class, 'apply']);
    Route::delete('/projects/{project}/withdraw', [ProjectApplicationController::class, 'withdraw']);
    Route::post('/projects/{project}/save', [ProjectApplicationController::class, 'save']);
    Route::delete('/projects/{project}/unsave', [ProjectApplicationController::class, 'unsave']);
    Route::get('/my-applications', [ProjectApplicationController::class, 'myApplications']);
    Route::get('/saved-projects', [ProjectApplicationController::class, 'savedProjects']);
        });

    // Project Applicant Routes
   
});