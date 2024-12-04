@extends('layouts.app')

@section('content')
    <div class="container mx-auto">
        <h1 class="text-xl font-bold mb-4">Task Details</h1>
        <div class="bg-white p-4 shadow rounded">
            <p><strong>Title:</strong> {{ $task->title }}</p>
            <p><strong>Description:</strong> {{ $task->description }}</p>
            <p><strong>Status:</strong> {{ ucfirst($task->status) }}</p>
            <p><strong>Start Date:</strong> {{ $task->start_date->format('d M, Y') }}</p>
            <p><strong>End Date:</strong> {{ $task->end_date->format('d M, Y') }}</p>
        </div>
    </div>
@endsection
