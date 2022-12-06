<?php

namespace App\Exports\Orders\Reports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class VendorOrderExport implements FromCollection, WithHeadings
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
        // $from =' From ' . $this->from;
        // if ($this->from < date('Y')) {
        //     $from .= ' - ' . date('Y');
        // }
        return [
            "order_number" => "Mã đơn hàng",
            "total_qty" => "Tổng số lượng",
            "tong_chi_phi" => "Tổng chi phí",
            "phuong_thuc_thanh_toan" => "Phương thức thanh toán",
            "ngay_thanh_toan" => "Ngày thanh toán",
            "so_tien_thanh_toan" => "Số tiền thanh toán",
            "ma_van_don" => "Mã vận đơn",
            "ngay_dat_hang" => "Ngày đặt hàng",
            "ten_nguoi_mua" => "Tên người mua",
            "email_nguoi_mua" => "email người mua",
            "phone_nguoi_mua" => "số điện thoại người mua"
        ];
    }
}
