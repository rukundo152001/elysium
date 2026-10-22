{{-- Admin Insurance Hierarchical Index --}}
<x-admin-layout>
    <div class="max-w-7xl mx-auto px-4 py-6">

        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-semibold text-gray-800">Insurance Providers, Categories & Packages</h1>
            <div class="space-x-2">
                <a href="{{ route('admin.insurances.create', ['type' => 'provider']) }}"
                   class="bg-teal-600 hover:bg-teal-700 text-white px-4 py-2 rounded-md shadow">
                    + Add Provider
                </a>
                <a href="{{ route('admin.insurances.create', ['type' => 'category']) }}"
                   class="bg-teal-500 hover:bg-teal-600 text-white px-4 py-2 rounded-md shadow">
                    + Add Category
                </a>
                <a href="{{ route('admin.insurances.create', ['type' => 'package']) }}"
                   class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md shadow">
                    + Add Package
                </a>
            </div>
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

        {{-- Providers Hierarchical Table --}}
        <div class="bg-white rounded-lg shadow border border-gray-200">
            <div class="p-4">
                @forelse ($providers as $provider)
                    <div class="mb-6 border-b border-gray-200 pb-4">
                        <div class="flex justify-between items-center">
    <h2 class="text-xl font-semibold text-teal-700">
        {{ $provider->name }}
    </h2>
    <div class="space-x-2">
        <a href="{{ route('admin.insurances.edit', $provider->id) }}"
           class="text-teal-600 hover:text-teal-800 font-medium">Edit</a>

        <form action="{{ route('admin.insurances.destroy', $provider->id) }}" method="POST" class="inline">
            @csrf
            @method('DELETE')
            <button type="submit"
                    onclick="return confirm('Are you sure you want to delete this insurance provider? This will remove all related categories and packages.')"
                    class="text-red-600 hover:text-red-800 font-medium">
                Delete
            </button>
        </form>
    </div>
</div>
                        <p class="text-gray-500 text-sm mb-2">{{ $provider->description ?? 'No description available.' }}</p>

                        {{-- Categories --}}
                        @if($provider->categories->count())
                            <ul class="ml-6 border-l border-gray-300 pl-4 space-y-4">
                                @foreach($provider->categories as $category)
                                    <li>
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <h3 class="font-semibold text-gray-700">{{ $category->name }}</h3>
                                                <p class="text-gray-500 text-sm">{{ $category->description ?? '' }}</p>
                                            </div>
                                            <div class="space-x-2">
                                                <a href="{{ route('admin.insurances.edit', ['type' => 'category', 'id' => $category->id]) }}"
                                                   class="text-teal-600 hover:text-teal-800 font-medium">Edit</a>
                                                <form action="{{ route('admin.insurances.destroy', ['type' => 'category', 'id' => $category->id]) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" onclick="return confirm('Delete this category?')" class="text-red-600 hover:text-red-800 font-medium">Delete</button>
                                                </form>
                                            </div>
                                        </div>

                                        {{-- Packages --}}
                                        @if($category->packages->count())
                                            <table class="mt-2 w-full text-sm text-gray-700 border border-gray-200 rounded">
                                                <thead class="bg-gray-100">
                                                    <tr>
                                                        <th class="px-3 py-2 text-left">Package Name</th>
                                                        <th class="px-3 py-2 text-left">Patient Cover (%)</th>
                                                        <th class="px-3 py-2 text-left">Insurance Cover (%)</th>
                                                        <th class="px-3 py-2 text-center">Manage</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($category->packages as $package)
                                                        <tr class="border-t">
                                                            <td class="px-3 py-2">{{ $package->package_name }}</td>
                                                            <td class="px-3 py-2">{{ $package->patient_cover }}%</td>
                                                            <td class="px-3 py-2">{{ $package->insurance_cover }}%</td>
                                                            <td class="px-3 py-2 text-center">
                                                                <a href="{{ route('admin.insurances.edit', ['type' => 'package', 'id' => $package->id]) }}"
                                                                   class="text-teal-600 hover:text-teal-800 font-medium">Edit</a> |
                                                                <form action="{{ route('admin.insurances.destroy', ['type' => 'package', 'id' => $package->id]) }}"
                                                                      method="POST" class="inline">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" onclick="return confirm('Delete this package?')" class="text-red-600 hover:text-red-800 font-medium">
                                                                        Delete
                                                                    </button>
                                                                </form>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        @else
                                            <p class="text-gray-500 italic mt-2 ml-2">No packages yet.</p>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-gray-500 italic ml-2">No categories yet.</p>
                        @endif
                    </div>
                @empty
                    <p class="text-center text-gray-500 py-6">No insurance providers added yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-admin-layout>
