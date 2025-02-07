<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rules\Enum;
use App\Enums\UserRole;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use App\Notifications\NewUserRegistrationNotification;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string|UploadedFile>  $input
     */
    public function create(array $input): User
    {
        $validator = Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'role' => ['required', new Enum(UserRole::class)],
            'password' => $this->passwordRules(),
            'verification_document_path' => [
                Rule::requiredIf(in_array($input['role'], ['company', 'college'])),
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:5120'
            ]
        ]);

        $validator->validate();

        try {
            $verificationDocumentPath = null;
            $status = 'approved';
            $role = UserRole::from($input['role']);

            // Handle student email validation
            if ($role === UserRole::STUDENT) {
                if (!str_ends_with($input['email'], '.edu.np')) {
                    throw ValidationException::withMessages([
                        'email' => ['Students must use an .edu.np email address.'],
                    ]);
                }
            }

            // Handle company/college document upload and status
            if (in_array($role, [UserRole::COLLEGE, UserRole::COMPANY])) {
                $status = 'pending';
                
                if (isset($input['verification_document_path'])) {
                    $file = $input['verification_document_path'];
                    
                    // Generate unique filename
                    $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    
                    // Store file
                    $verificationDocumentPath = $file->storeAs(
                        'verification-documents',
                        $filename,
                        'public'
                    );
                }
            }

            $user = User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'role' => $role,
                'status' => $status,
                'password' => Hash::make($input['password']),
                'verification_document_path' => $verificationDocumentPath,
            ]);

            // Send verification email for students
            if ($role === UserRole::STUDENT) {
                $user->sendEmailVerificationNotification();
            }

            return $user;

        } catch (\Exception $e) {
            // Clean up uploaded file if user creation fails
            if (isset($verificationDocumentPath)) {
                Storage::disk('public')->delete($verificationDocumentPath);
            }
            throw $e;
        }
    }
}
