<?php

// app/Models/TrainerVisit.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class TrainerVisit extends Model 
{

    // use InteractsWithMedia;

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
        'created_by',
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
        $this->total_expense = $this->travel_expense + $this->food_expense;
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


    public function creator()
    {
        return $this->belongsTo(User::class , 'created_by');
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


    // Set the user_id from the authenticated user
    if (empty($trainerVisit->user_id)) {
        $trainerVisit->user_id = Auth::id();
    }

    // Assign company_id only if it's not already set
    if (empty($trainerVisit->company_id)) {
        $trainerVisit->company_id = Auth::user()->company_id;
    }

    // Set the visit_date if it's not already provided
    if (empty($trainerVisit->visit_date)) {
        $trainerVisit->visit_date = now();
    }


            if ($trainerVisit->travel_type !== 'extra_expense'){
                // Calculate distance_traveled by subtracting starting_km from ending_km





                if ($trainerVisit->travel_type == 'own_vehicle' ){

                    if (!is_null($trainerVisit->starting_km) && !is_null($trainerVisit->ending_km)) {
                        $trainerVisit->distance_traveled = $trainerVisit->ending_km - $trainerVisit->starting_km;
        
                        // dd($trainerVisit);
        
                    
                            $rate = ($trainerVisit->travel_mode === 'car')
                                ? Setting::getCarRate() // Fetch car rate from settings
                                : Setting::getBikeRate(); // Fetch bike rate from settings
            
            
            
                            $trainerVisit->travel_expense = $rate * $trainerVisit->distance_traveled;
                        
        
                    }

                }

                if ($trainerVisit->travel_type == 'with_colleague' ){

                }

                // dd($trainerVisit);



            // Set the food expense from settings
            $trainerVisit->food_expense = Setting::getFoodExpenseRate();

            // Calculate total expense
            $trainerVisit->total_expense = $trainerVisit->travel_expense + $trainerVisit->food_expense;
            }
        });
    }
}
