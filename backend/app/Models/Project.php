<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $table = 'projects';
    
    protected $fillable = [
        'title',
        'description',
        'due_date',
        'created_by',
    ];

    protected $casts = [
        'due_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

   
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'project_user', 'project_id', 'user_id')
                    ->withTimestamps();
    }

    
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function hasMember($userId)
    {
        return $this->users()->where('user_id', $userId)->exists();
    }

    public function isCreator($userId)
    {
        return $this->created_by === $userId;
    }
}