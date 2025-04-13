<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CollegeController extends Controller
{
    public function index()
    {
        $colleges = User::where('role', 'college')
                       ->where('status', 'approved')
                       ->select('id', 'name')
                       ->get();
                       
        return response()->json($colleges);
    }
} 