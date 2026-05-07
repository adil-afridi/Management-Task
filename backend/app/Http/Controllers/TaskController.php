<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Task::with('project', 'assignedUser', 'creator');
        
        if ($request->has('project_id')) {
            $query->where('project_id', $request->project_id);
        }
        
       
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }
        
        if ($user->role !== 'admin') {
            $query->where('assigned_to', $user->id);
        }
        
        $tasks = $query->get();
        
        if ($user->role !== 'admin') {
            foreach ($tasks as $task) {
                $task->load('project');
            }
        }
        
        return response()->json($tasks);
    }

    public function store(Request $request)
    {
        // ONLY ADMIN can create tasks
        if (Auth::user()->role !== 'admin') {
            return response()->json([
                'message' => 'Only administrators can create tasks'
            ], 403);
        }
        
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'due_date' => 'required|date',
            'project_id' => 'required|exists:projects,id',
            'assigned_to' => 'required|exists:users,id',
        ]);

        $task = Task::create([
            'title' => $request->title,
            'description' => $request->description,
            'due_date' => $request->due_date,
            'status' => 'pending',
            'project_id' => $request->project_id,
            'assigned_to' => $request->assigned_to,
            'created_by' => Auth::id(),
        ]);

        $task->load('assignedUser', 'creator', 'project');
        
        return response()->json($task, 201);
    }

    public function show($id)
    {
        $task = Task::with('project', 'assignedUser', 'creator')->findOrFail($id);
        $user = Auth::user();
        
        // Admin can see any task, users can only see their assigned tasks
        if ($user->role !== 'admin' && $task->assigned_to !== $user->id) {
            return response()->json(['message' => 'Unauthorized - You can only view your own tasks'], 403);
        }
        
        return response()->json($task);
    }

    public function update(Request $request, $id)
    {
        $task = Task::findOrFail($id);
        $user = Auth::user();
        
        // Admin can update any task field
        if ($user->role === 'admin') {
            $request->validate([
                'title' => 'sometimes|string|max:255',
                'description' => 'sometimes|string',
                'due_date' => 'sometimes|date',
                'status' => 'sometimes|in:pending,in_progress,completed',
                'assigned_to' => 'sometimes|exists:users,id',
            ]);
            
            $task->update($request->only(['title', 'description', 'due_date', 'status', 'assigned_to']));
        } 
        // Regular users can ONLY update status (mark as complete/in progress)
        else if ($task->assigned_to === $user->id) {
            $request->validate([
                'status' => 'required|in:pending,in_progress,completed'
            ]);
            
            $task->status = $request->status;
            $task->save();
        } 
        else {
            return response()->json(['message' => 'Unauthorized - You can only update your own tasks'], 403);
        }
        
        $task->load('assignedUser', 'creator', 'project');
        return response()->json($task);
    }

    public function destroy($id)
    {
        // ONLY ADMIN can delete tasks
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Only administrators can delete tasks'], 403);
        }

        $task = Task::findOrFail($id);
        $task->delete();
        
        return response()->json(['message' => 'Task deleted successfully']);
    }
}