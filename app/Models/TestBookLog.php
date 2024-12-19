<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;


class TestBookLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'school_id',
        'lead_id',
        'book_id',
        'action',
        'count',
        'remarks',
        'follow_up_date',
        'created_by',
    ];

   
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    
    
    public function lead()
    {
        return $this->belongsTo(SalesLeadManagement::class);
    }

    
    
    public function book()
    {
        return $this->belongsTo(Book::class);
    }

  
    
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }



    protected static function boot()
    {
        parent::boot();
    
        static::creating(function ($model) {
            if (Auth::check()) {
                $model->created_by = Auth::id();
            }
    
            // Fetch the issued book record for stock validation
            $issuedBook = \App\Models\IssuedBook::where('user_id', $model->created_by)
                ->where('book_id', $model->book_id)
                ->first();
    
            if (!$issuedBook) {
                \Filament\Notifications\Notification::make()
                    ->title('Stock Error')
                    ->body('Book not found in the user\'s stock.')
                    ->danger()
                    ->send();
    
                // Prevent the modal from closing
                session()->flash('keepModalOpen', true);
    
                return false; // Stop saving the record
            }
    
            if ($model->action === 'issued') {
                // Check if there is enough stock to issue
                if ($issuedBook->stock_count < $model->count) {
                    \Filament\Notifications\Notification::make()
                        ->title('Insufficient Stock')
                        ->body("You only have {$issuedBook->stock_count} copies available. Cannot issue {$model->count} books.")
                        ->danger()
                        ->send();
    
                    // Prevent the modal from closing
                    session()->flash('keepModalOpen', true);
    
                    return false; // Stop saving the record
                }
    
                // Decrement stock
                $issuedBook->stock_count -= $model->count;
            } elseif ($model->action === 'returned') {
                // Increment stock
                $issuedBook->stock_count += $model->count;
            }
    
            // Save updated stock
            $issuedBook->save();
        });
    }
    
}