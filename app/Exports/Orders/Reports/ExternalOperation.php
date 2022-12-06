<?php

namespace App\Exports\Orders\Reports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ExternalOperation implements FromCollection, WithHeadings
{
    private $data = null;
    public function __construct($d, $from)
    {
        $this->data = $d;
        $this->from = $from;

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
        $from =' From ' . $this->from;
        if ($this->from < date('Y')) {
            $from .= ' - ' . date('Y');
        }
        return [
            "user_total" => "Total User".$from,
            "shop_total" => "Total Shop",
            "shop_date" => "Shop".$from,
            "order_total" => "Total Order".$from,
            "order_complete" => "Order Complete".$from,
            "order_declined" => "Order Declined".$from,
            "order_delivery" => "Order Delivery".$from,
            "order_pending" => "Order Pending".$from,
            "total_amount" => "Total Amount Order Completed".$from,
        ];
    }
}
// 'user_total',
// 'shop_total',
// 'shop_date',
// 'order_total',
// 'order_complete',
// 'order_declined',
// 'order_delivery',
// 'order_pending',
// 'total_amount'
