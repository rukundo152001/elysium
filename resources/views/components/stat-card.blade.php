<!-- Reusable stat card component -->
<div class="bg-gradient-to-br {{ $gradient ?? 'from-blue-500 to-blue-600' }} rounded-lg shadow-lg p-6 text-white">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-blue-100 text-sm">{{ $label }}</p>
            <p class="text-xl font-bold mt-2">{{ $value }}</p>
            @if(isset($subtitle))
                <p class="text-blue-100 text-xs mt-2">{{ $subtitle }}</p>
            @endif
        </div>
        <div class="w-12 h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
            {!! $icon !!}
        </div>
    </div>
</div>
