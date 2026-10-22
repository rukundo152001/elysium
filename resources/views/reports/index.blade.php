{{--
    File: resources/views/reports/index.blade.php

    Purpose:
    - Main reports landing page for Elysium.
    - Adds a direct entry point to the Insurance Summary workflow supplied in
      elysium_insurance_review_reconciliation.zip.

    IMPORTANT INTEGRATION NOTES:
    1. The ZIP's route snippet defines this route name:
           reports.insurance-summary.index
       Make sure you copied its routes into routes/web.php.
    2. This view extends layouts.app, matching the insurance-summary views in the ZIP.
       If your project uses another layout, change the @extends line below.
    3. Existing report links vary from project to project. Add your own route names
       in the optional section at the bottom; do not invent route names in Blade.
    4. This page only provides navigation. Authentication/authorization must also
       be enforced by the routes/controllers (the ZIP puts its routes behind auth).
--}}
<x-admin-layout :title="'Insurance Reports & Analytics'">
<div class="max-w-7xl mx-auto px-4 py-8 space-y-6">
    {{-- Page heading --}}
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900">Reports</h1>
            <p class="mt-1 text-sm text-gray-600">
                Generate, review, and manage Elysium operational and financial reports.
            </p>
        </div>
    </div>

    {{-- Laravel session feedback --}}
    @if (session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-800" role="status">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800" role="alert">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800" role="alert">
            <p class="font-semibold">Please correct the following:</p>
            <ul class="mt-2 list-disc space-y-1 pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Primary report card added for the insurance-summary package. --}}
    <section class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">
        <article class="flex flex-col rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-indigo-50 text-indigo-700">
                {{-- Simple spreadsheet/report icon --}}
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path d="M7 3.75h7l4 4V20a.75.75 0 0 1-.75.75h-10.5A.75.75 0 0 1 6 20V4.5a.75.75 0 0 1 .75-.75Z" stroke-linejoin="round" />
                    <path d="M14 4v4h4M9 12h6M9 15h6M9 18h3" stroke-linecap="round" />
                </svg>
            </div>

            <h2 class="mt-4 text-lg font-semibold text-gray-900">Insurance Summary &amp; Global Bill</h2>
            <p class="mt-2 flex-1 text-sm leading-6 text-gray-600">
                Prepare the monthly MRI insurance summary, review and verify examination rows,
                save a draft to continue later, download the Excel summary, and upload or view
                the insurer's global bill for reconciliation.
            </p>

            <div class="mt-5">
                @if (\Illuminate\Support\Facades\Route::has('reports.insurance-summary.index'))
                    <a href="{{ route('reports.insurance-summary.index') }}"
                       class="inline-flex w-full items-center justify-center rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        Open Insurance Summary
                        <svg class="ml-2 h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L10.94 10 7.23 6.29a.75.75 0 1 1 1.06-1.06l4.24 4.24a.75.75 0 0 1 0 1.06l-4.24 4.24a.75.75 0 0 1-1.08 0Z" clip-rule="evenodd" />
                        </svg>
                    </a>
                @else
                    <div class="rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800">
                        Insurance Summary route is not registered yet. Copy the route group from
                        <code class="rounded bg-amber-100 px-1">routes/web.php.snippet</code>
                        in the ZIP into your application's <code class="rounded bg-amber-100 px-1">routes/web.php</code>.
                    </div>
                @endif
            </div>
        </article>

        {{-- Optional placeholder card: connect this to a real route in your app if needed. --}}
        <article class="flex flex-col rounded-xl border border-dashed border-gray-300 bg-gray-50 p-6">
            <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-gray-200 text-gray-700">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path d="M4 19.25h16M6.5 16V10m5 6V5m5 11v-8" stroke-linecap="round" />
                </svg>
            </div>
            <h2 class="mt-4 text-lg font-semibold text-gray-900">Other Reports</h2>
            <p class="mt-2 flex-1 text-sm leading-6 text-gray-600">
                Add links here for patient activity, daily collections, billing, or other reports
                that already exist in your application.
            </p>
            <p class="mt-5 text-xs text-gray-500">
                Configure your existing report routes in this view before adding buttons.
            </p>
        </article>
    </section>

    {{--
        OPTIONAL: Example for an existing report route.
        Uncomment and replace 'reports.daily-collections' with a route name that
        actually exists in your application:

        @if (\Illuminate\Support\Facades\Route::has('reports.daily-collections'))
            <a href="{{ route('reports.daily-collections') }}">Daily Collections</a>
        @endif
    --}}
</div>
</x-admin-layout>
