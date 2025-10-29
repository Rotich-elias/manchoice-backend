<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard') - Man's Choice Enterprise</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="w-full bg-blue-600 text-white shadow-lg">
        <div class="w-full px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between py-4">
                <div class="text-xl font-bold">Man's Choice Enterprise</div>

                <!-- Mobile menu button -->
                <button id="mobile-menu-button" class="md:hidden inline-flex items-center justify-center p-2 rounded-md hover:bg-blue-700 focus:outline-none">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path id="menu-icon" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        <path id="close-icon" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>

                <!-- Desktop menu -->
                <div class="hidden md:flex items-center space-x-4 lg:space-x-6 overflow-x-auto">
                    <a href="/admin" class="hover:text-blue-200 whitespace-nowrap">Dashboard</a>
                    <a href="/admin/customers" class="hover:text-blue-200 whitespace-nowrap">Customers</a>
                    <a href="/admin/loans" class="hover:text-blue-200 whitespace-nowrap">Loans</a>
                    <a href="/admin/payments" class="hover:text-blue-200 whitespace-nowrap">Payments</a>
                    <a href="/admin/registration-fees" class="hover:text-blue-200 relative whitespace-nowrap">
                        Reg. Fees
                        @php
                            $pendingFees = \App\Models\RegistrationFee::where('status', 'pending')->count();
                        @endphp
                        @if($pendingFees > 0)
                            <span class="absolute -top-2 -right-2 bg-orange-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                                {{ $pendingFees }}
                            </span>
                        @endif
                    </a>
                    <a href="/admin/deposits" class="hover:text-blue-200 relative whitespace-nowrap">
                        Deposits
                        @php
                            $pendingDeposits = \App\Models\Deposit::where('status', 'pending')->where('type', 'loan_deposit')->count();
                        @endphp
                        @if($pendingDeposits > 0)
                            <span class="absolute -top-2 -right-2 bg-orange-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                                {{ $pendingDeposits }}
                            </span>
                        @endif
                    </a>
                    <a href="/admin/products" class="hover:text-blue-200 whitespace-nowrap">Products</a>
                    <a href="/admin/part-requests" class="hover:text-blue-200 whitespace-nowrap">Parts</a>
                    <a href="/admin/support-tickets" class="hover:text-blue-200 relative whitespace-nowrap">
                        Support
                        @php
                            $openTickets = \App\Models\SupportTicket::where('status', 'open')->count();
                        @endphp
                        @if($openTickets > 0)
                            <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                                {{ $openTickets }}
                            </span>
                        @endif
                    </a>
                    @if(auth()->user()->role === 'super_admin')
                        <a href="/admin/users" class="hover:text-blue-200 whitespace-nowrap">Staff</a>
                    @endif
                    <form method="POST" action="/admin/logout" class="inline">
                        @csrf
                        <button type="submit" class="hover:text-blue-200 whitespace-nowrap">Logout</button>
                    </form>
                </div>
            </div>

            <!-- Mobile menu -->
            <div id="mobile-menu" class="hidden md:hidden pb-4">
                <div class="flex flex-col space-y-3">
                    <a href="/admin" class="hover:bg-blue-700 px-3 py-2 rounded-md">Dashboard</a>
                    <a href="/admin/customers" class="hover:bg-blue-700 px-3 py-2 rounded-md">Customers</a>
                    <a href="/admin/loans" class="hover:bg-blue-700 px-3 py-2 rounded-md">Loans</a>
                    <a href="/admin/payments" class="hover:bg-blue-700 px-3 py-2 rounded-md">Payments</a>
                    <a href="/admin/registration-fees" class="hover:bg-blue-700 px-3 py-2 rounded-md flex items-center justify-between">
                        <span>Registration Fees</span>
                        @php
                            $pendingFees = \App\Models\RegistrationFee::where('status', 'pending')->count();
                        @endphp
                        @if($pendingFees > 0)
                            <span class="bg-orange-500 text-white text-xs rounded-full px-2 py-1">
                                {{ $pendingFees }}
                            </span>
                        @endif
                    </a>
                    <a href="/admin/deposits" class="hover:bg-blue-700 px-3 py-2 rounded-md flex items-center justify-between">
                        <span>Verify Deposits</span>
                        @php
                            $pendingDeposits = \App\Models\Deposit::where('status', 'pending')->where('type', 'loan_deposit')->count();
                        @endphp
                        @if($pendingDeposits > 0)
                            <span class="bg-orange-500 text-white text-xs rounded-full px-2 py-1">
                                {{ $pendingDeposits }}
                            </span>
                        @endif
                    </a>
                    <a href="/admin/products" class="hover:bg-blue-700 px-3 py-2 rounded-md">Products</a>
                    <a href="/admin/part-requests" class="hover:bg-blue-700 px-3 py-2 rounded-md">Part Requests</a>
                    <a href="/admin/support-tickets" class="hover:bg-blue-700 px-3 py-2 rounded-md flex items-center justify-between">
                        <span>Support Tickets</span>
                        @php
                            $openTickets = \App\Models\SupportTicket::where('status', 'open')->count();
                        @endphp
                        @if($openTickets > 0)
                            <span class="bg-red-500 text-white text-xs rounded-full px-2 py-1">
                                {{ $openTickets }}
                            </span>
                        @endif
                    </a>
                    @if(auth()->user()->role === 'super_admin')
                        <a href="/admin/users" class="hover:bg-blue-700 px-3 py-2 rounded-md">Staff Users</a>
                    @endif
                    <form method="POST" action="/admin/logout" class="w-full">
                        @csrf
                        <button type="submit" class="w-full text-left hover:bg-blue-700 px-3 py-2 rounded-md">Logout</button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <script>
        // Mobile menu toggle
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');
        const menuIcon = document.getElementById('menu-icon');
        const closeIcon = document.getElementById('close-icon');

        mobileMenuButton.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
            menuIcon.classList.toggle('hidden');
            closeIcon.classList.toggle('hidden');
        });
    </script>

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
