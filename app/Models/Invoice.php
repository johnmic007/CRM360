<?php

namespace App\Models;

use App\Models\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'school_id',
        'company_id',
        'issue_date',
        'total',
        'due_date',
        'paid',
        'total_amount',
        'due_amount',
        'status',
        'students_count',
        'trainer_required',
        'validity_start',
        'validity_end',
        'books_count',
        'closed_by',
        'description',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }





    // public function books()
    // {
    //     return $this->belongsToMany(Book::class, 'school_book', 'invoice_id', 'book_id')
    //         ->withPivot('school_id', 'books_count')
    //         ->withTimestamps();
    // }

    


    public function books()
    {
        return $this->hasMany(SchoolBook::class);
    }




    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function logs()
    {
        return $this->hasMany(InvoiceLog::class);
    }

    protected static function booted()
    {
        static::addGlobalScope(new CompanyScope());
    }

    

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invoice) {
            if (Auth::check()) {
                $invoice->company_id = Auth::user()->company_id; // Set the user's company_id
            }
        });
      
        
          // Trigger the total calculation before saving or updating
          static::saving(function ($invoice) {
            $totalAmount = 0;

            // Ensure relationships are loaded if using them
            $invoice->load(['books', 'items']); // Eager load books and items relationships

            // Loop through books and calculate total
            foreach ($invoice->books as $book) {
                $totalAmount += $book->total ?? 0;
            }

            // Loop through items and calculate total
            foreach ($invoice->items as $item) {
                $totalAmount += $item->total ?? 0;
            }

            // Set the total amount for the invoice
            $invoice->total_amount = $totalAmount;

            // Calculate the due amount by subtracting the paid amount
            $dueAmount = $totalAmount - ($invoice->paid ?? 0);

            // Set the due amount
            $invoice->due_amount = $dueAmount;
        });
    

   

        static::created(function ($invoice) {
            static::withoutEvents(function () use ($invoice) {
                $invoice->logs()->create([
                    'type' => 'created',
                    'description' => 'Invoice created with number ' . $invoice->invoice_number,
                ]);
            });
        });

        static::updated(function ($invoice) {
            static::withoutEvents(function () use ($invoice) {
                $invoice->logs()->create([
                    'type' => 'edited',
                    'description' => 'Invoice updated with number ' . $invoice->invoice_number,
                ]);
            });
        });

        static::deleted(function ($invoice) {
            static::withoutEvents(function () use ($invoice) {
                $invoice->logs()->create([
                    'type' => 'deleted',
                    'description' => 'Invoice deleted with number ' . $invoice->invoice_number,
                ]);
            });
        });
    }


    
}
