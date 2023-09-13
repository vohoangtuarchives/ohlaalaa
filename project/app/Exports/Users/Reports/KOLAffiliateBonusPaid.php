<?php

namespace App\Exports\Users\Reports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class KOLAffiliateBonusPaid implements FromCollection, WithHeadings
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
            "mpr_id" => "Id",
            "payment_number" => "Payment Number",
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
            "package_price" => "Package Price",
            "bonus" => "Bonus",
            "approved_at" => "Approved At",
            "created_at" => "Process Paid At",
        ];
    }
}
