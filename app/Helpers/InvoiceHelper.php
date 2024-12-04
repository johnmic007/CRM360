<?php

namespace App\Helpers;

use App\Models\Book;

class InvoiceHelper
{
    public static function calculateTotalAmount($items)
{
    $totalAmount = 0;

    // Check if $items is an array and not null
    if (is_array($items) || is_object($items)) {
        foreach ($items as $item) {
            // Ensure each item has quantity and price
            if (isset($item['quantity']) && isset($item['price'])) {
                $totalAmount += ($item['quantity'] * $item['price']);
            }
        }
    }

    return $totalAmount;
}

public static function calculateBookTotal($quantity, $bookId)
{
    $book = Book::find($bookId); // Find the book by ID

    if ($book) {
        return $book->price * $quantity; // Calculate and return the total (price * quantity)
    }

    return 0; // Return 0 if the book is not found
}

}
