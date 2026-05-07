<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        if ($user->role === 'admin') {
            $projects = Project::with('creator', 'users')->get();
        } else {
            $projects = $user->projects()->with('creator', 'users')->get();
        }
        
        return response()->json($projects);
    }

    public function store(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json([
                'message' => 'Only administrators can create projects'
            ], 403);
        }
        
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'due_date' => 'required|date',
        ]);

        $project = Project::create([
            'title' => $request->title,
            'description' => $request->description,
            'due_date' => $request->due_date,
            'created_by' => Auth::id(),
        ]);

        $project->users()->attach(Auth::id());

        return response()->json($project, 201);
    }

    public function show($id)
    {
        $project = Project::with('tasks.assignedUser', 'users', 'creator')->findOrFail($id);
        
        $user = Auth::user();
        if ($user->role !== 'admin' && !$project->users->contains($user)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        return response()->json($project);
    }

    public function update(Request $request, $id)
    {
        $project = Project::findOrFail($id);
        
       
        if (Auth::user()->role !== 'admin' && $project->created_by !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'due_date' => 'sometimes|date',
        ]);

        $project->update($request->all());
        
        return response()->json($project);
    }

    public function destroy($id)
    {
        $project = Project::findOrFail($id);
        
       
        if (Auth::user()->role !== 'admin' && $project->created_by !== Auth::id()) {
            return response()->json(['message' => 'Only project creator or admin can delete this project'], 403);
        }

        $project->delete();
        
        return response()->json(['message' => 'Project deleted successfully']);
    }

    public function addMember(Request $request, $id)
    {
    
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Only administrators can add team members'], 403);
        }
        
        $project = Project::findOrFail($id);
        
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        $project->users()->syncWithoutDetaching([$request->user_id]);
        
        return response()->json(['message' => 'Team member added successfully']);
    }
}