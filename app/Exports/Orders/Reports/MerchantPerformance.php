<?php

namespace App\Exports\Orders\Reports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MerchantPerformance implements FromCollection, WithHeadings
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
            "created_at" => "Order Date",
            "completed_at" => "Completed At",
            "order_number" => "Order Number",
            "shop_name" => "Shop Name",
            "consumer_name" => "Customer Name",
            "consumer_city" => "Customer City",
            "category" => "Category",
            "product" => "Product",
            "quantity" => "Qty",
            "amount" => "Amount",
            "price_shopping_point_amount" => "Shopping Point Amount",
            "total_amount" => "Total Amount",
        ];
    }
}
