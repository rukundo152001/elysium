<x-admin-layout>
<div class="p-6">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-semibold">View {{ ucfirst($level) }} Data</h2>
        <a href="{{ route('admin.locations.index', ['level' => $level]) }}" class="text-sm text-gray-500">Back to list</a>
    </div>

    <div class="bg-white shadow rounded-lg p-4">

        @if(isset($country))
            <h3 class="text-lg font-semibold mb-3">{{ $country->name }} (Country)</h3>

            <div class="mb-4">
                <a href="{{ route('admin.locations.create', ['level' => 'province', 'country_id' => $country->id]) }}" class="px-3 py-2 bg-teal-600 text-white rounded">Add Province</a>
            </div>

            @forelse($country->provinces as $prov)
                <div class="border rounded p-3 mb-3 bg-gray-50">
                    <div class="flex justify-between items-center">
                        <div>
                            <strong>Province:</strong> {{ $prov->name }}
                        </div>
                        <div class="flex items-center space-x-2">
                            <a href="{{ route('admin.locations.view',['level'=>'province','id'=>$prov->id]) }}" class="text-sm px-2 py-1 bg-gray-100 rounded">View</a>
                            <a href="{{ route('admin.locations.edit',['level'=>'province','id'=>$prov->id]) }}" class="text-sm text-indigo-600">Edit</a>
                            <form method="POST" action="{{ route('admin.locations.destroy',['level'=>'province','id'=>$prov->id]) }}" onsubmit="return confirm('Delete?')" style="display:inline;">
                                @csrf @method('DELETE')
                                <button class="text-red-600 text-sm">Delete</button>
                            </form>
                            <a href="{{ route('admin.locations.create', ['level' => 'district', 'province_id' => $prov->id, 'country_id' => $country->id]) }}" class="text-sm px-2 py-1 bg-green-100 rounded">Add District</a>
                        </div>
                    </div>

                    @if($prov->districts->count())
                        <div class="mt-3 pl-4">
                            @foreach($prov->districts as $dist)
                                <div class="border-l pl-3 pb-2 mb-2">
                                    <div class="flex justify-between items-center">
                                        <div><strong>District:</strong> {{ $dist->name }}</div>
                                        <div class="flex items-center space-x-2">
                                            <a href="{{ route('admin.locations.view',['level'=>'district','id'=>$dist->id]) }}" class="text-sm px-2 py-1 bg-gray-100 rounded">View</a>
                                            <a href="{{ route('admin.locations.edit',['level'=>'district','id'=>$dist->id]) }}" class="text-sm text-indigo-600">Edit</a>
                                            <form method="POST" action="{{ route('admin.locations.destroy',['level'=>'district','id'=>$dist->id]) }}" onsubmit="return confirm('Delete?')" style="display:inline;">
                                                @csrf @method('DELETE')
                                                <button class="text-red-600 text-sm">Delete</button>
                                            </form>
                                            <a href="{{ route('admin.locations.create', ['level' => 'sector', 'district_id' => $dist->id, 'province_id' => $prov->id, 'country_id' => $country->id]) }}" class="text-sm px-2 py-1 bg-green-100 rounded">Add Sector</a>
                                        </div>
                                    </div>

                                    @if($dist->sectors->count())
                                        <div class="mt-2 pl-3">
                                            @foreach($dist->sectors as $sec)
                                                <div class="mb-1">
                                                    <div class="flex justify-between items-center">
                                                        <div><strong>Sector:</strong> {{ $sec->name }}</div>
                                                        <div class="flex items-center space-x-2">
                                                            <a href="{{ route('admin.locations.view',['level'=>'sector','id'=>$sec->id]) }}" class="text-sm px-2 py-1 bg-gray-100 rounded">View</a>
                                                            <a href="{{ route('admin.locations.edit',['level'=>'sector','id'=>$sec->id]) }}" class="text-sm text-indigo-600">Edit</a>
                                                            <form method="POST" action="{{ route('admin.locations.destroy',['level'=>'sector','id'=>$sec->id]) }}" onsubmit="return confirm('Delete?')" style="display:inline;">
                                                                @csrf @method('DELETE')
                                                                <button class="text-red-600 text-sm">Delete</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @empty
                <div class="text-gray-500">No provinces found for this country.</div>
            @endforelse
        @endif


        @if(isset($province))
            <h3 class="text-lg font-semibold mb-3">{{ $province->name }} (Province)</h3>
            <div class="mb-4">
                <a href="{{ route('admin.locations.create', ['level' => 'district', 'province_id' => $province->id, 'country_id' => $province->country_id]) }}" class="px-3 py-2 bg-teal-600 text-white rounded">Add District</a>
            </div>

            @foreach($province->districts as $dist)
                <div class="border rounded p-3 mb-3">
                    <div class="flex justify-between items-center">
                        <div><strong>District:</strong> {{ $dist->name }}</div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.locations.edit',['level'=>'district','id'=>$dist->id]) }}" class="text-sm text-indigo-600">Edit</a>
                            <form method="POST" action="{{ route('admin.locations.destroy',['level'=>'district','id'=>$dist->id]) }}" onsubmit="return confirm('Delete?')" style="display:inline;">
                                @csrf @method('DELETE')
                                <button class="text-red-600 text-sm">Delete</button>
                            </form>
                            <a href="{{ route('admin.locations.create', ['level' => 'sector', 'district_id' => $dist->id, 'province_id' => $province->id, 'country_id' => $province->country_id]) }}" class="text-sm px-2 py-1 bg-green-100 rounded">Add Sector</a>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif


        @if(isset($district))
            <h3 class="text-lg font-semibold mb-3">{{ $district->name }} (District)</h3>
            <div class="mb-4">
                <a href="{{ route('admin.locations.create', ['level' => 'sector', 'district_id' => $district->id, 'province_id' => $district->province_id, 'country_id' => $district->province->country_id ?? null]) }}" class="px-3 py-2 bg-teal-600 text-white rounded">Add Sector</a>
            </div>

            @foreach($district->sectors as $sec)
                <div class="border rounded p-3 mb-3">
                    <div class="flex justify-between items-center">
                        <div><strong>Sector:</strong> {{ $sec->name }}</div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.locations.edit',['level'=>'sector','id'=>$sec->id]) }}" class="text-sm text-indigo-600">Edit</a>
                            <form method="POST" action="{{ route('admin.locations.destroy',['level'=>'sector','id'=>$sec->id]) }}" onsubmit="return confirm('Delete?')" style="display:inline;">
                                @csrf @method('DELETE')
                                <button class="text-red-600 text-sm">Delete</button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif


        @if(isset($sector))
            <h3 class="text-lg font-semibold mb-3">{{ $sector->name }} (Sector)</h3>
            {{-- Sector has no children now --}}
        @endif

    </div>
</div>
</x-admin-layout>
