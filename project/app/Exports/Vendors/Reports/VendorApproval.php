<?php

namespace App\Exports\Vendors\Reports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class VendorApproval implements FromCollection, WithHeadings
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
            "shop_name" => "Shop Name",
            "owner_name" => "Owner Name",
            "email" => "Email",
            "phone" => "Phone",
            "province" => "City",
            "created_at" => "Created At",
            "approved_at" => "Approved At",
            "approved_by" => "Approved By",
            "preferred" => "Preferred",
        ];
    }
}
