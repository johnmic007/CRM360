<x-mail::message>
<!-- # Introduction

The body of your message.

<x-mail::button :url="''">
Button Text
</x-mail::button>

Thanks,<br>
{{ config('app.name') }} -->



# Hello {{ $user->name }},

Here are your tasks for today:

@component('mail::table')
| Title       | Description     | Start Date | End Date   |
|-------------|-----------------|------------|------------|
@foreach($tasks as $task)
| {{ $task->title }} | {{ $task->description }} | {{ \Carbon\Carbon::parse($task->start_date)->format('Y-m-d') }} | {{ \Carbon\Carbon::parse($task->end_date)->format('Y-m-d') }} |
@endforeach
@endcomponent

Thanks,<br>
{{ config('app.name') }}



</x-mail::message>
