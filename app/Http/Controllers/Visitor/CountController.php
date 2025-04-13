<?php

namespace App\Http\Controllers\Visitor;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class CountController extends Controller
{
    //
    public function totalProjects(){
        $counts=['total_projects'=>DB::table('projects')->count(),
    'total_companies'=>DB::table('users')->where('role','company')->count(),
'total_colleges'=>DB::table('users')->where('role','college')->count()];
        return response()->json($counts);
    }

    public function totalCompanies(){
        $count=DB::table('users')->where('role','company')->count();
        return response()->json($count);
    }

    public function totalColleges(){
        $count=DB::table('users')->where('role','college')->count();
        return response()->json($count);
    }

    public function colleges() {
        $colleges = Profile::with('user')->whereHas('user', function ($query) {
            $query->where('role', 'college');
        })->get();
    
        return response()->json($colleges);
    }
    
    public function companies() {
        $companies = Profile::with('user')->whereHas('user', function ($query) {
            $query->where('role', 'company');
        })->get();
    
        return response()->json($companies);
    }
    

}
