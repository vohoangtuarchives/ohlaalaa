<?php

namespace App\Exports\Users\Reports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SaleReportSummariesTop10RPEarningByMerchantBonus implements FromCollection, WithHeadings
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
            "customer_name" => "Customer Name",
            "ranking" => "Rank",
            "created_at" => "Joined Date",
            "l1_count" => "Total Direct Downline (L1)",
            "total_reward_point" => "RP Earning",
            "reward_point" => "Total RP",

        ];
    }
}
