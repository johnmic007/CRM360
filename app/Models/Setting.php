<?php

// app/Models/Setting.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'id',
        'car_rate',
        'bike_rate',
        'food_expense_rate',
    ];

    // Get the food expense rate from the settings
    public static function getFoodExpenseRate()
    {
        return self::first()->food_expense_rate ?? 0;
    }

    // Get car and bike rates
    public static function getCarRate()
    {
        return self::first()->car_rate ?? 0;
    }

    public static function getBikeRate()
    {
        return self::first()->bike_rate ?? 0;
    }
}
