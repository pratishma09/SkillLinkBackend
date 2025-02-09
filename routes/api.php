<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{EmailVerificationNotificationController,LogoutController, ProfilePhotoController};
use  Laravel\Fortify\Http\Controllers\{AuthenticatedSessionController, RegisteredUserController,PasswordResetLinkController,ProfileInformationController,PasswordController};
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Auth;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use App\Http\Controllers\JobseekerController;
use App\Http\Controllers\JobSeekerEducationController;
use App\Http\Controllers\JobSeekerWorkExperienceController;
use App\Http\Controllers\JobSeekerCertificationController;
use App\Http\Controllers\JobSeekerProjectController;
use App\Http\Controllers\CollegeController;

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

Route::group(['middleware' => 'auth:sanctum'], function() {

    Route::prefix('auth')->group(function () {
        $verificationLimiter = config('fortify.limiters.verification', '6,1');

        Route::withoutMiddleware('auth:sanctum')->group(function () {
            $limiter = config('fortify.limiters.login');

            Route::post('/login', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'store'])
                ->middleware(array_filter([
                    'guest:'.config('fortify.guard'),
                    $limiter ? 'throttle:'.$limiter : null,
                ]));

            Route::post('/register', [RegisteredUserController::class, 'store'])
                ->middleware('guest:'.config('fortify.guard'));

            Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
                ->middleware('guest:'.config('fortify.guard'))
                ->name('password.email');
        });

        Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware([
            'throttle:'.$verificationLimiter
        ]);

        Route::post('/logout', [LogoutController::class, 'destroy']);
    });

    
    Route::prefix('user')->group(function () {
        Route::get('/', function (Request $request) {
            return $request->user();  
        });

    Route::put('/update-password', [PasswordController::class, 'update']);
});

Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::get('/pending-users', [AdminController::class, 'pendingUsers']);
    Route::post('/users/{user}/approve', [AdminController::class, 'approve']);
    Route::post('/users/{user}/reject', [AdminController::class, 'reject']);
});
});

Route::get('/email/verify/{id}/{hash}', function (Request $request, $id) {
    $user = User::findOrFail($id);

    if ($user->hasVerifiedEmail()) {
        return response()->json(['message' => 'Email already verified'], 200);
    }

    if ($user->markEmailAsVerified()) {
        event(new Verified($user));
        
        // Update user status after verification
        $user->update([
            'is_verified' => true,
        ]);
    }

    return response()->json(['message' => 'Email verified successfully'], 200);
})->middleware('signed')->name('verification.verify');

// Protected routes that need authentication
Route::middleware('auth:sanctum')->group(function () {
    // Resend verification email
    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return response()->json(['message' => 'Verification link sent']);
    })->middleware(['throttle:6,1'])->name('verification.send');

    Route::get('/jobseeker/profile', [JobseekerController::class, 'show']);
    Route::post('/jobseeker/profile', [JobseekerController::class, 'update']);

    // Education routes
    Route::post('/jobseeker/education', [JobSeekerEducationController::class, 'store']);
    Route::put('/jobseeker/education/{education}', [JobSeekerEducationController::class, 'update']);
    Route::delete('/jobseeker/education/{education}', [JobSeekerEducationController::class, 'destroy']);

    // Work Experience routes
    Route::post('/jobseeker/experience', [JobSeekerWorkExperienceController::class, 'store']);
    Route::put('/jobseeker/experience/{experience}', [JobSeekerWorkExperienceController::class, 'update']);
    Route::delete('/jobseeker/experience/{experience}', [JobSeekerWorkExperienceController::class, 'destroy']);

    // Certifications
    Route::post('/jobseeker/certifications', [JobSeekerCertificationController::class, 'store']);
    Route::put('/jobseeker/certifications/{certification}', [JobSeekerCertificationController::class, 'update']);
    Route::delete('/jobseeker/certifications/{certification}', [JobSeekerCertificationController::class, 'destroy']);

    // Projects
    Route::post('/jobseeker/projects', [JobSeekerProjectController::class, 'store']);
    Route::put('/jobseeker/projects/{project}', [JobSeekerProjectController::class, 'update']);
    Route::delete('/jobseeker/projects/{project}', [JobSeekerProjectController::class, 'destroy']);

    // College routes
    Route::get('/colleges', [CollegeController::class, 'index']);
});

