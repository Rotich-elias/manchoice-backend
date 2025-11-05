@extends('admin.layout')

@section('title', 'Dashboard')

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-800">Dashboard Overview</h1>
    <p class="text-gray-600 mt-1">Monitor your business performance and key metrics</p>
</div>

<!-- Stats Grid -->
<div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-8">
    <div class="bg-gradient-to-br from-blue-500 to-blue-600 p-6 rounded-lg shadow-lg text-white">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-blue-100 text-sm mb-1">Total Customers</div>
                <div class="text-3xl font-bold">{{ $stats['customers'] }}</div>
            </div>
            <div class="bg-white bg-opacity-20 p-3 rounded-lg">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
            </div>
        </div>
    </div>
    <div class="bg-gradient-to-br from-green-500 to-green-600 p-6 rounded-lg shadow-lg text-white">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-green-100 text-sm mb-1">Total Loans</div>
                <div class="text-3xl font-bold">{{ $stats['loans'] }}</div>
            </div>
            <div class="bg-white bg-opacity-20 p-3 rounded-lg">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
        </div>
    </div>
    <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 p-6 rounded-lg shadow-lg text-white">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-yellow-100 text-sm mb-1">Pending Loans</div>
                <div class="text-3xl font-bold">{{ $stats['pending_loans'] }}</div>
            </div>
            <div class="bg-white bg-opacity-20 p-3 rounded-lg">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
    </div>
    <div class="bg-gradient-to-br from-purple-500 to-purple-600 p-6 rounded-lg shadow-lg text-white">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-purple-100 text-sm mb-1">Active Loans</div>
                <div class="text-3xl font-bold">{{ $stats['active_loans'] }}</div>
            </div>
            <div class="bg-white bg-opacity-20 p-3 rounded-lg">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
    </div>
    <div class="bg-gradient-to-br from-red-500 to-red-600 p-6 rounded-lg shadow-lg text-white">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-red-100 text-sm mb-1">Defaulted Loans</div>
                <div class="text-3xl font-bold">{{ $stats['defaulted_loans'] }}</div>
            </div>
            <div class="bg-white bg-opacity-20 p-3 rounded-lg">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- Financial Stats -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white p-6 rounded-lg shadow-lg border-l-4 border-green-500">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-gray-500 text-sm mb-1">Total Borrowed</div>
                <div class="text-2xl font-bold text-gray-800">KES {{ number_format($stats['total_borrowed'], 2) }}</div>
            </div>
            <div class="bg-green-100 p-3 rounded-full">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
    </div>
    <div class="bg-white p-6 rounded-lg shadow-lg border-l-4 border-blue-500">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-gray-500 text-sm mb-1">Total Paid</div>
                <div class="text-2xl font-bold text-gray-800">KES {{ number_format($stats['total_paid'], 2) }}</div>
            </div>
            <div class="bg-blue-100 p-3 rounded-full">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 8h6m-5 0a3 3 0 110 6H9l3 3m-3-6h6m6 1a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
    </div>
    <div class="bg-white p-6 rounded-lg shadow-lg border-l-4 border-red-500">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-gray-500 text-sm mb-1">Outstanding Balance</div>
                <div class="text-2xl font-bold text-gray-800">KES {{ number_format($stats['total_borrowed'] - $stats['total_paid'], 2) }}</div>
            </div>
            <div class="bg-red-100 p-3 rounded-full">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"></path>
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- Product Stats -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
    <div class="bg-white p-6 rounded-lg shadow-lg border-l-4 border-indigo-500">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-gray-500 text-sm mb-1">Total Products</div>
                <div class="text-2xl font-bold text-gray-800">{{ $stats['products'] }}</div>
            </div>
            <div class="bg-indigo-100 p-3 rounded-full">
                <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
            </div>
        </div>
    </div>
    <div class="bg-white p-6 rounded-lg shadow-lg border-l-4 border-orange-500">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-gray-500 text-sm mb-1">Low Stock Products</div>
                <div class="text-2xl font-bold text-gray-800">{{ $stats['low_stock_products'] }}</div>
            </div>
            <div class="bg-orange-100 p-3 rounded-full">
                <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- Charts Section -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Loan Status Distribution -->
    <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-lg font-semibold mb-4 text-gray-800">Loan Status Distribution</h3>
        <div class="relative" style="height: 300px;">
            <canvas id="loanStatusChart"></canvas>
        </div>
    </div>

    <!-- Payment Methods -->
    <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-lg font-semibold mb-4 text-gray-800">Revenue by Payment Method</h3>
        <div class="relative" style="height: 300px;">
            <canvas id="paymentMethodChart"></canvas>
        </div>
    </div>
