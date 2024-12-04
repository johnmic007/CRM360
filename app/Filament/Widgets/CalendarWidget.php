<?php


namespace App\Filament\Widgets;

use Guava\Calendar\Widgets\CalendarWidget as BaseCalendarWidget;
use App\Models\Task;
use Illuminate\Support\Collection;

class CalendarWidget extends BaseCalendarWidget
{
    protected bool $eventClickEnabled = true;

    protected static ?int $sort = 1;  // Ensure it comes after WalletBalanceWidget


    public function getEvents(array $fetchInfo = []): Collection | array
    {
        return Task::where('user_id', auth()->id())
            ->with('school') // Eager load the school relationship
            ->get()
            ->map(fn ($task) => [
                'title' => $task->title . ' - ' . ($task->school ? $task->school->name : 'No School Assigned'), // Show title and school name
                'start' => $task->start_date,
                'end' => $task->end_date,
                'id' => $task->id,
                'backgroundColor' => $this->getStatusColor($task->status),
                'borderColor' => '#ccc',
                'extendedProps' => [
                    'description' => $task->description,
                    'status' => ucfirst($task->status),
                    'school_name' => $task->school ? $task->school->name : 'No School Assigned', // Just the name of the school
                ],
            ])
            ->toArray();
    }

    protected function getStatusColor(string $status): string
    {
        return match ($status) {
            'pending' => '#ff9800', // Orange
            'in_progress' => '#2196f3', // Blue
            'completed' => '#4caf50', // Green
            'cancelled' => '#f44336', // Red
            default => '#9e9e9e', // Grey
        };
    }

    public function onEventClick(array $info = [], ?string $action = null): void
    {
        $taskId = $info['event']['id'] ?? null;
        if ($taskId) {
            $this->redirect("/admin/tasks/{$taskId}/edit");
        }
    }

    protected function getCustomJavaScript(): ?string
    {
        return <<<JS
        document.addEventListener('DOMContentLoaded', function () {
            var calendarEl = document.querySelector('[data-calendar-widget]');
            if (calendarEl) {
                var calendar = new FullCalendar.Calendar(calendarEl, {
                    eventMouseEnter: function(info) {
                        var tooltip = document.createElement('div');
                        tooltip.classList.add('calendar-tooltip');
                        tooltip.innerHTML = '<strong>' + info.event.title + '</strong><br>' +
                                            'Status: ' + info.event.extendedProps.status + '<br>' +
                                            'School: ' + info.event.extendedProps.school_name + '<br>' +
                                            (info.event.extendedProps.description || '');
                        document.body.appendChild(tooltip);
                        tooltip.style.left = info.jsEvent.pageX + 'px';
                        tooltip.style.top = info.jsEvent.pageY + 'px';
                    },
                    eventMouseLeave: function() {
                        var tooltips = document.querySelectorAll('.calendar-tooltip');
                        tooltips.forEach(t => t.remove());
                    },
                });
                calendar.render();
            }
        });
JS;
    }
}
