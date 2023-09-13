<?php

namespace App\Exports\Users\MemberPackageRegisters;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MemberPackageRegisterExcel implements FromCollection, WithHeadings
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
            "user_id" => "User Id",
            "user_name" => "Name",
            "email" => "Email",
            "phone" => "Phone",
            "affilate_code" => "Affiliate Code",
            "id" => "Register Id",
            "package_config_id" => "Package Id",
            "package_name" => "Package Name",
            "created_at" => "Submit Date",
            "package_old_end_at" => "Current End Date",
            "package_new_end_at" => "Renewal End Date",
            "approval_status" => "Approval Status",
            "checked_tnc" => "T&C Checked",
            "approval_at" => "Approved At",
            "rejected_at" => "Rejected At",
            "payment_status" => "Payment Status",
            "rejected_by" => "Rejected By",
            "approved_by" => "Approved By",
            "payment_number" => "Payment Number",
        ];
    }
}
