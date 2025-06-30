<?php

use App\Http\Controllers\Visitor\ProjectSearchController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Student\ProjectApplicationController;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use App\Http\Controllers\Company\ProjectController;
use App\Http\Controllers\Company\ProfileController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Admin\ProjectCategoryController;
use App\Http\Controllers\Company\PaymentController;
use App\Http\Controllers\Visitor\CountController;
use App\Http\Controllers\Company\ProjectApplicantController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
Route::get('/users/count/total', [CountController::class, 'totalUsers']);
Route::get('/pending-users/count/total', [CountController::class, 'pendingUsersCount']);
Route::get('/categories/count/total', [CountController::class, 'totalCategories']);
Route::get('/admin/counts', [CountController::class, 'adminView']);
Route::get('/all/colleges', [CountController::class, 'colleges']);
Route::get('/all/companies', [CountController::class, 'companies']);

Route::get('/search', [ProjectSearchController::class, 'search']);

Route::get('/categories', [ProjectCategoryController::class, 'index']);

Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail']);
Route::post('/reset-password', [ForgotPasswordController::class, 'reset'])->name('password.updated');
Route::get('/v1/reset-password/{token}', [ForgotPasswordController::class, 'showResetForm'])
    ->name('password.reset');

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
    
    Route::get('/profiles', [ProfileController::class, 'index']);
    Route::get('/profiles/{profile}', [ProfileController::class, 'show']);
    Route::post('/profiles', [ProfileController::class, 'store']);
    Route::put('/profiles/{profile}', [ProfileController::class, 'update']);
    Route::delete('/profiles/{profile}', [ProfileController::class, 'destroy']);
    Route::get('/users/{user}/profile', [ProfileController::class, 'getProfileByUser']);
    Route::middleware(['role:college'])->group(function () {
        Route::get('/college/dashboard', [\App\Http\Controllers\College\StudentController::class, 'getDashboardData']);
        Route::get('/college/students', [\App\Http\Controllers\College\StudentController::class, 'index']);
    });
    // Company only routes
    Route::middleware(['role:company'])->group(function () {
        Route::post('/projects/validate', [ProjectController::class, 'validateProject']);
        Route::post('/projects/store-after-payment', [ProjectController::class, 'storeAfterPayment']);
        Route::post('/projects', [ProjectController::class, 'store']);
        Route::put('/projects/{project}', [ProjectController::class, 'update']);
        Route::delete('/projects/{project}', [ProjectController::class, 'destroy']);
        Route::get('/my-projects', [ProjectController::class, 'myProjects']);

        Route::get('/projects/{project}/applicants', [ProjectApplicantController::class, 'getProjectApplications']);
        Route::put('/projects/{project}/applicants/{applicant}/status', [ProjectApplicantController::class, 'updateApplicantStatus']);

        Route::get('/projects/{project}/applicants/{applicant}', [ProjectApplicantController::class, 'getApplicantDetails']);
    });

    // Admin routes
    Route::middleware(['role:admin'])->group(function () {
        Route::post('/category', [ProjectCategoryController::class, 'store']);
        Route::put('/category/{category}', [ProjectCategoryController::class, 'update']);
        Route::delete('/category/{category}', [ProjectCategoryController::class, 'destroy']);
    });

    Route::middleware(['role:company'])->group(function () {
        // Payment verification route
        Route::post('/verify-payment', [PaymentController::class, 'verifyPayment']);

        // Test datetime conversion
        Route::get('/test-datetime', function () {
            $testTime = '2025-06-27 19:30:00';
            $carbon = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $testTime);

            return response()->json([
                'input' => $testTime,
                'iso_format' => $carbon->toIso8601String(),
                'db_format' => $carbon->format('Y-m-d H:i:s'),
                'timezone' => $carbon->timezone->getName()
            ]);
        });

        // Test meeting link generation
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


Route::get('/google-calendar/auth', function () {
    $client = new Google_Client();
    $client->setAuthConfig(storage_path('app/google-calendar/oauth-credentials.json'));
    $client->setScopes([\Google_Service_Calendar::CALENDAR]);
    $client->setAccessType('offline'); // Get a refresh token
    $client->setPrompt('consent'); // Ensure prompt for consent

    // Redirect the user to the Google OAuth consent screen
    $authUrl = $client->createAuthUrl();

    // dd($authUrl);
    return redirect($authUrl);
});
Route::get('/google-calendar/callback', function (Request $request) {
    $client = new Google_Client();
    $client->setAuthConfig(storage_path('app/google-calendar/oauth-credentials.json'));

    $client->setScopes([\Google_Service_Calendar::CALENDAR]);
    $client->setAccessType('offline');
    $client->setPrompt('consent');

    $authCode = $request->input('code');

    if (!$authCode) {
        return response()->json(['error' => 'Authorization code missing'], 400);
    }

    $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);

    if (isset($accessToken['error'])) {
        return response()->json(['error' => $accessToken['error']], 400);
    }

    Storage::put('google-calendar/oauth-token.json', json_encode($accessToken));

    return response()->json(['success' => true, 'message' => 'OAuth token generated successfully.']);
});

// Test Khalti SSL connection
Route::get('/test-khalti-ssl', function () {
    try {
        $httpClient = \Illuminate\Support\Facades\Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'key ' . env('KHALTI_SECRET_KEY'),
        ]);

        // Disable SSL verification in development
        if (env('APP_DEBUG', false)) {
            $httpClient = $httpClient->withOptions([
                'verify' => false,
                'timeout' => 30,
            ]);
        }

        $response = $httpClient->post('https://a.khalti.com/api/v2/epayment/lookup/', [
            'pidx' => 'test-pidx'
        ]);

        return response()->json([
            'success' => true,
            'status' => $response->status(),
            'body' => $response->body(),
            'ssl_verification' => env('APP_DEBUG') ? 'disabled' : 'enabled'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'ssl_verification' => env('APP_DEBUG') ? 'disabled' : 'enabled'
        ]);
    }
});
