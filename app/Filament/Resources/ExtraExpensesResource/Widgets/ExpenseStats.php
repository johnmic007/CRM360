<?php

namespace App\Filament\Resources\ExtraExpensesResource\Widgets;

use App\Models\TrainerVisit;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class ExpenseStats extends BaseWidget
{
    protected function getCards(): array
    {
        // Get the applied category filter from the request
        $filters = request()->query('tableFilters', []);
        $selectedCategory = $filters['category'] ?? null;

        // Query total expenses
        $query = TrainerVisit::query();

        if ($selectedCategory) {
            $query->where('category', $selectedCategory);
        }

        // Get total expenses grouped by category (respecting filter)
        $totalsByCategory = $query->selectRaw('category, SUM(total_expense) as total')
            ->groupBy('category')
            ->pluck('total', 'category');

        // Define custom colors for categories
        $categoryColors = [
            'Travel' => 'info',
            'Marketing' => 'success',
            'Operations' => 'warning',
            'Equipment' => 'danger',
            'Miscellaneous' => 'gray',
        ];

        // Define icons for categories
        $categoryIcons = [
            'Travel' => 'heroicon-o-globe-alt',
            'Marketing' => 'heroicon-o-bullhorn',
            'Operations' => 'heroicon-o-cog',
            'Equipment' => 'heroicon-o-device-mobile',
            'Miscellaneous' => 'heroicon-o-collection',
        ];

        // Create category-wise statistic cards
        $cards = [];

        foreach ($totalsByCategory as $category => $total) {
            $color = $categoryColors[$category] ?? 'gray'; // Default color
            $icon = $categoryIcons[$category] ?? 'heroicon-o-currency-dollar'; // Default icon

            $cards[] = Card::make(Str::title($category) . " Expense", "₹" . number_format($total, 2))
                ->color($color)
                ->icon($icon)
                ->extraAttributes(['class' => 'text-lg font-semibold p-4 rounded-lg shadow-md']);
        }

        // Add an overall total card
        $totalOverall = $query->sum('total_expense');
        $cards[] = Card::make('💰 Total Expenses', "₹" . number_format($totalOverall, 2))
            ->color('primary')
            ->icon('heroicon-o-chart-bar')
            ->extraAttributes(['class' => 'text-xl font-bold p-5 rounded-xl shadow-lg bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 text-white']);

        return $cards;
    }
}
