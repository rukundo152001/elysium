
<x-admin-layout>

<div class="min-h-screen bg-gray-50 text-gray-800 py-8 px-4">
    <div class="max-w-5xl mx-auto bg-white shadow-md rounded-2xl p-6 border border-gray-200">
        <div class="mb-6 border-b">
            <h1 class="text-2xl font-semibold text-teal-900 border-gray-200 pb-3">Edit Record</h1>
            <p class="text-gray-600 mt-1">Update service information</p>
        </div>
    <!-- Header -->
    
        <div class="flex items-center text-sm text-gray-600 mb-4">
            <a href="{{ route('admin.services.index') }}" class="hover:text-blue-600 transition duration-150">Services</a>
            <svg class="w-4 h-4 mx-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
            <span class="text-gray-900 font-medium">Edit Service</span>
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

    <!-- Warning if service has payments -->
    @if($paymentCount > 0)
        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">This service has existing payments</h3>
                    <div class="mt-2 text-sm text-red-700">
                        <p>This service has {{ $paymentCount }} associated payment(s). Be careful when modifying the price or name as it may affect historical records.</p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    

    <!-- Form Card -->
    <div class="bg-white rounded-lg shadow-md p-8">
        <form action="{{ route('admin.services.update', $service) }}" method="POST" id="serviceForm">
            @csrf
            @method('PUT')

            <!-- Service Name -->
            <div class="mb-6">
                <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                    Service Name <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       id="name" 
                       name="name" 
                       value="{{ old('name', $service->name) }}"
                       required
                       maxlength="255"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('name') border-red-500 @enderror"
                       placeholder="e.g., Birth Certificate, ID Card Replacement">
                
            </div>

            <!-- Category -->
            <div class="mb-6">
                <label for="category" class="block text-sm font-semibold text-gray-700 mb-2">
                    Category <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       id="category" 
                       name="category" 
                       value="{{ old('category', $service->category) }}"
                       required
                       maxlength="100"
                       list="categoryList"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('category') border-red-500 @enderror"
                       placeholder="e.g., Civil Registration, Legal Services">
                <datalist id="categoryList">
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}">
                    @endforeach
                </datalist>
                <p class="mt-2 text-sm text-gray-500">Start typing to see existing categories or create a new one</p>
            </div>

            <!-- Price -->
            <div class="mb-6">
                <label for="price" class="block text-sm font-semibold text-gray-700 mb-2">
                    Price (RWF) <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <input type="number" 
                           id="price" 
                           name="price" 
                           value="{{ old('price', $service->price) }}"
                           required
                           min="0"
                           step="0.01"
                           max="99999999.99"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('price') border-red-500 @enderror"
                           placeholder="0.00">
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                        <span class="text-gray-500 sm:text-sm">RWF</span>
                    </div>
                </div>
            </div>

            <!-- Description -->
            <div class="mb-6">
                <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">
                    Description
                </label>
                <textarea id="description" 
                          name="description" 
                          rows="4"
                          maxlength="1000"
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('description') border-red-500 @enderror"
                          placeholder="Provide a detailed description of the service...">{{ old('description', $service->description) }}</textarea>
                <div class="flex justify-between mt-2">
                    <p class="text-sm text-gray-500">Optional: Add details about what this service includes</p>
                    <p class="text-sm text-gray-500"><span id="charCount">0</span>/1000</p>
                </div>
            </div>

            <!-- Is Active -->
            <div class="mb-8">
                <div class="flex items-center">
                    <input type="checkbox" 
                           id="is_active" 
                           name="is_active" 
                           value="1"
                           {{ old('is_active', $service->is_active) ? 'checked' : '' }}
                           class="h-5 w-5 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="is_active" class="ml-3 block text-sm font-medium text-gray-700">
                        Service is active and available for customers
                    </label>
                </div>
                <p class="mt-2 ml-8 text-sm text-gray-500">Inactive services won't be visible to customers</p>
            </div>

            <!-- Tips Section -->
            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6 rounded">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800">Tips for updating services</h3>
                        <div class="mt-2 text-sm text-blue-700">
                            <ul class="list-disc list-inside space-y-1">
                                <li>Price changes will only affect new payments, not existing ones</li>
                                <li>Consider deactivating instead of deleting services with payment history</li>
                                <li>Keep service names consistent for better reporting</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-4">
                <button type="submit" 
                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition duration-200 shadow-md hover:shadow-lg">
                    Update Service
                </button>
                <a href="{{ route('admin.services.index') }}" 
                   class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-3 rounded-lg font-semibold text-center transition duration-200">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<script>
    // Character counter for description
    const descriptionField = document.getElementById('description');
    const charCount = document.getElementById('charCount');
    
    descriptionField.addEventListener('input', function() {
        charCount.textContent = this.value.length;
    });
    
    // Initialize character count
    charCount.textContent = descriptionField.value.length;

    // Form validation
    document.getElementById('serviceForm').addEventListener('submit', function(e) {
        const name = document.getElementById('name').value.trim();
        const category = document.getElementById('category').value.trim();
        const price = document.getElementById('price').value;

        if (!name || !category || !price) {
            e.preventDefault();
            alert('Please fill in all required fields.');
            return false;
        }

        if (parseFloat(price) < 0) {
            e.preventDefault();
            alert('Price cannot be negative.');
            return false;
        }
    });
</script>
        
</div>


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


</x-admin-layout>