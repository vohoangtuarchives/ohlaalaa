<?php

namespace App\Exports\Orders\Reports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SaleFullReportDetail implements FromCollection, WithHeadings
{
    use Exportable;
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
            "order_id" => "Order Id",
            "created_at" => "1. Order Date",
            "shop_id" => "2. Merchant",
            "shop_name" => "3. Merchant Name",
            "address" => "4. Address",
            "TaxCode" => "5. Tax Code",
            "email" => "6. Email",
            "order_number" => "7. Bill No",
            "status" => "8. Status Of Bill",
            "_9_Amount" => "9. Amount",
            "_10_Point" => "10. Point",
            "_11_Rate" => "11. Rate",
            "_12_Amount_of_Point" => "12. Amount of Point",
            "_13_Voucher_of_Merchant" => "13. Voucher of Merchant",
            "_14_Voucher_of_Techhub" => "14. Voucher of Techhub",
            "_15_Delivery_fee" => "15. Delivery Fee",
            "_18_VISA_JCB_MASTERCARD_UPI_AMEX" => "18. Credit cards",
            "_19_Amount_Partner_Must_Pay" => "19. Partner Must Pay",
            "_20_PAYMENT_TO_TECHHUB_Date" => "20. Partner Payment Date",
            "_21_PAYMENT_TO_TECHHUB_PARTNER" => "21. Partner Name",
            "_22_PAYMENT_TO_TECHHUB_AMOUNT" => "22. Partner Payment to Techhub Amount",
            "_23_CHARGE_FEES_VND" => "23. Charge Fee ",
            "_24_Amount_Must_Pay_to_Merchant" => "24. Must Pay to Merchant",
            "_25_PAYMENT_to_Merchant_Date" => "25. Payment to Merchant Date",
            "_26_Payment_to_Merchant_Amount" => "26. Payment to Merchant Amount",
            "BankAccountName" => "27. Merchant Bank Account Name",
            "BankName" => "28. Merchant Bank Name",
            "BankAddress" => "29. Merchant Bank Branch",
            "BankAccountNumber" => "30. Merchant Bank Account No",
            "_31_Payment_to_Carrier_Date" => "31. Carrier Payment Date",
            "_32_Payment_to_Carrier_Amount" => "32. Carrier Payment Amount",
            "handling_fee_rate" => "Merchant Handling Fee",
            "tax" => "Tax",
            "phone" => "Phone",
            "shipping_partner_code" => "Delivery Code",
            "shipping_type" => "Shipping Type",
            "is_handlingfee_collected" => "Handling Fee Collected",
            "merchant_is_debt" => "Merchant Is Debt",
            "merchant_debt_amount" => "Debt Amount",
            "product_name" => "Product Name",
            "child_category" => "Child Category",
            "sub_category" => "Sub Category",
            "main_category" => "Main Category",
            "refund_date" => "Refund Date",
            "refund_amount" => "Refund Amount",
            "refund_bank" => "Refund Bank",
            "refund_note" => "Refund Note",
        ];
    }
}
