<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    @vite('resources/css/main.css')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script type="module" src="{{ Vite::asset('resources/js/custom.js') }}"></script>

    {{-- <style>
        body { font-family: sans-serif; }
        .dashboard-container { max-width: 800px; margin: 50px auto; padding: 20px; border: 1px solid #ccc; border-radius: 8px; }
        .dashboard-header { display: flex; justify-content: space-between; align-items: center; }
        .dashboard-header h1 { margin: 0; }
        .logout-link { color: #d9534f; text-decoration: none; }
    </style> --}}
</head>
<body>
    <x-sidebar/>
    <x-top-bar />
    <x-admin-panel :orders="$orders" :total-orders="$totalOrders"/>
    <x-footer/>
    
    {{-- <div class="dashboard-container">
        <div class="dashboard-header">
            <h1>Admin Dashboard</h1>
            <a class="logout-link" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                Logout
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                @csrf
            </form>
        </div>
        <hr>
        <p>Welcome, {{ Auth::user()->name }}! You have administrative privileges.</p>
        <p>This is your custom dashboard. You can add links to manage users, posts, or other parts of your application here.</p>
        <p>Your roles: **{{ implode(', ', Auth::user()->getRoleNames()->toArray()) }}**</p>
    </div> --}}

</body>
</html>