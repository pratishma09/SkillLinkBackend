<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\ProjectRequest;

class ProjectController extends Controller
{
    public function index(): JsonResponse
    {
        $projects = Project::with(['company', 'projectcategory'])
            ->where('status', 'active')
            ->latest()
            ->paginate(10);

        return response()->json($projects);
    }

    public function store(ProjectRequest $request): JsonResponse
    {
        // Verify the authenticated user is a company
        if (auth()->user()->role !== 'company') {
            return response()->json([
                'message' => 'Only companies can post projects'
            ], 403);
        }

        $project = Project::create([
            ...$request->validated(),
            'posted_by' => auth()->id(),
        ]);

        return response()->json([
            'message' => 'Project created successfully',
            'project' => $project->load(['company', 'projectcategory'])
        ], 201);
    }

    public function show(Project $project): JsonResponse
    {
        return response()->json($project->load(['company', 'projectcategory']));
    }

    public function update(ProjectRequest $request, Project $project): JsonResponse
    {
        // Verify the authenticated user owns the project
        if ($project->posted_by !== auth()->id()) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        $project->update($request->validated());

        return response()->json([
            'message' => 'Project updated successfully',
            'project' => $project->load(['company', 'projectcategory'])
        ]);
    }

    public function destroy(Project $project): JsonResponse
    {
        // Verify the authenticated user owns the project
        if ($project->posted_by !== auth()->id()) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        $project->delete();

        return response()->json([
            'message' => 'Project deleted successfully'
        ]);
    }

    public function myProjects(): JsonResponse
    {
        $projects = Project::with(['company', 'projectcategory'])
            ->where('posted_by', auth()->id())
            ->latest()
            ->paginate(10);

        return response()->json($projects);
    }

} 