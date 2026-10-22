<x-admin-layout :title="'Admin Dashboard'">
    <div class="max-w-7xl mx-auto">

    <!-- ======================== -->
    <!--      STATS CARDS         -->
    <!-- ======================== -->
    <div class="grid grid-cols-1 md:grid-cols-5 lg:grid-cols-5 gap-6 mb-6">


        @include('components.stat-card', [
            'label' => 'Total Patients',
            'value' => number_format( $stats['totalPatients']),
            'subtitle' => '',
            'gradient' => 'from-green-500 to-green-600',
            'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>'
        ])

        @include('components.stat-card', [
            'label' => 'Total Revenue',
            'value' => 'Rwf ' . number_format( $stats['totalRevenue'], 1),
            'subtitle' => '',
            'gradient' => 'from-blue-500 to-blue-600',
            'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>'
        ])

        @include('components.stat-card', [
            'label' => 'Insurance Revenue',
            'value' => $stats['insurance_revenue'] ? 'Rwf ' . number_format( $stats['insurance_revenue'], 1) : 'Rwf 0',
            'subtitle' => 'Requires attention',
            'gradient' => 'from-yellow-500 to-yellow-600',
            'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>'
        ])

        @include('components.stat-card', [
            'label' => 'Insured Patient Revenue',
            'value' => $stats['insured_patient_revenue'] ? 'Rwf ' . number_format( $stats['insured_patient_revenue'], 1) : 'Rwf 0',
            'subtitle' => 'Insured patients',
            'gradient' => 'from-teal-500 to-teal-600',
            'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>'
        ])

        @include('components.stat-card', [
            'label' => 'Private Revenue',
            'value' => $stats['privateRevenue'] ? 'Rwf ' . number_format( $stats['privateRevenue'], 1) : 'Rwf 0',
            'subtitle' => 'Private payments',
            'gradient' => 'from-purple-500 to-purple-600',
            'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>'
        ])
        

    </div>

    <!-- ======================== -->
    <!--  TRANSACTIONS + ACTIVITY -->
    <!-- ======================== -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

        <!-- Recent Transactions -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Recent Transactions</h2>
            </div>

            <div class="p-6 space-y-4">
                @foreach($recentTransactions as $t)
                    <div class="flex items-center justify-between {{ !$loop->last ? 'pb-4 border-b border-gray-100' : '' }}">
                        
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>

                            <div>
                                <p class="text-sm font-medium text-gray-900">Service Added</p>
                                <p class="text-xs text-gray-500">{{ $t->patient->ref_number }} — {{ $t->service_name ?? '' }}</p>
                            </div>
                        </div>

                        <span class="text-sm font-semibold text-green-600">
                            + {{ number_format($t->unit_price, 1) }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- System Activity -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">System Activity</h2>
            </div>

            <div class="p-6 space-y-4">
                @foreach($systemActivity as $activity)
                    <div class="flex items-start space-x-3">
                        <div class="w-2 h-2 bg-blue-500 rounded-full mt-2"></div>

                        <div class="flex-1">
                            <p class="text-sm text-gray-900">
                                {!! $activity['message'] !!}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ $activity['time']->diffForHumans() }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

    </div>

    <!-- ======================== -->
    <!--  STAFF PERFORMANCE TABLE -->
    <!-- ======================== -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900">Staff Performance</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Staff Name
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Patients Handled
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Revenue Generated
                        </th>
                        <th class="px-6 py-3">Status</th>
                    </tr>
                </thead>

                <tbody class="bg-white divide-y divide-gray-200">

                    @foreach($staffPerformance as $staff)
                        <tr class="hover:bg-gray-50">

                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $staff['name'] }}
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                {{ $staff['patients_handled'] }}
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                Rwf {{ number_format($staff['revenue_generated'], 2) }}
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                                    {{ $staff['status'] }}
                                </span>
                            </td>

                        </tr>
                    @endforeach

                </tbody>

            </table>
        </div>
    </div>
</div>
</x-admin-layout>
