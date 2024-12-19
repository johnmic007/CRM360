<?php

// app/Models/TrainerVisit.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrainerVisit extends Model
{
    protected $fillable = [
        'user_id',
        'school_id',
        'company_id',
        'visit_date',
        'travel_mode',
        'starting_meter_photo',
        'starting_km',
        'ending_meter_photo',
        'ending_km',
        'distance_traveled',
        'travel_expense',
        'food_expense',
        'total_expense',
        'description',
        'gps_photo',
        'travel_bill',
        'approved_by',
        'approval_status',
        'sales_role_evaluation',
        'travel_type',
        'travel_bill',
    ];


    protected $casts = [
        
        'school_id' => 'array', 
        'travel_bill' => 'array', 

    ];
    // Calculate the travel expense based on the mode of transport
    public function calculateTravelExpense()
    {
        $rate = $this->travel_mode == 'car' ? Setting::getCarRate() : Setting::getBikeRate();
        return $this->distance_traveled * $rate;
    }

    // Calculate the total expense including food
    public function calculateTotalExpense()
    {
        $foodExpenseRate = Setting::getFoodExpenseRate();
        $this->food_expense = $foodExpenseRate;
        $this->travel_expense = $this->calculateTravelExpense();
        $this->total_expense = $this->travel_expense + $this->food_expense  ;
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }


    public function schools()
{
    return School::whereIn('id', $this->school_id)->get();
}


    

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Approve the visit and deduct from trainer's wallet
    public function approveVisit()
    {
        if ($this->approval_status == 'approved') {
            // Deduct from wallet
            $trainer = $this->trainer;
            $trainer->wallet_balance -= $this->total_expense;
            $trainer->save();
        }
    }


    protected static function boot()
    {
        parent::boot();

        static::saving(function ($trainerVisit) {
            // Ensure travel_mode and distance_traveled are set
            if ($trainerVisit->travel_mode && $trainerVisit->distance_traveled) {
                $rate = $trainerVisit->travel_mode === 'car'
                    ? Setting::getCarRate() // Fetch car rate from settings
                    : Setting::getBikeRate(); // Fetch bike rate from settings

                $trainerVisit->travel_expense = $rate * $trainerVisit->distance_traveled;
            } else {
                $trainerVisit->travel_expense = 0; // Default to 0 if data is incomplete
            }


            $trainerVisit->food_expense = Setting::getFoodExpenseRate();
        });
    }
    
}
