<?php

namespace App\Exports\Orders\Reports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class KOLConsumerBonusForUser implements FromCollection, WithHeadings
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
            "referral_id" => "Id",
            "name" => "Ten kol",
            "kol_email" => "Email",
            "kol_bankname" => "Tên ngân hàng",
            "kol_bankaccount" => "Tên tài khoản ngân hàng",
            "kol_bankbumber" => "Số tài khoản ngân hàng",
            "kol_bankaddress" => "Địa chỉ ngân hàng",
            "special_kol" => "KOL đắc biệt",
            "total_user" => "Tổng User mới (L1 >= ". $config->number_users_l1 ." ) (L2 >= ". $config->number_users_l2 ." )",
            "total_order" => "'Số lượng đơn hàng (L1 >= ". $config->number_orders_l1 ." ) (L2 >= ". $config->number_orders_l2 ." )",
            "total_order_user_new" => "Tổng đơn hàng User mới",
            "total_amount_user_new" => "Tống tiền User mới",
            "total_order_user_exits" => "Tổng đơn hàng User cũ",
            "total_amount_user_exits" => "Tống tiền User cũ",
            "total_amount" => "Tống chi tiêu L1",
            "bonus" => "Tiền thưởng từ L1 (1)",
            "revenue_total_sales" => "Doanh thu shop",
            "con_bonus" => "(%) được thưởng (L1 >= ". $config->con_bonus_l1 ." ) (L2 >= ". $config->con_bonus_l2 ." )",
            "total_new_shop"=> "Số lượng shop uy tín (L1 >= ". $config->number_shops_l1 ." ) (L2 >= ". $config->number_shops_l2 ." )",
            "total_affiliate_member"=> "Thành viên Affiliate mới (L1 >= ". $config->number_affiliate_member_l1 ." ) (L2 >= ". $config->number_affiliate_member_l2 ." )",
            "total_affiliate_bonus"=> "Tổng tiền thưởng từ Affiliate (2)",
            "vat"=> "VAT",
            "total_bonus" => "Tổng tiền thưởng (1) + (2)"
        ];
    }
}
