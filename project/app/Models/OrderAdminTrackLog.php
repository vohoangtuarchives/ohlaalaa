<?php

namespace App\Models;

use App\Models\Order;
use App\Models\OrderVendorInfo;
use Illuminate\Database\Eloquent\Model;

class OrderAdminTrackLog extends Model
{
    public function write_vendor_info($old, $new, $issuer_id)
    {
        if($old==null){
            $old = new OrderVendorInfo;
        }
        $this->issuer_id = $issuer_id;
        $this->shop_id = $new->shop_id;
        $this->order_id = $new->order_id;
        $this->title = 'PAYMENT TO MERCHANT';
        $content = '';
        if($old->is_paid != $new->is_paid)
            $content .= 'Is Paid: '.'['.(isset($old->is_paid) ? $old->is_paid : '').'] => ['.$new->is_paid.']'.'<br>';
        if($old->payment_to_merchant_date != $new->payment_to_merchant_date)
            $content .= 'Date: '.'['.(isset($old->payment_to_merchant_date) ? $old->payment_to_merchant_date : '').'] => ['.$new->payment_to_merchant_date.']'.'<br>';
        if($old->payment_to_merchant_amount != $new->payment_to_merchant_amount)
            $content .= 'Amount: '.'['.(isset($old->payment_to_merchant_amount) ? $old->payment_to_merchant_amount : '').'] => ['.$new->payment_to_merchant_amount.']'.'<br>';
        if($old->is_debt != $new->is_debt)
            $content .= 'Merchant In Debt: '.'['.($old->is_debt == 1 ? 'Yes' : 'No').'] => ['.($new->is_debt == 1 ? 'Yes' : 'No').']'.'<br>';
        if($old->debt_amount != $new->debt_amount)
            $content .= 'Debt Amount: '.'['.(isset($old->debt_amount) ? $old->debt_amount : '').'] => ['.$new->debt_amount.']';
        if($content != ''){
            $content = $new->shop()->first()->shop_name.'<br>'.$content;
            $this->content = $content;
            $this->save();
        }
    }

    public function write_refund($old, $new, $issuer_id)
    {
        $this->issuer_id = $issuer_id;
        $this->order_id = $new->id;
        $this->title = 'REFUND DETAIL';
        $content = '';
        if($old->refund_date != $new->refund_date)
            $content .= 'Date: '.'['.(isset($old->refund_date) ? $old->refund_date : '').'] => ['.$new->refund_date.']'.'<br>';
        if($old->refund_bank != $new->refund_bank)
            $content .= 'Bank: '.'['.(isset($old->refund_bank) ? $old->refund_bank : '').'] => ['.$new->refund_bank.']'.'<br>';
        if($old->refund_amount != $new->refund_amount)
            $content .= 'Amount: '.'['.(isset($old->refund_amount) ? $old->refund_amount : '').'] => ['.$new->refund_amount.']';
        if($content != ''){
            $this->content = $content;
            $this->save();
        }
    }

    public function write_techhub_payment($old, $new, $issuer_id)
    {
        $this->issuer_id = $issuer_id;
        $this->order_id = $new->id;
        $this->title = 'PAYMENT TO TECHHUB';
        $content = '';
        if($old->payment_to_company_date != $new->payment_to_company_date)
            $content .= 'Date: '.'['.(isset($old->payment_to_company_date) ? $old->payment_to_company_date : '').'] => ['.$new->payment_to_company_date.']'.'<br>';
        if($old->payment_to_company_partner != $new->payment_to_company_partner)
            $content .= 'Partner: '.'['.(isset($old->payment_to_company_partner) ? $old->payment_to_company_partner : '').'] => ['.$new->payment_to_company_partner.']'.'<br>';
        if($old->payment_to_company_amount != $new->payment_to_company_amount)
            $content .= 'Amount: '.'['.(isset($old->payment_to_company_amount) ? $old->payment_to_company_amount : '').'] => ['.$new->payment_to_company_amount.']';
        if($content != ''){
            $this->content = $content;
            $this->save();
        }
    }


    public function issuer()
    {
        return $this->belongsTo('App\Models\Admin','issuer_id');
    }
}
