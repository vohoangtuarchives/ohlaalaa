<?php

namespace App\Exports\Orders\Reports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class RefundDetail implements FromCollection, WithHeadings
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
            "order_number" => "Order Number",
            "pay_amount2" => "Order Amount",
            "shipping_cost" => "Shipping Cost",
            "shop_name" => "Shop Name",
            "email" => "Shop Email",
            "refund_to" => "Refund To",
            "refund_by_name" => "Refund By",
            "refund_date" => "Refund Date",
            "refund_bank" => "Refund Bank",
            "refund_amount" => "Refund Amount",
            "refund_note" => "Refund Reason",
        ];
    }
}
