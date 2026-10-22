{{-- Admin Create (Provider / Category / Package) --}}
<x-admin-layout>
    <div class="max-w-3xl mx-auto px-4 py-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-6">
            @if(request('type') === 'provider') Add New Provider
            @elseif(request('type') === 'category') Add New Category
            @else Add New Package @endif
        </h2>

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

        <form action="{{ route('admin.insurances.store', ['type' => request('type')]) }}" method="POST"
              class="bg-white p-6 rounded-lg shadow border border-gray-200">
            @csrf

            @if(request('type') === 'provider')
                {{-- Provider Form --}}
                <div class="mb-4">
                    <label class="block text-gray-700 mb-1">Provider Name</label>
                    <input type="text" name="name" class="w-full border-gray-300 rounded-md p-2"
                           placeholder="e.g. RSSB" required>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="3" class="w-full border-gray-300 rounded-md p-2"
                              placeholder="Describe this provider"></textarea>
                </div>

            @elseif(request('type') === 'category')
                {{-- Category Form --}}
                <div class="mb-4">
                    <label class="block text-gray-700 mb-1">Select Provider</label>
                    <select name="insurance_provider_id" class="w-full border-gray-300 rounded-md p-2" required>
                        @foreach($providers as $provider)
                            <option value="{{ $provider->id }}">{{ $provider->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 mb-1">Category Name</label>
                    <input type="text" name="name" class="w-full border-gray-300 rounded-md p-2"
                           placeholder="e.g. RAMA or CBHI" required>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="3" class="w-full border-gray-300 rounded-md p-2"
                              placeholder="Describe this category"></textarea>
                </div>

            @else
                {{-- Package Form --}}
                <div class="mb-4">
                    <label class="block text-gray-700 mb-1">Select Category</label>
                    <select name="insurance_category_id" class="w-full border-gray-300 rounded-md p-2" required>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->provider->name }} → {{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 mb-1">Package Name</label>
                    <input type="text" name="package_name" class="w-full border-gray-300 rounded-md p-2"
                           placeholder="e.g. CBHI Full Coverage">
                </div>
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-gray-700 mb-1">Patient Cover (%)</label>
                        <input type="number" name="patient_cover" min="0" max="100"
                               class="w-full border-gray-300 rounded-md p-2" required>
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-1">Insurance Cover (%)</label>
                        <input type="number" name="insurance_cover" min="0" max="100"
                               class="w-full border-gray-300 rounded-md p-2" required>
                    </div>
                </div>
            @endif

            {{-- Buttons --}}
            <div class="flex justify-end">
                <a href="{{ route('admin.insurances.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded mr-2">
                    Cancel
                </a>
                <button type="submit" class="bg-teal-600 hover:bg-teal-700 text-white px-4 py-2 rounded shadow">
                    Save
                </button>
            </div>
        </form>
    </div>
</x-admin-layout>
