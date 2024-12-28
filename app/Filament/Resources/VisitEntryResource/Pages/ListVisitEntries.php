<?php

namespace App\Filament\Resources\VisitEntryResource\Pages;

use App\Filament\Resources\VisitEntryResource;
use App\Models\VisitEntry;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;


class ListVisitEntries extends ListRecords
{
    protected static string $resource = VisitEntryResource::class;

    public function mount(): void
    {
        parent::mount();

        $userId = Auth::id();
        $todayDate = Carbon::now()->toDateString(); // Get only the current date (YYYY-MM-DD)

        // Fetch today's entry for the logged-in user based on the date
        $todayEntry = VisitEntry::where('user_id', $userId)
            ->whereDate('start_time', $todayDate)
            ->first();

        // If no entry exists, create one
        if (!$todayEntry) {
            $todayEntry = VisitEntry::create([
                'user_id' => $userId,
            ]);
        }

        // Redirect to the edit page for today's record
        $this->redirect(static::getResource()::getUrl('edit', ['record' => $todayEntry]));
    }
}
