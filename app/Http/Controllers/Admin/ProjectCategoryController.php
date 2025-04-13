<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProjectCategory;
use Illuminate\Http\Request;

class ProjectCategoryController extends Controller
{
    public function index()
    {
        $categories = ProjectCategory::all();
        return response()->json($categories);
    }

    public function store(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            return response()->json([
                'message' => 'Only admins can manage categories'
            ], 403);
        }
        $data = $request->validate([
            'name' => 'required|string'
        ]);

        $category = ProjectCategory::create($data);

        return response()->json([
            'category' => $category,
            'message' => 'Category created successfully',
        ], 201);
    }

    public function show(ProjectCategory $category)
    {
        return response()->json($category);
    }

    public function update(Request $request, ProjectCategory $category)
    {
        if (auth()->user()->role !== 'admin') {
            return response()->json([
                'message' => 'Only admins can manage categories'
            ], 403);
        }

        $data = $request->validate([
            'name' => 'required|string'
        ]);

        $category->update($data);

        return response()->json([
            'category' => $category,
            'message' => 'Category updated successfully'
        ]);
    }

    public function destroy(ProjectCategory $category)
    {
        if (auth()->user()->role !== 'admin') {
            return response()->json([
                'message' => 'Only admins can manage categories'
            ], 403);
        }

        $category->delete();

        return response()->json([
            'message' => 'Category deleted successfully'
        ]);
    }
}
