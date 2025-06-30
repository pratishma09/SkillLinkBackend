<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Verify Khalti payment and create project if payment is successful
     */
    public function verifyPayment(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'pidx' => 'required|string',
                'project_data' => 'sometimes|array',
            ]);

            // Verify the authenticated user is a company
            if (auth()->user()->role !== 'company') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only companies can create projects'
                ], 403);
            }

            $pidx = $request->input('pidx');
            $projectData = $request->input('project_data');

            // Verify payment with Khalti
            $paymentInquiry = $this->paymentService->inquiry($pidx);

            if (!$this->paymentService->isSuccess($paymentInquiry)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment verification failed or payment not completed'
                ], 400);
            }

            // If payment is successful and we have project data, create the project
            if ($projectData) {
                Log::info('Received project data for creation after payment', ['project_data' => $projectData]);

                // Create project using the new endpoint logic
                $projectData['payment_verified'] = true;
                $projectData['payment_transaction_id'] = $pidx;
                $projectData['payment_amount'] = $this->paymentService->requestedAmount($paymentInquiry) / 100; // Convert from paisa to rupees

                // Use the same validation as the new endpoint
                $validatedData = $this->validateProjectData($projectData);

                $project = Project::create([
                    ...$validatedData,
                    'posted_by' => auth()->id(),
                    'payment_verified' => true,
                    'payment_transaction_id' => $pidx,
                    'payment_amount' => $this->paymentService->requestedAmount($paymentInquiry) / 100,
                ]);

                Log::info('Project created after payment verification', [
                    'project_id' => $project->id,
                    'payment_id' => $pidx,
                    'user_id' => auth()->id()
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Payment verified and project created successfully',
                    'project' => $project->load(['company', 'projectcategory']),
                    'payment' => $paymentInquiry
                ], 201);
            }

            return response()->json([
                'success' => true,
                'message' => 'Payment verified successfully',
                'payment' => $paymentInquiry
            ]);

        } catch (\Exception $e) {
            Log::error('Payment verification error', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'pidx' => $request->input('pidx')
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Payment verification failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validate project data for creation
     */
    private function validateProjectData(array $projectData): array
    {
        Log::info('Validating project data', [
            'data_types' => array_map('gettype', $projectData),
            'requirements_type' => isset($projectData['requirements']) ? gettype($projectData['requirements']) : 'not_set',
            'skills_required_type' => isset($projectData['skills_required']) ? gettype($projectData['skills_required']) : 'not_set'
        ]);

        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'type_of_project' => 'required|in:internship,full-time,part-time,contract',
            'requirements' => 'sometimes|array',
            'skills_required' => 'sometimes|array',
            'deadline' => 'required|date|after:today',
            'location' => 'nullable|string|max:255',
            'salary' => 'nullable|numeric|min:0',
            'project_category_id' => 'required|exists:project_categories,id',
        ];

        $validator = validator($projectData, $rules);

        if ($validator->fails()) {
            Log::error('Project data validation failed', [
                'errors' => $validator->errors()->toArray(),
                'data' => $projectData
            ]);
            throw new \Exception('Invalid project data: ' . $validator->errors()->first());
        }

        $validated = $validator->validated();

        // Set default status
        $validated['status'] = 'active';

        return $validated;
    }
}