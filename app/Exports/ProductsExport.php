<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductsExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Product::latest()->get();
    }

    /**
     * Define headings for the export
     */
    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Category',
            'Description',
            'Price (KES)',
            'Original Price (KES)',
            'Discount (%)',
            'Stock Quantity',
            'Stock Value (KES)',
            'Availability',
            'Created At',
            'Updated At',
        ];
    }

    /**
     * Map data for each row
     */
    public function map($product): array
    {
        $stockValue = $product->price * $product->stock_quantity;

        return [
            $product->id,
            $product->name,
            $product->category,
            $product->description,
            number_format($product->price, 2),
            number_format($product->original_price, 2),
            $product->discount_percentage,
            $product->stock_quantity,
            number_format($stockValue, 2),
            $product->is_available ? 'Available' : 'Unavailable',
            $product->created_at->format('Y-m-d H:i'),
            $product->updated_at->format('Y-m-d H:i'),
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
