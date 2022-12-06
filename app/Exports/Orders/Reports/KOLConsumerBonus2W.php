<?php

namespace App\Exports\Orders\Reports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class KOLConsumerBonus2W implements FromCollection, WithHeadings
{
    private $data = null;
    public function __construct($d, $config)
    {
        $this->data = $d;
        $this->config = $config;
       
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
        $config = $this->config;
        return [
            "referral_id" => "Beneficiary Id",
            "name" => "Beneficiary Name",
            "kol_email" => "Beneficiary Email",
            "kol_bankname" => "Beneficiary Bank Name",
            "kol_bankaccount" => "Beneficiary Bank Account Name",
            "kol_bankbumber" => "Beneficiary Bank Account Number",
            "kol_bankaddress" => "Beneficiary Bank Address",
            "special_kol" => "Special KOL",
            "total_user" => "Total User (L1 >= ". $config->number_users_l1 ." ) (L2 >= ". $config->number_users_l2 ." )",
            "total_order" => "Number Order (L1 >= ". $config->number_orders_l1 ." ) (L2 >= ". $config->number_orders_l2 ." )",
            "total_order_user_new" => "Number Order New Users",
            "total_amount_user_new" => "Total Amount Order New Users",
            "total_order_user_exits" => "Number Order Existing Users",
            "total_amount_user_exits" => "Total Amount Order Existing Users",
            "total_amount" => "Total L1 Consumer Amount",
            "bonus" => "Bonus From L1 Consumer Amount",
            "revenue_total_sales" => "Shop Revenue",
            "con_bonus" => "KOL Bonus Rate (%) (L1 >= ". $config->con_bonus_l1 ." ) (L2 >= ". $config->con_bonus_l2 ." )",
            "total_new_shop"=> "Number Preferred Shops (L1 >= ". $config->number_shops_l1 ." ) (L2 >= ". $config->number_shops_l2 ." )",
            "total_affiliate_member"=> "New Affiliate Member (L1 >= ". $config->number_affiliate_member_l1 ." ) (L2 >= ". $config->number_affiliate_member_l2 ." )",
            "total_affiliate_bonus"=> "Total Affiliate Bonus",
            "vat" => "VAT",
            "total_bonus" => "Total Bonus"
        ];
    }
}
            