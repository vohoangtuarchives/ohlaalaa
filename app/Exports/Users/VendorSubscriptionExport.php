<?php

namespace App\Exports\Users;

use App\Models\UserSubscription;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class VendorSubscriptionExport implements FromCollection, WithHeadings
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
            "email" => "Email",
            "phone" => "Phone",
            "affilate_code" => "Aff. Code",
            "title" => "Plan",
            "created_at" => "Submit Date",
            "old_end_at" => "Current End Date",
            "new_end_at" => "Renewal End Date",
            "approved_at" => "Approved At",
            "rejected_at" => "Rejected At",
            "status_caption" => "Status",
        ];
    }
}
