@php
    $user = auth()->user();

    // Not logged in
    if (!$user) {
        echo "<script>
            alert('You must login first.');
            window.location.href = '".route('homepage')."';
        </script>";
        exit;
    }

    // Logged in but role not allowed
    if ($user->role !== 'admin' && $user->role !== 'staff' && $user->role !== 'cashier') {
        echo "<script>
            alert('Authorized access only.');
            window.location.href = '".route('homepage')."';
        </script>";
        exit;
    }
@endphp



<!DOCTYPE html>
<head lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - EHRL</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

<style>
    @page {
        size: A4;
        margin: 8mm;
    }
    

    @media print {
        html, body {
            width: 210mm;
            height: 297mm;
            margin: 0;
            padding: 0;
            overflow: hidden;
        }

        /* Strict A4 enforcement - full width and height with flexbox */
        .max-w-4xl {
            max-width: 210mm !important;
            width: 210mm !important;
            max-height: 297mm !important;
            height: 297mm !important;
            margin: 0 !important;
            padding: 8mm !important;
            overflow: hidden;
            display: flex !important;
            flex-direction: column !important;
            box-sizing: border-box;
        }

        /* Ensure content fills available space and footer sticks to bottom */
        .relative.z-10 {
            flex: 1;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            font-size: 12px;
        }

        /* Reduce padding and spacing to fit content */
        .bg-white.rounded-2xl.shadow.p-6 {
            padding: 12px !important;
            margin-bottom: 8px !important;
        }

        .gap-x-8 {
            gap: 12px !important;
        }

        .gap-y-3 {
            gap-y: 2px !important;
        }

        /* Scale down signature section to ensure it fits */
        .grid.grid-cols-3 {
            margin-top: 4px !important;
            padding-top: 2px !important;
            gap: 8px !important;
        }

        .grid.grid-cols-3 > div {
            margin: 0;
        }

        .grid.grid-cols-3 div p {
            font-size: 11px !important;
            margin: 0 !important;
            padding: 0;
        }

        .grid.grid-cols-3 div .border-t {
            margin-top: 3px !important;
            
        }

        html {
                margin: 0;
                padding: 0;
            }
            body {
    margin-bottom: 80px !important; /* space for footer */
  }

  * {
                box-sizing: border-box;
            }

        /* Ensure footer sticks to bottom and fills remaining space */
        footer {
    position: fixed !important;
    bottom: 0 !important;
    left: 0 !important;
    width: 100% !important;
    border-top: 1px solid #d1d5db !important;
    padding-top: 10px !important;
    padding-bottom: 15px !important;
    background: transparent !important;
    z-index: 999 !important;
  }
        footer p {
            margin: 0 !important;
            padding: 0 !important;
        }

        /* Adjust table spacing for better fit */
        table {
            font-size: 11px !important;
        }

        table th,
        table td {
            padding: 3px 4px !important;
        }

        h1 {
            margin: 0 !important;
            font-size: 16px !important;
        }

        h2 {
            margin: 0 0 4px 0 !important;
            font-size: 12px !important;
        }

        h3 {
            margin: 0 0 4px 0 !important;
            font-size: 11px !important;
        }

        p {
            margin: 0 !important;
            line-height: 1.2 !important;
        }

        /* Eliminate all unnecessary margins */
        .border-b {
            margin-bottom: 4px !important;
        }

        .border-t {
            margin-top: 2px !important;
        }
        
    }
</style>
</head>
<body class="bg-gray-50">
    
    {{ $slot }}

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>
