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
        'created_by',
        'sales_lead_management_id',
        'starting_meter_photo',
        'ending_meter_photo',
        'travel_type',
        'travel_bill',
        'travel_expense',
        'starting_km',
        'ending_km',
        'travel_mode',
        'visit_date',


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


    static::saving(function ($visitEntry) {
        // Ensure visit_date is present
        if ($visitEntry->visit_date) {
            $visitDate = $visitEntry->visit_date;

            // Concatenate visit_date with start_time and end_time
            if ($visitEntry->start_time) {
                $visitEntry->start_time = $visitDate . ' ' . $visitEntry->start_time;
            }

            if ($visitEntry->end_time) {
                $visitEntry->end_time = $visitDate . ' ' . $visitEntry->end_time;
            }
        }

        // dd($visitEntry);
    });


    // After saving a VisitEntry, update or create the corresponding TrainerVisit
    static::saved(function ($visitEntry) {



        




        if ( $visitEntry->travel_type == 'own_vehicle' && $visitEntry->ending_km) {

            TrainerVisit::updateOrCreate(
                ['visit_entry_id' => $visitEntry->id], // Match by visit_entry_id
                [
                    'starting_meter_photo' => $visitEntry->starting_meter_photo,
                    'user_id' => $visitEntry->user_id,
                    'visit_date' => $visitEntry->start_time,


                    'starting_km' => $visitEntry->starting_km,
                    'ending_km' => $visitEntry->ending_km,
                    'ending_meter_photo' => $visitEntry->ending_meter_photo,
                    'travel_type' => $visitEntry->travel_type,
                    'travel_mode' => $visitEntry->travel_mode,
                ]
            );

        }



        if ( $visitEntry->travel_type == 'with_colleague' && $visitEntry->end_time) {

            TrainerVisit::updateOrCreate(
                ['visit_entry_id' => $visitEntry->id], // Match by visit_entry_id
                [
                    'travel_type' => $visitEntry->travel_type,
                    'travel_bill' => $visitEntry->travel_bill,
                    'user_id' => $visitEntry->user_id,
                    'visit_date' => $visitEntry->start_time,


                    'travel_expense' => $visitEntry->travel_expense,

                ]
            );


        }
      
    });
}



}
