

<x-admin-layout>
<div class="min-h-screen bg-gray-50 text-gray-800 py-8 px-4">
    <div class="max-w-5xl mx-auto bg-white shadow-md rounded-2xl p-6 border border-gray-200">
        <h1 class="text-2xl font-semibold text-teal-900 mb-6 border-b border-gray-200 pb-3">Add New Service</h1>

        
        <div class="space-y-6">
            
    <div class="max-w-3xl mx-auto py-10">
        


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

        <form action="{{ route('admin.services.store') }}" method="POST">
            @csrf

            <!-- Category -->
            <div class="mb-6">
                <label class="block font-semibold mb-2">Service Category <span class="text-red-500">*</span></label>
                <input list="categories" name="category" value="{{ old('category') }}" required
                       class="w-full border-gray-300 rounded-lg px-3 py-2">
                <datalist id="categories">
                    @foreach ($categories as $category)
                        <option value="{{ $category }}"></option>
                    @endforeach
                </datalist>
            </div>

            <!-- Name -->
            <div class="mb-6">
                <label class="block font-semibold mb-2">Service Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" required class="w-full border-gray-300 rounded-lg px-3 py-2"
                       placeholder="e.g. Brain MRI">
            </div>

            <!-- Description -->
            <div class="mb-6">
                <label class="block font-semibold mb-2">Description</label>
                <textarea name="description" class="w-full border-gray-300 rounded-lg px-3 py-2" rows="3"></textarea>
            </div>

            <!-- Price -->
            <div class="mb-6">
                <label class="block font-semibold mb-2">Price (RWF)</label>
                <input type="number" step="0.01" name="price" required
                       class="w-full border-gray-300 rounded-lg px-3 py-2">
            </div>

            <button type="submit" class="bg-blue-600 text-white px-6 py-3 rounded-lg">Save Service</button>

            <a href="{{ route('admin.services.index') }}" 
                   class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-3 rounded-lg font-semibold text-center transition duration-200">
                    Back to Services List
            </a>
        </form>
    </div>


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
