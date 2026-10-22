
<x-admin-layout>
<div class="min-h-screen bg-gray-50 text-gray-800 py-8 px-4">
    <div class="max-w-5xl mx-auto bg-white shadow-md rounded-2xl p-6 border border-gray-200">
        <h1 class="text-2xl font-semibold text-teal-600 mb-6 border-b border-gray-200 pb-3">Service Records</h1>

        
            
<div class="max-w-5xl mx-auto py-4">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Service Management</h1>
        <a href="{{ route('admin.services.create') }}" class="bg-teal-600 text-white px-6 py-3 rounded-lg">+ Add Service</a>
    </div>

    <!-- Errors & Success Messages -->
            @if(session('success'))
            <div class="mb-6 bg-green-50 border-l-4 border-green-500 text-green-800 px-6 py-4 rounded-r-lg shadow-sm">
                {{ session('success') }}
                </div>
                @endif
                @if(session('error'))
                <div class="mb-6 bg-red-50 border-l-4 border-red-500 text-red-800 px-6 py-4 rounded-r-lg shadow-sm">
                    {{ session('error') }}
                </div>
            @endif

    @php
        $grouped = $services->groupBy('category');
    @endphp

    @foreach ($grouped as $category => $items)
        <div class="bg-white rounded-lg shadow mb-8">
            <div class="bg-gray-100 p-4 font-semibold text-gray-700">
                {{ $category ?? 'Uncategorized' }}
            </div>
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="p-3 text-left">Name</th>
                        <th class="p-3 text-left">Price</th>
                        <th class="p-3 text-left">Description</th>
                        <th class="p-3 text-center">Status</th>
                        <th class="p-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $service)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="p-3">{{ $service->name }}</td>
                            <td class="p-3">{{ number_format($service->price, 2) }} RWF</td>
                            <td class="p-3">{{ $service->description ?? '—' }}</td>
                            <td class="p-3 text-center">
                                <span class="px-3 py-1 rounded-full text-xs {{ $service->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-600' }}">
                                    {{ $service->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="p-3 text-right">
                                <a href="{{ route('admin.services.edit', $service) }}" class="text-blue-600 hover:underline">Modify</a>
                                
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endforeach
</div>


        
    </div>
</div>
</x-admin-layout>

<style>
    button, [type='submit'] {
        @apply bg-teal-600 hover:bg-teal-700 text-white font-medium py-2 px-4 rounded-lg transition;
    }
    a.btn, .btn {
        @apply inline-block bg-teal-500 hover:bg-teal-600 text-white font-semibold py-2 px-4 rounded-lg transition;
    }
    input, select, textarea {
        @apply border-gray-300 focus:ring-teal-500 focus:border-teal-500 rounded-lg shadow-sm w-full;
    }
    table {
        @apply w-full border border-gray-200 rounded-lg overflow-hidden text-sm;
    }
    th {
        @apply bg-gray-100 text-left px-4 py-2 font-semibold text-gray-700;
    }
    td {
        @apply border-t px-4 py-2 text-gray-700;
    }
</style>
