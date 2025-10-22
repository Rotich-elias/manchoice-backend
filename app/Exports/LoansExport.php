<?php

namespace App\Exports;

use App\Models\Loan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LoansExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $status;

    public function __construct($status = null)
    {
        $this->status = $status;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $query = Loan::with(['customer', 'approver']);

        if ($this->status) {
            $query->where('status', $this->status);
        }

        return $query->latest()->get();
    }

    /**
     * Define headings for the export
     */
    public function headings(): array
    {
        return [
            'Loan Number',
            'Customer Name',
            'Customer Phone',
            'Principal Amount (KES)',
            'Interest Rate (%)',
            'Total Amount (KES)',
            'Amount Paid (KES)',
            'Balance (KES)',
            'Status',
            'Disbursement Date',
            'Due Date',
            'Days Overdue',
            'Approved By',
            'Approved At',
            'Created At',
        ];
    }

    /**
     * Map data for each row
     */
    public function map($loan): array
    {
        return [
            $loan->loan_number,
            $loan->customer->name,
            $loan->customer->phone,
            number_format($loan->principal_amount, 2),
            $loan->interest_rate,
            number_format($loan->total_amount, 2),
            number_format($loan->amount_paid, 2),
            number_format($loan->balance, 2),
            ucfirst($loan->status),
            $loan->disbursement_date ? $loan->disbursement_date->format('Y-m-d') : '',
            $loan->due_date ? $loan->due_date->format('Y-m-d') : '',
            $loan->isOverdue() ? $loan->daysOverdue() : 0,
            $loan->approver ? $loan->approver->name : '',
            $loan->approved_at ? $loan->approved_at->format('Y-m-d H:i') : '',
            $loan->created_at->format('Y-m-d H:i'),
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
