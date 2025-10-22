@extends('reports.layout')

@section('title', 'Payment Collection Report')

@section('content')
<h2 class="report-title">PAYMENT COLLECTION REPORT</h2>

<div class="report-meta">
    <strong>Report Generated:</strong> {{ date('F d, Y \a\t h:i A') }}<br>
    @if($dateFrom || $dateTo)
        <strong>Period:</strong>
        {{ $dateFrom ? date('M d, Y', strtotime($dateFrom)) : 'Start' }} -
        {{ $dateTo ? date('M d, Y', strtotime($dateTo)) : 'End' }}<br>
    @endif
    @if($status)
        <strong>Status Filter:</strong> {{ ucfirst($status) }}<br>
    @endif
    <strong>Total Payments:</strong> {{ $payments->count() }}
</div>

<div class="summary-box">
    <h3>Collection Summary</h3>
    <div class="summary-grid">
        <div class="summary-item">
            <div class="summary-label">Total Collected</div>
            <div class="summary-value text-green">KES {{ number_format($payments->sum('amount'), 2) }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">M-PESA</div>
            <div class="summary-value">KES {{ number_format($payments->where('payment_method', 'mpesa')->sum('amount'), 2) }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Cash</div>
            <div class="summary-value">KES {{ number_format($payments->where('payment_method', 'cash')->sum('amount'), 2) }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Bank Transfer</div>
            <div class="summary-value">KES {{ number_format($payments->where('payment_method', 'bank_transfer')->sum('amount'), 2) }}</div>
        </div>
    </div>
</div>

<table>
    <thead>
        <tr>
            <th>Transaction ID</th>
            <th>Date</th>
            <th>Customer</th>
            <th>Loan #</th>
            <th class="text-right">Amount</th>
            <th>Method</th>
            <th>Receipt #</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($payments as $payment)
        <tr>
            <td style="font-size: 8pt;">{{ $payment->transaction_id }}</td>
            <td>{{ $payment->payment_date->format('Y-m-d') }}</td>
            <td>{{ $payment->customer->name }}</td>
            <td class="font-bold">{{ $payment->loan->loan_number }}</td>
            <td class="text-right text-green font-bold">{{ number_format($payment->amount, 2) }}</td>
            <td style="font-size: 8pt;">{{ strtoupper($payment->payment_method) }}</td>
            <td style="font-size: 8pt;">{{ $payment->mpesa_receipt_number ?? '-' }}</td>
            <td>
                <span class="status-badge status-{{ $payment->status }}">
                    {{ ucfirst($payment->status) }}
                </span>
            </td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr style="background-color: #f3f4f6; font-weight: bold;">
            <td colspan="4" class="text-right" style="padding: 10px;">TOTAL:</td>
            <td class="text-right text-green" style="font-size: 12pt;">KES {{ number_format($payments->sum('amount'), 2) }}</td>
            <td colspan="3"></td>
        </tr>
    </tfoot>
</table>

@if($payments->isEmpty())
<div style="text-align: center; padding: 40px; color: #9ca3af;">
    <p>No payments found for the selected criteria.</p>
</div>
@endif

</body>
</html>
@endsection
