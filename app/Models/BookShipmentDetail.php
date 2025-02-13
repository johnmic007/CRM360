<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookShipmentDetail extends Model
{
    use HasFactory;

    protected $fillable = ['book_shipment_id', 'book_id', 'quantity'];

    public function bookShipment()
    {
        return $this->belongsTo(BookShipment::class);
    }

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    protected static function boot()
    {
        parent::boot();
    
        // static::saving(function ($shipmentDetail) {
        //     // Retrieve the school_id using the book_shipment_id
        //     $schoolId = BookShipment::where('id', $shipmentDetail->book_shipment_id)
        //         ->value('school_id');
    
        //     if (!$schoolId) {
        //         \Filament\Notifications\Notification::make()
        //             ->title('Error')
        //             ->danger()
        //             ->body('Invalid school ID.')
        //             ->send();
    
        //         return false; // Stop the saving process
        //     }
    
        //     // Fetch the related SchoolBook record
        //     $schoolBook = SchoolBook::where('school_id', $schoolId)
        //         ->where('book_id', $shipmentDetail->book_id)
        //         ->first();
    
        //     if (!$schoolBook) {
        //         \Filament\Notifications\Notification::make()
        //             ->title('Error')
        //             ->danger()
        //             ->body('Book is not allocated to the selected school.')
        //             ->send();
    
        //         return false; // Stop the saving process
        //     }
    
        //     // Calculate remaining available books
        //     $remainingBooks = $schoolBook->books_count - $schoolBook->issued_books_count;
    
        //     if ($shipmentDetail->quantity > $remainingBooks) {
        //         \Filament\Notifications\Notification::make()
        //             ->title('Insufficient Stock')
        //             ->danger()
        //             ->body('Not enough books available. Remaining: ' . $remainingBooks)
        //             ->send();
    
        //         return false; // Stop the saving process
        //     }
    
        //     // Update the issued_books_count in the school_book table
        //     $schoolBook->increment('issued_books_count', $shipmentDetail->quantity);
    
        //     \Filament\Notifications\Notification::make()
        //         ->title('Success')
        //         ->success()
        //         ->body('Book shipment detail saved successfully.')
        //         ->send();
        // });
    }
    


}
