<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VisitEntry extends Model
{

    use HasFactory;

    protected $fillable = [
        'start_time',
        'end_time',
        'user_id',
        'sales_lead_management_id',
        'starting_meter_photo',
        'ending_meter_photo',
        'travel_type',
        'travel_bill',
        'travel_expense',
        'starting_km',
        'ending_km',
        'travel_mode',


    ];


    public function user()
{
    return $this->belongsTo(User::class, 'user_id');
}

public function roles()
{
    return $this->user->roles(); // Delegate roles relationship to the user
}

    public function trainerVisit()
    {
        return $this->hasOne(TrainerVisit::class);
    }

// E.g., in SalesLeadManagement.php
public function leadStatuses()
{
    return $this->hasMany(SalesLeadStatus::class, 'visit_entry_id');
}





protected static function boot()
{
    parent::boot();

    // After saving a VisitEntry, update or create the corresponding TrainerVisit
    static::saved(function ($visitEntry) {



        




        if ( $visitEntry->travel_type == 'own_vehicle') {

            TrainerVisit::updateOrCreate(
                ['visit_entry_id' => $visitEntry->id], // Match by visit_entry_id
                [
                    'starting_meter_photo' => $visitEntry->starting_meter_photo,
                    'starting_km' => $visitEntry->starting_km,
                    'ending_km' => $visitEntry->ending_km,
                    'ending_meter_photo' => $visitEntry->ending_meter_photo,
                    'travel_type' => $visitEntry->travel_type,
                    'travel_mode' => $visitEntry->travel_mode,
                ]
            );

        }



        if ( $visitEntry->travel_type == 'with_colleague') {

            TrainerVisit::updateOrCreate(
                ['visit_entry_id' => $visitEntry->id], // Match by visit_entry_id
                [
                    'travel_type' => $visitEntry->travel_type,
                    'travel_bill' => $visitEntry->travel_bill,
                    'travel_expense' => $visitEntry->travel_expense,

                ]
            );


        }
      
    });
}



}
