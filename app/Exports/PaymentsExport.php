<?php

namespace App\Exports;

use App\Models\Payment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PaymentsExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $status;
    protected $dateFrom;
    protected $dateTo;

    public function __construct($status = null, $dateFrom = null, $dateTo = null)
    {
        $this->status = $status;
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $query = Payment::with(['customer', 'loan', 'recorder']);

        if ($this->status) {
            $query->where('status', $this->status);
        }

        if ($this->dateFrom) {
            $query->whereDate('payment_date', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->whereDate('payment_date', '<=', $this->dateTo);
        }

        return $query->latest('payment_date')->get();
    }

    /**
     * Define headings for the export
     */
    public function headings(): array
    {
        return [
            'Transaction ID',
            'Customer Name',
            'Customer Phone',
            'Loan Number',
            'Amount (KES)',
            'Payment Method',
            'M-PESA Receipt',
            'Phone Number',
            'Payment Date',
            'Status',
            'Recorded By',
            'Notes',
            'Created At',
        ];
    }

    /**
     * Map data for each row
     */
    public function map($payment): array
    {
        return [
            $payment->transaction_id,
            $payment->customer->name,
            $payment->customer->phone,
            $payment->loan->loan_number,
            number_format($payment->amount, 2),
            ucfirst($payment->payment_method),
            $payment->mpesa_receipt_number,
            $payment->phone_number,
            $payment->payment_date->format('Y-m-d'),
            ucfirst($payment->status),
            $payment->recorder ? $payment->recorder->name : 'System',
            $payment->notes,
            $payment->created_at->format('Y-m-d H:i'),
        ];
    }

    /**
     * Style the worksheet
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
