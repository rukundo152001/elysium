<x-admin-layout>
{{-- resources/views/admin/locations/index.blade.php --}}

<div class="p-6">

    <div class="flex items-center justify-between mb-5">
        <h2 class="text-2xl font-semibold text-gray-800">Manage Locations</h2>

        <div class="flex items-center space-x-3">
            {{-- Level Selector --}}
            <form method="GET" action="{{ route('admin.locations.index') }}">
                <select 
                    name="level" 
                    onchange="this.form.submit()" 
                    class="border border-gray-300 rounded-lg px-3 py-2 bg-white shadow-sm focus:ring-teal-500 focus:border-teal-500 text-sm"
                >
                    @php
                        $levels = ['country','province','district','sector'];
                    @endphp
                    @foreach($levels as $l)
                        <option value="{{ $l }}" {{ $level === $l ? 'selected' : '' }}>
                            {{ ucfirst($l) }}
                        </option>
                    @endforeach
                </select>
            </form>

            {{-- Create Buttons --}}
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.locations.create', ['level' => $level]) }}"
                    class="bg-teal-600 hover:bg-teal-700 text-white px-4 py-2 rounded-lg text-sm font-medium shadow">
                    + Add {{ ucfirst($level) }}
                </a>

                {{-- Quick Add Dropdown --}}
                <div x-data="{ open:false }" class="relative">
                    <button @click="open = !open" class="px-3 py-2 bg-gray-100 rounded border text-sm">Quick Add</button>
                    <div x-show="open" @click.outside="open=false" class="absolute right-0 mt-2 bg-white border rounded shadow z-50 w-56">
                        <div class="p-2">
                            @foreach($levels as $l)
                                <a href="{{ route('admin.locations.create', ['level' => $l]) }}" 
                                   class="block px-3 py-2 hover:bg-gray-50 rounded text-sm">
                                   Add {{ ucfirst($l) }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="mb-3 text-green-600 bg-green-50 border border-green-200 rounded-lg p-3">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-3 text-red-600 bg-red-50 border border-red-200 rounded-lg p-3">
            {{ session('error') }}
        </div>
    @endif

    {{-- Table --}}
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="min-w-full table-auto divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-2 text-left font-semibold text-gray-700">#</th>
                    <th class="px-4 py-2 text-left font-semibold text-gray-700">Name</th>

                    @if($level !== 'country')
                        <th class="px-4 py-2 text-left font-semibold text-gray-700">
                            Parent {{ ucfirst($parents ? array_key_first($parents) : 'Location') }}
                        </th>
                    @endif

                    <th class="px-4 py-2 text-left font-semibold text-gray-700">Actions</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-200">
                @forelse($items as $i)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2 text-gray-700">{{ $i->id }}</td>
                        <td class="px-4 py-2 font-medium text-gray-800">{{ $i->name }}</td>

                        {{-- Parent --}}
                        @if($level !== 'country')
                            <td class="px-4 py-2 text-gray-600">
                                @switch($level)
                                    @case('province')
                                        {{ $i->country->name ?? '—' }}
                                        @break
                                    @case('district')
                                        {{ $i->province->name ?? '—' }}
                                        @break
                                    @case('sector')
                                        {{ $i->district->name ?? '—' }}
                                        @break
                                @endswitch
                            </td>
                        @endif

                        {{-- Actions --}}
                        <td class="px-4 py-2 flex items-center space-x-2">
                            <!-- View Data -->
                            <a href="{{ route('admin.locations.view', ['level' => $level, 'id' => $i->id]) }}"
                                class="text-gray-700 bg-gray-100 px-2 py-1 rounded text-sm hover:bg-gray-200">
                                View Data
                            </a>

                            <!-- Edit -->
                            <a href="{{ route('admin.locations.edit', ['level' => $level, 'id' => $i->id]) }}"
                                class="text-indigo-600 hover:text-indigo-800 font-medium">Edit</a>

                            <!-- Delete -->
                            <form method="POST" action="{{ route('admin.locations.destroy', ['level' => $level, 'id' => $i->id]) }}"
                                onsubmit="return confirm('Are you sure?')" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $level === 'country' ? 3 : 4 }}" class="px-4 py-3 text-center text-gray-500">
                            No {{ $level }} records found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Pagination --}}
        <div class="p-4 bg-gray-50 border-t">
            {{ $items->links() }}
        </div>
    </div>
</div>

</x-admin-layout>
