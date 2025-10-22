@extends('reports.layout')

@section('title', 'Loan Portfolio Report')

@section('content')
<h2 class="report-title">LOAN PORTFOLIO REPORT</h2>

<div class="report-meta">
    <strong>Report Generated:</strong> {{ date('F d, Y \a\t h:i A') }}<br>
    @if($status)
        <strong>Filter:</strong> {{ ucfirst($status) }} Loans Only<br>
    @endif
    <strong>Total Loans:</strong> {{ $loans->count() }}
</div>

<div class="summary-box">
    <h3>Portfolio Summary</h3>
    <div class="summary-grid">
        <div class="summary-item">
            <div class="summary-label">Total Principal</div>
            <div class="summary-value">KES {{ number_format($loans->sum('principal_amount'), 2) }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Total Amount</div>
            <div class="summary-value text-blue">KES {{ number_format($loans->sum('total_amount'), 2) }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Amount Paid</div>
            <div class="summary-value text-green">KES {{ number_format($loans->sum('amount_paid'), 2) }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Outstanding</div>
            <div class="summary-value text-red">KES {{ number_format($loans->sum('balance'), 2) }}</div>
        </div>
    </div>
</div>

<table>
    <thead>
        <tr>
            <th>Loan #</th>
            <th>Customer</th>
            <th>Phone</th>
            <th class="text-right">Principal</th>
            <th>Rate</th>
            <th class="text-right">Total</th>
            <th class="text-right">Paid</th>
            <th class="text-right">Balance</th>
            <th>Due Date</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($loans as $loan)
        <tr>
            <td class="font-bold">{{ $loan->loan_number }}</td>
            <td>{{ $loan->customer->name }}</td>
            <td>{{ $loan->customer->phone }}</td>
            <td class="text-right">{{ number_format($loan->principal_amount, 0) }}</td>
            <td class="text-center">{{ $loan->interest_rate }}%</td>
            <td class="text-right">{{ number_format($loan->total_amount, 2) }}</td>
            <td class="text-right text-green">{{ number_format($loan->amount_paid, 2) }}</td>
            <td class="text-right {{ $loan->balance > 0 ? 'text-red' : '' }}">
                {{ number_format($loan->balance, 2) }}
            </td>
            <td style="font-size: 8pt;">
                @if($loan->due_date)
                    {{ $loan->due_date->format('Y-m-d') }}
                    @if($loan->isOverdue())
                        <br><span class="text-red">({{ $loan->daysOverdue() }}d overdue)</span>
                    @endif
                @else
                    -
                @endif
            </td>
            <td>
                <span class="status-badge status-{{ $loan->status }}">
                    {{ ucfirst($loan->status) }}
                </span>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

@if($loans->isEmpty())
<div style="text-align: center; padding: 40px; color: #9ca3af;">
    <p>No loans found{{ $status ? ' with status: ' . ucfirst($status) : '' }}.</p>
</div>
@endif

</body>
</html>
@endsection
