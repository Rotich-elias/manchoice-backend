<?php

namespace App\Exports;

use App\Models\Customer;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CustomersExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Customer::with(['loans'])->get();
    }

    /**
     * Define headings for the export
     */
    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Phone',
            'Email',
            'ID Number',
            'Address',
            'Credit Limit (KES)',
            'Total Borrowed (KES)',
            'Total Paid (KES)',
            'Outstanding Balance (KES)',
            'Loan Count',
            'Status',
            'Motorcycle Plate',
            'Next of Kin',
            'Next of Kin Phone',
            'Guarantor',
            'Guarantor Phone',
            'Created At',
        ];
    }

    /**
     * Map data for each row
     */
    public function map($customer): array
    {
        return [
            $customer->id,
            $customer->name,
            $customer->phone,
            $customer->email,
            $customer->id_number,
            $customer->address,
            number_format($customer->credit_limit, 2),
            number_format($customer->total_borrowed, 2),
            number_format($customer->total_paid, 2),
            number_format($customer->outstandingBalance(), 2),
            $customer->loan_count,
            ucfirst($customer->status),
            $customer->motorcycle_number_plate,
            $customer->next_of_kin_name,
            $customer->next_of_kin_phone,
            $customer->guarantor_name,
            $customer->guarantor_phone,
            $customer->created_at->format('Y-m-d H:i'),
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
