<?php

// app/Models/TrainerVisit.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class TrainerVisit extends Model
{
    protected $fillable = [
        'id',
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
        'approved_at',
        'approval_status',
        'verified_by',
        'verify_status',
        'clarification_question',
        'clarification_answer',
        'verified_at',
        'sales_role_evaluation',
        'travel_type',
        'files',
        'visit_entry_id',
    ];


    public function leadStatuses()
{
    return $this->hasMany(SalesLeadStatus::class, 'visit_entry_id', 'visit_entry_id');
}



    protected $casts = [
        
        'school_id' => 'array', 
        'travel_bill' => 'array', 
        'files' => 'array', 


    ];
    // Calculate the travel expense based on the mode of transport
    public function calculateTravelExpense()
    {
        $rate = $this->travel_mode == 'car' ? Setting::getCarRate() : Setting::getBikeRate();
        return $this->distance_traveled * $rate;
    }


    public function visitEntry()
    {
        return $this->belongsTo(VisitEntry::class);
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


    public function visitedSchool()
{
    return $this->hasMany(SalesLeadStatus::class, 'visit_entry_id');
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


    static::creating(function ($trainerVisit) {
        // Set the user_id from the authenticated user
        if (Auth::check()) {
            $trainerVisit->user_id = Auth::id();
            
            // Set the company_id from the related user
            $trainerVisit->company_id = Auth::user()->company_id;
        }

        // Set the visit_date if it's not already provided
        if (empty($trainerVisit->visit_date)) {
            $trainerVisit->visit_date = now();
        }
    });


    static::saving(function ($trainerVisit) {
    
        

        // Calculate distance_traveled by subtracting starting_km from ending_km
        if (!is_null($trainerVisit->starting_km) && !is_null($trainerVisit->ending_km)) {
            $trainerVisit->distance_traveled = $trainerVisit->ending_km - $trainerVisit->starting_km;

            // Ensure the distance is not negative
            if ($trainerVisit->distance_traveled < 0) {
                throw new \Exception('Ending KM must be greater than or equal to Starting KM.');
            }
        }

        // Calculate travel expense based on travel_mode
        if (
            $trainerVisit->travel_mode &&
            $trainerVisit->distance_traveled &&
            !$trainerVisit->travel_expense
        ) {
            $rate = ($trainerVisit->travel_mode === 'car')
                ? Setting::getCarRate() // Fetch car rate from settings
                : Setting::getBikeRate(); // Fetch bike rate from settings
        
            $trainerVisit->travel_expense = $rate * $trainerVisit->distance_traveled;
        }
        
        // Set the food expense from settings
        $trainerVisit->food_expense = Setting::getFoodExpenseRate();

        // Calculate total expense
        $trainerVisit->total_expense = $trainerVisit->travel_expense + $trainerVisit->food_expense ;
    });
}

    
}
