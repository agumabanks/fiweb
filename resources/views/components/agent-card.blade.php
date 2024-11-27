<!-- resources/views/components/agent-card.blade.php -->
<div class="bg-white shadow-lg rounded-lg p-6 flex flex-col justify-between">
    <!-- Agent Information -->
    <div>
        <div class="flex items-center mb-4">
            <div class="bg-indigo-500 text-white rounded-full h-12 w-12 flex items-center justify-center mr-4">
                <!-- Agent Initials -->
                <span class="text-lg font-semibold">{{ strtoupper(substr($agent['agent_name'], 0, 1)) }}</span>
            </div>
            <div>
                <h2 class="text-xl font-semibold text-gray-800">{{ $agent['agent_name'] }}</h2>
                <p class="text-sm text-gray-500">Total Clients: {{ $agent['total_clients'] }}</p>
            </div>
        </div>

        <!-- Today's Performance -->
        <div class="mb-4">
            <h3 class="text-md font-medium text-gray-700">Today's Performance</h3>
            <div class="mt-2 space-y-1">
                <div class="flex justify-between text-gray-600">
                    <span>Paid</span>
                    <span>{{ $agent['total_clients_paid_today'] }} ({{ $agent['percentage_paid'] }}%)</span>
                </div>
                <div class="flex justify-between text-gray-600">
                    <span>Unpaid</span>
                    <span>{{ $agent['total_clients_unpaid_today'] }} ({{ $agent['percentage_unpaid'] }}%)</span>
                </div>
                <div class="flex justify-between text-gray-600">
                    <span>Advance Paid</span>
                    <span>{{ $agent['total_clients_advance_paid_today'] }} ({{ $agent['percentage_advance_paid'] }}%)</span>
                </div>
                <div class="flex justify-between text-gray-600">
                    <span>Total Installments Collected</span>
                    <span>{{ $agent['total_installments_collected_today'] }}</span>
                </div>
            </div>
        </div>

        <!-- Financials -->
        <div class="mb-4">
            <h3 class="text-md font-medium text-gray-700">Financials</h3>
            <div class="mt-2 space-y-1">
                <div class="flex justify-between text-gray-600">
                    <span>Amount Collected</span>
                    <span>${{ $agent['total_amount_collected_today'] }}</span>
                </div>
                <div class="flex justify-between text-gray-600">
                    <span>Expected Collection</span>
                    <span>${{ $agent['total_expected_collection_today'] }}</span>
                </div>
                <div class="flex justify-between text-gray-600">
                    <span>Average Payment per Client</span>
                    <span>${{ $agent['average_payment_per_client'] }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- New Clients Badge -->
    <div class="text-center mt-4">
        <span class="inline-block bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">
            New Clients Today: {{ $agent['new_clients_today'] }}
        </span>
    </div>
</div>
