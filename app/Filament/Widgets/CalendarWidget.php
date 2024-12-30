<?php

namespace App\Filament\Widgets;

use Guava\Calendar\Widgets\CalendarWidget as BaseCalendarWidget;
use App\Models\Task;
use App\Models\SalesLeadStatus;
use Illuminate\Support\Collection;

class CalendarWidget extends BaseCalendarWidget
{
    protected bool $eventClickEnabled = true;

    protected static ?int $sort = 2;

    
    


    public function getEvents(array $fetchInfo = []): Collection | array
{
    $taskEvents = Task::where('user_id', auth()->id())
        ->with('school')
        ->get()
        ->map(fn ($task) => [
            'title' => $task->title . ' - ' . ($task->school ? $task->school->name : 'No School Assigned'),
            'start' => $task->start_date,
            'end' => $task->end_date,
            'id' => $task->id,
            'type' => 'task', // Add event type
            'backgroundColor' => $this->getStatusColor($task->status),
            'borderColor' => '#ccc',
            'extendedProps' => [
                'description' => $task->description,
                'status' => ucfirst($task->status),
                'school_name' => $task->school ? $task->school->name : 'No School Assigned',
            ],
        ])->toArray();

    $salesLeadEvents = SalesLeadStatus::where('created_by', auth()->id())
        ->whereNull('reschedule_date') // Exclude reschedule_date records
        ->with('school')
        ->get()
        ->map(fn ($lead) => [
            'title' => 'Follow-up - ' . ($lead->school ? $lead->school->name : 'No School Assigned'),
            'start' => $lead->follow_up_date,
            'end' => $lead->follow_up_date,
            'id' => $lead->id,
            'type' => 'salesLead', // Add event type
            'backgroundColor' => '#007bff', // Blue for Sales Leads
            'borderColor' => '#ccc',
            'extendedProps' => [
                'status' => ucfirst($lead->status),
                'remarks' => $lead->remarks,
                'contacted_person' => $lead->contacted_person,
                'school_name' => $lead->school ? $lead->school->name : 'No School Assigned',
            ],
        ])->toArray();

    $demoRescheduleEvents = SalesLeadStatus::where('created_by', auth()->id())
        ->whereNotNull('reschedule_date') // Include only reschedule_date records
        ->with('school')
        ->get()
        ->map(fn ($demo) => [
            'title' => 'Demo Reschedule - ' . ($demo->school ? $demo->school->name : 'No School Assigned'),
            'start' => $demo->reschedule_date,
            'end' => $demo->reschedule_date,
            'id' => $demo->id,
            'type' => 'demoReschedule', // Add event type
            'backgroundColor' => '#28a745', // Green for Demo Reschedules
            'borderColor' => '#ccc',
            'extendedProps' => [
                'remarks' => $demo->remarks,
                'visited_by' => $demo->visited_by,
                'school_name' => $demo->school ? $demo->school->name : 'No School Assigned',
                'contacted_person' => $demo->contacted_person,
                'designation' => $demo->contacted_person_designation,
            ],
        ])->toArray();

    
        

    return array_values(array_merge($taskEvents, $salesLeadEvents, $demoRescheduleEvents));
}

    protected function getStatusColor(string $status): string
    {
        return match ($status) {
            'pending' => '#ff9800',
            'in_progress' => '#2196f3',
            'completed' => '#4caf50',
            'cancelled' => '#f44336',
            default => '#9e9e9e',
        };
    }

    public function onEventClick(array $info = [], ?string $action = null): void
{
    $event = $info['event'] ?? [];
    $eventId = $event['id'] ?? null;

    // Check if the 'contacted_person' field exists in extendedProps
    $eventType = isset($event['extendedProps']['contacted_person']) ? 'salesLead' : 'task';
    // dd($eventType);
    if ($eventType === 'task' && $eventId) {
        // Redirect only for task events
        $this->redirect("/admin/tasks/{$eventId}/edit");
    }

    // For salesLead events, do nothing
    // if ($eventType === 'salesLead') {
    //     \Log::info("SalesLead event clicked. ID: {$eventId}");
    // }
}

    
}
    