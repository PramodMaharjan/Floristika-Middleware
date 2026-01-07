<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    {{-- Favicon --}}
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v=2" type="image/x-icon">

    {{-- Vite --}}
    {{-- @vite('resources/css/main.css')
    @vite('resources/css/styles.css') --}}
    @vite(['resources/css/main.css', 'resources/css/styles.css'])

    {{-- Bootstrap --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Data Tables --}}
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.css">
    
    {{-- Datepicker --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    
    {{-- Icon --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    @stack('head')
</head>
<body>
    @include('admin.partials.sidebar')
    @include('admin.partials.top-bar')
        @yield('content')
        {{-- Global toast --}}
            @include('admin.partials.toast')
        {{-- Global modals --}}
        <x-modals.error-modal />
        <x-modals.lineclear-modal />
        <x-modals.lalamove-modal />
        <x-modals.detrack-modal />
        {{-- Global spinner --}}
        @include('admin.partials.overlay-spinner')

    @include('admin.partials.footer')

    {{-- jQuery --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    {{-- Bootstrap --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"></script>
    
    {{-- Datepicker --}}
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    
    {{-- Data Tables --}}
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.js"></script>

    {{-- HTML to PDF --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

    {{-- Print JS --}}
    <script src="https://printjs-4de6.kxcdn.com/print.min.js"></script>
    <link rel="stylesheet" href="https://printjs-4de6.kxcdn.com/print.min.css" />

    {{-- Custom JS --}}
    @vite(['resources/js/custom.js', 'resources/js/dashboard.js', 'resources/js/order-details.js'])
    {{-- <script src="{{ Vite::asset('resources/js/custom.js') }}"></script>
    <script src="{{ Vite::asset('resources/js/dashboard.js') }}"></script>
    <script src="{{ Vite::asset('resources/js/order-details.js') }}"></script> --}}

    @stack('scripts')
</body>
</html>


