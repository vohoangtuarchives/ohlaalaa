<?php

namespace App\Exports\Orders\Reports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class KOLConsumerBonus implements FromCollection, WithHeadings
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
            "order_id" => "Order Id",
            "order_number" => "Order Number",
            "consumer_id" => "L1 Id",
            "consumer_name" => "L1 Name",
            "consumer_email" => "L1 Email",
            "l1_id" => "Beneficiary Id",
            "l1_name" => "Beneficiary Name",
            "l1_email" => "Beneficiary Email",
            "l1_ranking" => "Beneficiary Rank",
            "l1_bankname" => "Beneficiary Bank Name",
            "l1_bankaccount" => "Beneficiary Bank Account Name",
            "l1_bankbumber" => "Beneficiary Bank Account Number",
            "l1_bankaddress" => "Beneficiary Bank Address",
            "total_sales" => "Total Sales",
            "bonus" => "Bonus",
        ];
    }
}
