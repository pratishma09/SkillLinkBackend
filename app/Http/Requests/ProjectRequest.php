<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->role === 'company';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'type_of_project' => 'required|in:internship,full-time,part-time,contract',
            'status' => 'sometimes|in:active,closed,draft',
            'requirements' => 'sometimes|array',
            'requirements.*' => 'string',
            'skills_required' => 'sometimes|array',
            'skills_required.*' => 'string',
            'deadline' => 'required|date|after:today',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'title.required' => 'A project title is required',
            'description.required' => 'A project description is required',
            'type_of_project.required' => 'Please specify the type of project',
            'type_of_project.in' => 'Project type must be internship, full-time, part-time, or contract',
            'deadline.required' => 'A project deadline is required',
            'deadline.after' => 'The deadline must be a future date',
        ];
    }
} 