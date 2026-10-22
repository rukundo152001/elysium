{{-- Edit Insurance (Provider / Category / Package) --}}
<x-admin-layout>
    <div class="max-w-3xl mx-auto px-4 py-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-6 capitalize">
            Edit {{ $type }}
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

        <form action="{{ route('admin.insurances.update', ['id' => $item->id, 'type' => $type]) }}" method="POST"
              class="bg-white p-6 rounded-lg shadow border border-gray-200">
            @csrf
            @method('PUT')

            {{-- Provider Form --}}
            @if($type === 'provider')
                <div class="mb-4">
                    <label class="block text-gray-700 mb-1">Provider Name</label>
                    <input type="text" name="name" value="{{ old('name', $item->name) }}"
                           class="w-full border-gray-300 rounded-md p-2 focus:ring-teal-500 focus:border-teal-500"
                           required>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="3"
                              class="w-full border-gray-300 rounded-md p-2 focus:ring-teal-500 focus:border-teal-500"
                              placeholder="Short description">{{ old('description', $item->description) }}</textarea>
                </div>

            {{-- Category Form --}}
            @elseif($type === 'category')
                <div class="mb-4">
                    <label class="block text-gray-700 mb-1">Category Name</label>
                    <input type="text" name="name" value="{{ old('name', $item->name) }}"
                           class="w-full border-gray-300 rounded-md p-2 focus:ring-teal-500 focus:border-teal-500"
                           required>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="3"
                              class="w-full border-gray-300 rounded-md p-2 focus:ring-teal-500 focus:border-teal-500"
                              placeholder="Short description">{{ old('description', $item->description) }}</textarea>
                </div>

            {{-- Package Form --}}
            @elseif($type === 'package')
                <div class="mb-4">
                    <label class="block text-gray-700 mb-1">Package Name</label>
                    <input type="text" name="package_name" value="{{ old('package_name', $item->package_name) }}"
                           class="w-full border-gray-300 rounded-md p-2 focus:ring-teal-500 focus:border-teal-500"
                           required>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-gray-700 mb-1">Patient Cover (%)</label>
                        <input type="number" name="patient_cover"
                               value="{{ old('patient_cover', $item->patient_cover) }}"
                               min="0" max="100"
                               class="w-full border-gray-300 rounded-md p-2 focus:ring-teal-500 focus:border-teal-500"
                               required>
                    </div>

                    <div>
                        <label class="block text-gray-700 mb-1">Insurance Cover (%)</label>
                        <input type="number" name="insurance_cover"
                               value="{{ old('insurance_cover', $item->insurance_cover) }}"
                               min="0" max="100"
                               class="w-full border-gray-300 rounded-md p-2 focus:ring-teal-500 focus:border-teal-500"
                               required>
                    </div>
                </div>
            @endif

            {{-- Buttons --}}
            <div class="flex justify-end">
                <a href="{{ route('admin.insurances.index') }}"
                   class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded mr-2">
                    Cancel
                </a>
                <button type="submit"
                        class="bg-teal-600 hover:bg-teal-700 text-white px-4 py-2 rounded shadow">
                    Update
                </button>
            </div>
        </form>
    </div>
</x-admin-layout>
