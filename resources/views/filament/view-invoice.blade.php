<x-filament-panels::page>
    <div class="p-6 bg-white rounded shadow">
        <!-- Invoice Header -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold">Invoice</h1>
                <p class="text-gray-600">Invoice #: {{ $record->invoice_number }}</p>
                <p class="text-gray-600">Issue Date: {{ $record->issue_date->format('d/m/Y') }}</p>
                <p class="text-gray-600">Due Date: {{ $record->due_date ? $record->due_date->format('d/m/Y') : 'N/A' }}</p>
            </div>
            <div>
                <img src="{{ asset('path/to/your/logo.png') }}" alt="Company Logo" class="h-20">
            </div>
        </div>

        <!-- Parties Information -->
        <div class="flex justify-between mb-8">
            <div>
                <h2 class="text-xl font-semibold">Bill To:</h2>
                <p>{{ $record->school->name }}</p>
                <p>{{ $record->school->address }}</p>
                <p>{{ $record->school->email }}</p>
                <p>{{ $record->school->phone }}</p>
            </div>
            <div>
                <h2 class="text-xl font-semibold">From:</h2>
                <p>{{ $record->company->name }}</p>
                <p>{{ $record->company->address }}</p>
                <p>{{ $record->company->email }}</p>
                <p>{{ $record->company->phone }}</p>
            </div>
        </div>

        <!-- Items Table -->
        <table class="w-full mb-8 border-collapse">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border px-4 py-2 text-left">Item</th>
                    <th class="border px-4 py-2 text-left">Description</th>
                    <th class="border px-4 py-2 text-right">Quantity</th>
                    <th class="border px-4 py-2 text-right">Price</th>
                    <th class="border px-4 py-2 text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($record->items as $item)
                <tr>
                    <td class="border px-4 py-2">{{ $item->item_name }}</td>
                    <td class="border px-4 py-2">{{ $item->description }}</td>
                    <td class="border px-4 py-2 text-right">{{ $item->quantity }}</td>
                    <td class="border px-4 py-2 text-right">{{ number_format($item->price, 2) }}</td>
                    <td class="border px-4 py-2 text-right">{{ number_format($item->total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Books Section -->
        @if ($record->books->isNotEmpty())
        <div class="mt-8">
            <h2 class="text-xl font-bold mb-4">Books</h2>
            <table class="w-full mb-8 border-collapse">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border px-4 py-2 text-left">Book</th>
                        <th class="border px-4 py-2 text-right">Quantity</th>
                        <th class="border px-4 py-2 text-right">Price</th>
                        <th class="border px-4 py-2 text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($record->books as $book)
                    <tr>
                        <td class="border px-4 py-2">{{ $book->title }}</td>
                        <td class="border px-4 py-2 text-right">{{ $book->books_count }}</td>
                        <td class="border px-4 py-2 text-right">{{ number_format($book->price, 2) }}</td>
                        <td class="border px-4 py-2 text-right">{{ number_format($book->books_count * $book->price, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        <!-- Summary Section -->
        <div class="flex justify-end">
            <div class="w-1/2">
                <div class="flex justify-between mb-2">
                    <span class="font-semibold">Subtotal:</span>
                    <span>{{ number_format($record->items->sum('total') + $record->books->sum(fn($book) => $book->books_count * $book->price), 2) }}</span>
                </div>
                <div class="flex justify-between mb-2">
                    <span class="font-semibold">Total Amount:</span>
                    <span>{{ number_format($record->total_amount, 2) }}</span>
                </div>
                <div class="flex justify-between mb-2">
                    <span class="font-semibold">Amount Paid:</span>
                    <span>{{ number_format($record->paid, 2) }}</span>
                </div>
                <div class="flex justify-between mb-2">
                    <span class="font-semibold">Balance Due:</span>
                    <span>{{ number_format($record->total_amount - $record->paid, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Additional Information -->
        <div class="mt-8">
            <h2 class="text-lg font-semibold">Additional Information</h2>
            <ul>
                <li><strong>No. of Students:</strong> {{ $record->students_count }}</li>
                <li><strong>No. of Books:</strong> {{ $record->books_count }}</li>
                <li><strong>Trainer Required:</strong> {{ $record->trainer_required ? 'Yes' : 'No' }}</li>
                <li><strong>Validity:</strong> {{ $record->validity_start->format('d/m/Y') }} to {{ $record->validity_end->format('d/m/Y') }}</li>
            </ul>
        </div>

        <!-- Notes -->
        @if ($record->notes)
        <div class="mt-8">
            <h3 class="text-lg font-semibold">Notes:</h3>
            <p>{{ $record->notes }}</p>
        </div>
        @endif

        <!-- Print Button -->
        <div class="mt-8">
            <button onclick="window.print()" class="bg-blue-500 text-white px-4 py-2 rounded">Print Invoice</button>
        </div>
    </div>
</x-filament-panels::page>
