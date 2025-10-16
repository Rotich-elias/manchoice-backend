<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard') - Man's Choice Enterprise</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-blue-600 text-white shadow-lg">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between py-4">
                <div class="text-xl font-bold">Man's Choice Enterprise</div>
                <div class="flex items-center space-x-6">
                    <a href="/admin" class="hover:text-blue-200">Dashboard</a>
                    <a href="/admin/customers" class="hover:text-blue-200">Customers</a>
                    <a href="/admin/loans" class="hover:text-blue-200">Loans</a>
                    <a href="/admin/payments" class="hover:text-blue-200">Payments</a>
                    <a href="/admin/products" class="hover:text-blue-200">Products</a>
                    <form method="POST" action="/admin/logout" class="inline">
                        @csrf
                        <button type="submit" class="hover:text-blue-200">Logout</button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-8">
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </div>
</body>
</html>
