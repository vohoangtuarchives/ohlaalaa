<?php

namespace App\Exports\Users\Reports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SaleReport implements FromCollection, WithHeadings
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
            "customer_name" => "Customer Name",
            "customer_email" => "Email",
            "ranking" => "Rank",
            "created_at" => "Joined Date",
            "l1_count" => "Total Direct Downline (L1)",
            "number_of_sales" => "Total Sales Order",
            "total_qty" => "Total Sales Quantity",
            "total_amount" => "Total Sales Amount",
            "reward_point" => "Total RP",
            "shopping_point" => "Total SP",
        ];
    }
}
