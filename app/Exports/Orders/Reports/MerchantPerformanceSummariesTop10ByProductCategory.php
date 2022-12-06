<?php

namespace App\Exports\Orders\Reports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MerchantPerformanceSummariesTop10ByProductCategory implements FromCollection, WithHeadings
{
    private $data = null;
    public function __construct($d)
    {
        $this->data = $d;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            "category_name" => "Category Name",
            "number_of_sales" => "Total Number of Sales",
            "total_qty" => "Total Number of Sales Quantity",
            "total_amount" => "Total Number of Sales Amount",
        ];
    }
}
