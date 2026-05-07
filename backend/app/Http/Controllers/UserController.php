<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index()
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $users = User::select('id', 'name', 'email', 'role')->get();
        return response()->json($users);
    }
    
    public function getTeamMembers($projectId)
    {
        $user = Auth::user();
        
        if ($user->role === 'admin') {
            $users = User::select('id', 'name', 'email', 'role')->get();
        } else {
           $project = \App\Models\Project::findOrFail($projectId);
            $users = $project->users()->select('users.id', 'users.name', 'users.email')->get();
        }
        
        return response()->json($users);
    }
}