<?php

namespace App\Exports\Users;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class VendorExport implements FromCollection, WithHeadings
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
            "shop_name" => "Vendor Name",
            "email" => "Vendor Email",
            "phone" => "Phone",
            "TaxCode" => "Tax Code",
            "province_name" => "City",
            "preferred" => "Preferred",
            "status" => "Active",
            "ver_status" => "Verification",
            "vendor_subscription" => "Subscription",
            "reward_point" => "Reward Point",
            "shopping_point" => "Shopping Poine",
            "affilate_code" => "Affiliate Code",
            "ranking" => "Rank",
            "ranking_end_date" => "Rank End Date",
            "verified_at" => "Verified",
            "created_at" => "Created",
            "date" => "Subscription End",
        ];
    }
}
