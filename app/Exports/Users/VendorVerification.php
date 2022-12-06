<?php

namespace App\Exports\Users;

use App\Models\Verification;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class VendorVerification implements FromCollection, WithHeadings
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
            "name" => "Vendor Name",
            "email" => "Vendor Email",
            "text" => "Descriptions",
            "status" => "Status",
            "approved_at" => "Approved at",
        ];
    }
}
