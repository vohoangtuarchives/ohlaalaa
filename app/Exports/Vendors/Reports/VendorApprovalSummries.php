<?php

namespace App\Exports\Vendors\Reports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class VendorApprovalSummries implements FromCollection, WithHeadings
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
            "province" => "City",
            "total_vendor" => "Total Vendor",
            "vendor_per_day" => "Vendor Per Day",
        ];
    }
}
