<?php

namespace App\Http\Controllers\Visitor;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Project;
use App\Services\QLMService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProjectSearchController extends Controller
{
    public function search(Request $request, QLMService $qlm)
    {
        // Get the filter values from the request
        $query = strtolower(trim($request->input('query', '')));
        $categoryId = $request->input('category_id');
        $typeOfProject = $request->input('type_of_project');
        $minSalary = $request->input('min_salary');
        $maxSalary = $request->input('max_salary');
        $deadlineBefore = $request->input('deadline_before');
        
        // Category-only search
        if (empty($query) && $categoryId) {
            $projects = Project::where('project_category_id', $categoryId)
                ->when($typeOfProject, function ($queryBuilder) use ($typeOfProject) {
                    return $queryBuilder->where('type_of_project', $typeOfProject);
                })
                ->when($minSalary, function ($queryBuilder) use ($minSalary) {
                    return $queryBuilder->where('salary', '>=', $minSalary);
                })
                ->when($maxSalary, function ($queryBuilder) use ($maxSalary) {
                    return $queryBuilder->where('salary', '<=', $maxSalary);
                })
                ->when($deadlineBefore, function ($queryBuilder) use ($deadlineBefore) {
                    return $queryBuilder->where('deadline', '<=', $deadlineBefore);
                })
                ->latest()
                ->get();
            return response()->json($projects);
        }

        // Query-only or Query+Category search using QLM
        if (!empty($query)) {
            $queryTerms = preg_split('/\s+/', $query);
            $corpusLength = DB::table('project_search_index')->sum('doc_length');
            $scores = $qlm->search($queryTerms, $corpusLength);
            Log::info($scores);
            $projectIds = array_keys($scores);

            if (empty($projectIds)) {
                return response()->json([]); // No results found
            }

            $projects = Project::whereIn('id', $projectIds)
                ->when($categoryId, function ($queryBuilder) use ($categoryId) {
                    $queryBuilder->where('project_category_id', $categoryId);
                })
                ->when($typeOfProject, function ($queryBuilder) use ($typeOfProject) {
                    return $queryBuilder->where('type_of_project', $typeOfProject);
                })
                ->when($minSalary, function ($queryBuilder) use ($minSalary) {
                    return $queryBuilder->where('salary', '>=', $minSalary);
                })
                ->when($maxSalary, function ($queryBuilder) use ($maxSalary) {
                    return $queryBuilder->where('salary', '<=', $maxSalary);
                })
                ->when($deadlineBefore, function ($queryBuilder) use ($deadlineBefore) {
                    return $queryBuilder->where('deadline', '<=', $deadlineBefore);
                })
                ->orderByRaw("FIELD(id, " . implode(',', $projectIds) . ")")
                ->get();

            return response()->json($projects);
        }

        // Default query if no filters applied — return active projects
        $projects = Project::with(['company', 'projectcategory'])
            ->where('status', 'active')
            ->when($categoryId, function ($queryBuilder) use ($categoryId) {
                return $queryBuilder->where('project_category_id', $categoryId);
            })
            ->when($typeOfProject, function ($queryBuilder) use ($typeOfProject) {
                return $queryBuilder->where('type_of_project', $typeOfProject);
            })
            ->when($minSalary, function ($queryBuilder) use ($minSalary) {
                return $queryBuilder->where('salary', '>=', $minSalary);
            })
            ->when($maxSalary, function ($queryBuilder) use ($maxSalary) {
                return $queryBuilder->where('salary', '<=', $maxSalary);
            })
            ->when($deadlineBefore, function ($queryBuilder) use ($deadlineBefore) {
                return $queryBuilder->where('deadline', '<=', $deadlineBefore);
            })
            ->latest()
            ->paginate(10);

        return response()->json($projects);
    }
}
