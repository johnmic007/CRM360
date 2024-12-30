<div>
    @if ($showLeadsModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div class="bg-white rounded-lg shadow-lg p-6 w-2/3">
                <h2 class="text-lg font-bold mb-4">Lead Status Details</h2>
                <table class="table-auto w-full text-left border-collapse">
                    <thead>
                        <tr>
                            <th class="border px-4 py-2">ID</th>
                            <th class="border px-4 py-2">Lead Management ID</th>
                            <th class="border px-4 py-2">Status</th>
                            <th class="border px-4 py-2">Remarks</th>
                            <th class="border px-4 py-2">Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($selectedVisitLeads as $lead)
                            <tr>
                                <td class="border px-4 py-2">{{ $lead['id'] }}</td>
                                <td class="border px-4 py-2">{{ $lead['sales_lead_management_id'] }}</td>
                                <td class="border px-4 py-2">{{ $lead['status'] }}</td>
                                <td class="border px-4 py-2">{{ $lead['remarks'] }}</td>
                                <td class="border px-4 py-2">{{ $lead['created_at'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">No Leads Found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="mt-4 text-right">
                    <button class="px-4 py-2 bg-gray-700 text-white rounded-lg" wire:click="closeModal">Close</button>
                </div>
            </div>
        </div>
    @endif
</div>