</div>

<!-- Revenue & Loans Charts -->
<div class="grid grid-cols-1 gap-6 mb-8">
    <!-- Revenue Trend -->
    <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-lg font-semibold mb-4 text-gray-800">Revenue Trend (Last 30 Days)</h3>
        <div class="relative" style="height: 300px;">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>

    <!-- Loans Disbursed -->
    <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-lg font-semibold mb-4 text-gray-800">Loans Disbursed (Last 30 Days)</h3>
        <div class="relative" style="height: 300px;">
            <canvas id="loansChart"></canvas>
        </div>
    </div>
</div>

<!-- Customer Growth -->
<div class="bg-white p-6 rounded-lg shadow mb-8">
    <h3 class="text-lg font-semibold mb-4 text-gray-800">Customer Growth (Last 6 Months)</h3>
    <div class="relative" style="height: 300px;">
        <canvas id="customerGrowthChart"></canvas>
    </div>
</div>

<!-- Pending Loans -->
@if($pendingLoans->count() > 0)
<div class="bg-white p-6 rounded-lg shadow mb-8">
    <h2 class="text-xl font-bold mb-4">Pending Loan Approvals</h2>
    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Loan Number</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Purpose</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($pendingLoans as $loan)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $loan->loan_number }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $loan->customer->name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">KES {{ number_format($loan->total_amount, 2) }}</td>
                    <td class="px-6 py-4">{{ $loan->purpose }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <form action="/admin/loans/{{ $loan->id }}/approve" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">
                                Approve
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<!-- Defaulted Loans -->
@if($defaultedLoans->count() > 0)
<div class="bg-white p-6 rounded-lg shadow mb-8 border-l-4 border-red-500">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-bold text-red-700">⚠ Defaulted Loans (Missed Payments)</h2>
        <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm font-semibold">
            {{ $defaultedLoans->count() }} Loan(s)
        </span>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead class="bg-red-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Loan Number</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Customer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Total Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Balance</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Due Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @foreach($defaultedLoans as $loan)
                <tr class="hover:bg-red-50">
                    <td class="px-6 py-4 whitespace-nowrap font-semibold text-red-700">{{ $loan->loan_number }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <a href="/admin/customers/{{ $loan->customer->id }}" class="text-blue-600 hover:underline">
                            {{ $loan->customer->name }}
                        </a>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">KES {{ number_format($loan->total_amount, 2) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="text-red-600 font-bold">KES {{ number_format($loan->balance, 2) }}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($loan->due_date)
                            <span class="text-red-600">{{ $loan->due_date->format('M d, Y') }}</span>
                            <br>
                            <span class="text-xs text-gray-500">({{ $loan->due_date->diffForHumans() }})</span>
                        @else
                            N/A
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <a href="/admin/loans/{{ $loan->id }}" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded text-sm">
                            View Details
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<!-- Recent Loans -->
<div class="bg-white p-6 rounded-lg shadow">
    <h2 class="text-xl font-bold mb-4">Recent Loans</h2>
    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Loan Number</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($recentLoans as $loan)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $loan->loan_number }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $loan->customer->name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">KES {{ number_format($loan->total_amount, 2) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs rounded-full {{ $loan->status === 'approved' ? 'bg-green-100 text-green-800' : ($loan->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                            {{ ucfirst($loan->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $loan->created_at->format('M d, Y') }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    // Loan Status Distribution - Doughnut Chart
    const loanStatusCtx = document.getElementById('loanStatusChart').getContext('2d');
    new Chart(loanStatusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Pending', 'Approved', 'Active', 'Completed', 'Rejected', 'Defaulted'],
            datasets: [{
                data: [
                    {{ $loansByStatus['pending'] }},
                    {{ $loansByStatus['approved'] }},
                    {{ $loansByStatus['active'] }},
                    {{ $loansByStatus['completed'] }},
                    {{ $loansByStatus['rejected'] }},
                    {{ $loansByStatus['defaulted'] }}
                ],
                backgroundColor: [
                    '#EAB308', // Yellow - Pending
                    '#10B981', // Green - Approved
                    '#3B82F6', // Blue - Active
                    '#06B6D4', // Cyan - Completed
                    '#EF4444', // Red - Rejected
                    '#DC2626'  // Dark Red - Defaulted
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        font: {
                            size: 12
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.label || '';
                            if (label) {
                                label += ': ';
                            }
                            label += context.parsed + ' loans';
                            return label;
                        }
                    }
                }
            }
        }
    });

    // Payment Methods - Pie Chart
    const paymentMethodCtx = document.getElementById('paymentMethodChart').getContext('2d');
    const paymentMethodsData = @json($paymentMethods);
    const paymentLabels = Object.keys(paymentMethodsData);
    const paymentValues = Object.values(paymentMethodsData);

    new Chart(paymentMethodCtx, {
        type: 'pie',
        data: {
            labels: paymentLabels.length > 0 ? paymentLabels : ['No Data'],
            datasets: [{
                data: paymentValues.length > 0 ? paymentValues : [1],
                backgroundColor: [
                    '#10B981', // Green
                    '#3B82F6', // Blue
                    '#F59E0B', // Amber
                    '#8B5CF6', // Purple
                    '#EC4899'  // Pink
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        font: {
                            size: 12
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.label || '';
                            if (label) {
                                label += ': ';
                            }
                            label += 'KES ' + context.parsed.toLocaleString();
                            return label;
                        }
                    }
                }
            }
        }
    });

    // Revenue Trend - Line Chart
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: @json($revenueDates),
            datasets: [{
                label: 'Revenue (KES)',
                data: @json($revenueData),
                borderColor: '#10B981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                pointBackgroundColor: '#10B981',
                pointBorderColor: '#fff',
                pointBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'KES ' + value.toLocaleString();
                        }
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Revenue: KES ' + context.parsed.y.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // Loans Disbursed - Bar Chart
    const loansCtx = document.getElementById('loansChart').getContext('2d');
    new Chart(loansCtx, {
        type: 'bar',
        data: {
            labels: @json($revenueDates),
            datasets: [{
                label: 'Loans Disbursed',
                data: @json($loansData),
                backgroundColor: '#3B82F6',
                borderColor: '#2563EB',
                borderWidth: 1,
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Loans: ' + context.parsed.y;
                        }
                    }
                }
            }
        }
    });

    // Customer Growth - Line Chart
    const customerGrowthCtx = document.getElementById('customerGrowthChart').getContext('2d');
    new Chart(customerGrowthCtx, {
        type: 'line',
        data: {
            labels: @json($customerMonths),
            datasets: [{
                label: 'New Customers',
                data: @json($customerGrowth),
                borderColor: '#8B5CF6',
                backgroundColor: 'rgba(139, 92, 246, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointRadius: 6,
                pointBackgroundColor: '#8B5CF6',
                pointBorderColor: '#fff',
                pointBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'New Customers: ' + context.parsed.y;
                        }
                    }
                }
            }
        }
    });
</script>
@endsection
