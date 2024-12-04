<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Task;


class TaskController extends Controller
{
    public function show(Task $task)
    {
        return view('tasks.show', compact('task'));
    }
}
