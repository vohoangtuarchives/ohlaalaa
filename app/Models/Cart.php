<?php

namespace App\Models;
use Session;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    public $items = null;
    public $totalQty = 0;
    public $totalPrice = 0;
    public $totalSPPrice = 0;
    public $totalSPPriceAmount = 0;
    public $totalSPPriceRemainAmount = 0;
    public $totalSPUsed = 0;
    public $totalSPAmount = 0;
    public $totalShopCouponAmount = 0;
    public $totalProductSubAmount = 0;
    public $totalProductFinalAmount = 0;

    public function __construct($oldCart)
    {
        if ($oldCart) {
            $this->items = $oldCart->items;
            $this->totalQty = $oldCart->totalQty;
            $this->totalPrice = $oldCart->totalPrice;
            $this->totalSPPrice = $oldCart->totalSPPrice;
            $this->totalSPPriceAmount = $oldCart->totalSPPriceAmount;
            $this->totalSPPriceRemainAmount = $oldCart->totalSPPriceRemainAmount;
            $this->totalSPUsed = $oldCart->totalSPUsed;
            $this->totalSPAmount = $oldCart->totalSPAmount;
            $this->totalShopCouponAmount = $oldCart->totalShopCouponAmount;
            $this->totalProductSubAmount = $oldCart->totalProductSubAmount;
            $this->totalProductFinalAmount = $oldCart->totalProductFinalAmount;
            $this->refresh_new_fields();
        }
    }

// **************** ADD TO CART *******************

    public function add($item, $id, $size,$color, $keys, $values) {
        $size_cost = 0;
        $storedItem = [
            'qty' => 0
            , 'size_key' => 0
            , 'size_qty' =>  $item->size_qty
            , 'size_price' => $item->size_price
            , 'size' => $item->size
            , 'color' => $item->color
            , 'stock' => $item->stock
            , 'price' => $item->price
            , 'price_shopping_point' => $item->price_shopping_point
            , 'price_shopping_point_amount' => $item->price_shopping_point
            , 'percent_shopping_point' => $item->percent_shopping_point
            , 'item' => $item
            , 'license' => ''
            , 'dp' => '0'
            , 'keys' => $keys
            , 'values' => $values
            , 'item_price' => $item->price
            , 'item_price_shopping_point' => $item->price_shopping_point
            , 'is_shopping_point_used' => 1
            , 'shopping_point_used' => 0
            , 'shopping_point_amount' => 0
            , 'shopping_point_payment_remain' => 0
            , 'exchange_rate' => 0
            , 'is_rebate_paid' => 0
            , 'rebate_bonus' => 0
            , 'rebate_amount' => 0
            , 'rebate_in' => 0
            , 'shop_coupon_code' => ''
            , 'shop_coupon_amount' => 0
            , 'shop_coupon_value' => 0
            , 'shop_coupon_times' => 0
            , 'shop_coupon_used' => 0
            , 'product_sub_amount' => 0
            , 'product_final_amount' => 0
        ];



        if($item->type == 'Physical')
        {
            if ($this->items) {
                if (array_key_exists($id.$size.$color.str_replace(str_split(' ,'),'',$values), $this->items)) {
                    $storedItem = $this->items[$id.$size.$color.str_replace(str_split(' ,'),'',$values)];
                }
            }
        }
        else {
            if ($this->items) {
                if (array_key_exists($id.$size.$color.str_replace(str_split(' ,'),'',$values), $this->items)) {
                    $storedItem = $this->items[$id.$size.$color.str_replace(str_split(' ,'),'',$values)];
                    $storedItem['dp'] = 1;
                }
            }
        }
        $storedItem['qty']++;
        $stck = (string)$item->stock;
        if($stck != null){
            $storedItem['stock']--;
        }
        if(!empty($item->size)){
            $storedItem['size'] = $item->size[0];
        }
        if(!empty($size)){
            $storedItem['size'] = $size;
        }
        if(!empty($item->size_qty)){
            $storedItem['size_qty'] = $item->size_qty[0];
        }
        if($item->size_price != null){
            $storedItem['size_price'] = $item->size_price[0];
            $size_cost = $item->size_price[0];
        }
        if(!empty($color)){
            $storedItem['color'] = $color;
        }
        if(!empty($keys)){
            $storedItem['keys'] = $keys;
        }
        if(!empty($values)){
            $storedItem['values'] = $values;
        }
        $item->price += $size_cost;
        $storedItem['item_price_shopping_point'] = $item->price_shopping_point;
        if(!empty($item->whole_sell_qty))
        {
            foreach(array_combine($item->whole_sell_qty,$item->whole_sell_discount) as $whole_sell_qty => $whole_sell_discount)
            {
                if($storedItem['qty'] == $whole_sell_qty)
                {
                    $whole_discount[$id.$size.$color.str_replace(str_split(' ,'),'',$values)] = $whole_sell_discount;
                    Session::put('current_discount',$whole_discount);
                    break;
                }
            }
            if(Session::has('current_discount')) {
                    $data = Session::get('current_discount');
                if (array_key_exists($id.$size.$color.str_replace(str_split(' ,'),'',$values), $data)) {
                    $discount = $item->price * ($data[$id.$size.$color.str_replace(str_split(' ,'),'',$values)] / 100);
                    $item->price = $item->price - $discount;
                }
            }
        }

        $gs = Generalsetting::findOrFail(1);

        $storedItem['price'] = $item->price * $storedItem['qty'];
        $storedItem['item_price'] = $item->price;
        $storedItem['price_shopping_point'] = $item->price_shopping_point * $storedItem['qty'];
        $storedItem['price_shopping_point_amount'] = $item->price_shopping_point * $storedItem['qty'] * $gs->sp_vnd_exchange_rate;

        if($storedItem['is_shopping_point_used'] == 1){
            $maxpoint = intval($storedItem['price_shopping_point']);
            $storedItem['shopping_point_used'] = $maxpoint;
            $storedItem['shopping_point_amount'] = $maxpoint * $gs->sp_vnd_exchange_rate;
            $storedItem['shopping_point_payment_remain'] = $storedItem['price_shopping_point_amount'] - $storedItem['shopping_point_amount'];
            $storedItem['exchange_rate'] = $gs->sp_vnd_exchange_rate;
        }
        $storedItem['shop_coupon_code'] = "";
        $storedItem['shop_coupon_amount'] = 0;
        $storedItem['shop_coupon_value'] = 0;
        $storedItem['shop_coupon_times'] = 0;
        $storedItem['shop_coupon_used'] = 0;
        $storedItem['product_sub_amount'] = $storedItem['price'] + $storedItem['price_shopping_point_amount'] - $storedItem['shopping_point_amount'];
        $storedItem['product_final_amount'] = $storedItem['product_sub_amount'];

        $this->items[$id.$size.$color.str_replace(str_split(' ,'),'',$values)] = $storedItem;
        $this->totalQty++;


    }

// **************** ADD TO CART ENDS *******************



// **************** ADD TO CART MULTIPLE *******************

    public function addnum($item, $id, $qty, $size, $color, $size_qty, $size_price, $size_key, $keys, $values) {
        $size_cost = 0;
        $storedItem = [
            'qty' => 0
            , 'size_key' => 0
            , 'size_qty' =>  $item->size_qty
            , 'size_price' => $item->size_price
            , 'size' => $item->size
            , 'color' => $item->color
            , 'stock' => $item->stock
            , 'price' => $item->price
            , 'price_shopping_point' => $item->price_shopping_point
            , 'price_shopping_point_amount' => $item->price_shopping_point
            , 'percent_shopping_point' => $item->percent_shopping_point
            , 'item' => $item
            , 'license' => ''
            , 'dp' => '0'
            , 'keys' => $keys
            , 'values' => $values
            , 'item_price' => $item->price
            , 'item_price_shopping_point' => $item->price_shopping_point
            , 'is_shopping_point_used' => 1
            , 'shopping_point_used' => 0
            , 'shopping_point_amount' => 0
            , 'shopping_point_payment_remain' => 0
            , 'exchange_rate' => 0
            , 'is_rebate_paid' => 0
            , 'rebate_bonus' => 0
            , 'rebate_amount' => 0
            , 'rebate_in' => 0
            , 'shop_coupon_code' => ''
            , 'shop_coupon_amount' => 0
            , 'shop_coupon_value' => 0
            , 'shop_coupon_times' => 0
            , 'shop_coupon_used' => 0
            , 'product_sub_amount' => 0
            , 'product_final_amount' => 0
        ];

        if($item->type == 'Physical')
        {
            if ($this->items) {
                if (array_key_exists($id.$size.$color.str_replace(str_split(' ,'),'',$values), $this->items)) {
                    $storedItem = $this->items[$id.$size.$color.str_replace(str_split(' ,'),'',$values)];
                }
            }
        }
        else {
            if ($this->items) {
                if (array_key_exists($id.$size.$color.str_replace(str_split(' ,'),'',$values), $this->items)) {
                    $storedItem = $this->items[$id.$size.$color.str_replace(str_split(' ,'),'',$values)];
                    $storedItem['dp'] = 1;
                }
            }
        }
        $storedItem['qty'] = $storedItem['qty'] + $qty;
        $stck = (string)$item->stock;
        if($stck != null){
                $storedItem['stock'] = $storedItem['stock'] - $qty;
        }
        if(!empty($item->size)){
            $storedItem['size'] = $item->size[0];
        }
        if(!empty($size)){
            $storedItem['size'] = $size;
        }
        if(!empty($size_key)){
            $storedItem['size_key'] = $size_key;
        }
        if(!empty($item->size_qty)){
            $storedItem['size_qty'] = $item->size_qty [0];
        }
        if(!empty($size_qty)){
            $storedItem['size_qty'] = $size_qty;
        }
        if(!empty($item->size_price)){
            $storedItem['size_price'] = $item->size_price[0];
            $size_cost = $item->size_price[0];
        }
        if(!empty($size_price)){
        $storedItem['size_price'] = $size_price;
        $size_cost = $size_price;
        }
        if(!empty($item->color)){
        $storedItem['color'] = $item->color[0];
        }
        if(!empty($color)){
        $storedItem['color'] = $color;
        }
        if(!empty($keys)){
        $storedItem['keys'] = $keys;
        }
        if(!empty($values)){
        $storedItem['values'] = $values;
        }

        $item->price += $size_cost;

        $storedItem['item_price_shopping_point'] = $item->price_shopping_point;
        if(!empty($item->whole_sell_qty))
        {
            foreach($item->whole_sell_qty as $key => $data){
                if(($key + 1) != count($item->whole_sell_qty)){
                    if(($storedItem['qty'] >= $item->whole_sell_qty[$key]) && ($storedItem['qty'] < $item->whole_sell_qty[$key+1])){
                        $whole_discount[$id.$size.$color.str_replace(str_split(' ,'),'',$values)] = $item->whole_sell_discount[$key];
                        Session::put('current_discount',$whole_discount);
                        $ck = 'first';
                        break;
                    }
                }
                else {
                    if(($storedItem['qty'] >= $item->whole_sell_qty[$key])){
                        $whole_discount[$id.$size.$color.str_replace(str_split(' ,'),'',$values)] = $item->whole_sell_discount[$key];
                        Session::put('current_discount',$whole_discount);
                        $ck = 'last';
                        break;
                    }
                }
            }

            if(Session::has('current_discount')) {
                    $data = Session::get('current_discount');
                if (array_key_exists($id.$size.$color.str_replace(str_split(' ,'),'',$values), $data)) {

                    $discount = $item->price * ($data[$id.$size.$color.str_replace(str_split(' ,'),'',$values)] / 100);
                    $item->price = $item->price - $discount;
                }
            }
        }
        $gs = Generalsetting::findOrFail(1);
        $storedItem['price'] = $item->price * $storedItem['qty'];
        $storedItem['item_price'] = $item->price;
        $storedItem['price_shopping_point'] = $item->price_shopping_point * $storedItem['qty'];
        $storedItem['price_shopping_point_amount'] = $item->price_shopping_point * $storedItem['qty'] * $gs->sp_vnd_exchange_rate;

        if($storedItem['is_shopping_point_used'] == 1){
            $maxpoint = intval($storedItem['price_shopping_point']);
            $storedItem['shopping_point_used'] = $maxpoint;
            $storedItem['shopping_point_amount'] = $maxpoint * $gs->sp_vnd_exchange_rate;
            $storedItem['shopping_point_payment_remain'] = $storedItem['price_shopping_point_amount'] - $storedItem['shopping_point_amount'];
            $storedItem['exchange_rate'] = $gs->sp_vnd_exchange_rate;
        }
        $storedItem['shop_coupon_code'] = "";
        $storedItem['shop_coupon_amount'] = 0;
        $storedItem['shop_coupon_value'] = 0;
        $storedItem['shop_coupon_times'] = 0;
        $storedItem['shop_coupon_used'] = 0;
        $storedItem['product_sub_amount'] = $storedItem['price'] + $storedItem['price_shopping_point_amount'] - $storedItem['shopping_point_amount'];
        $storedItem['product_final_amount'] = $storedItem['product_sub_amount'];

        $this->items[$id.$size.$color.str_replace(str_split(' ,'),'',$values)] = $storedItem;
        $this->totalQty += $storedItem['qty'];
    }


// **************** ADD TO CART MULTIPLE ENDS *******************


// **************** ADDING QUANTITY *******************

    public function adding($item, $id, $size_qty, $size_price) {
        $storedItem = ['qty' => 0
            , 'size_key' => 0
            , 'size_qty' =>  $item->size_qty
            , 'size_price' => $item->size_price
            , 'size' => $item->size
            , 'color' => $item->color
            , 'stock' => $item->stock
            , 'price' => $item->price
            , 'price_shopping_point' => $item->price_shopping_point
            , 'price_shopping_point_amount' => $item->price_shopping_point
            , 'percent_shopping_point' => $item->percent_shopping_point
            , 'item' => $item
            , 'license' => ''
            , 'dp' => '0'
            , 'keys' => ''
            , 'values' => ''
            , 'item_price' => $item->price
            , 'item_price_shopping_point' => $item->price_shopping_point
            , 'is_shopping_point_used' => 1
            , 'shopping_point_used' => 0
            , 'shopping_point_amount' => 0
            , 'shopping_point_payment_remain' => 0
            , 'exchange_rate' => 0
            , 'is_rebate_paid' => 0
            , 'rebate_bonus' => 0
            , 'rebate_amount' => 0
            , 'rebate_in' => 0
            , 'shop_coupon_code' => ''
            , 'shop_coupon_amount' => 0
            , 'shop_coupon_value' => 0
            , 'shop_coupon_times' => 0
            , 'shop_coupon_used' => 0
            , 'product_sub_amount' => 0
            , 'product_final_amount' => 0
        ];

        if ($this->items) {
            if (array_key_exists($id, $this->items)) {
                $storedItem = $this->items[$id];
            }
        }
        $storedItem['qty']++;

            if($item->stock != null){
                $storedItem['stock']--;
            }

        // CURRENCY ISSUE CHECK IT CAREFULLY

        $item->price += (double)$size_price;

        if(!empty($item->whole_sell_qty))
        {
            foreach(array_combine($item->whole_sell_qty,$item->whole_sell_discount) as $whole_sell_qty => $whole_sell_discount)
            {
                if($storedItem['qty'] == $whole_sell_qty)
                {
                    $whole_discount[$id] = $whole_sell_discount;
                    Session::put('current_discount',$whole_discount);
                    break;
                }
            }
            if(Session::has('current_discount')) {
                    $data = Session::get('current_discount');
                if (array_key_exists($id, $data)) {
                    $discount = $item->price * ($data[$id] / 100);
                    $item->price = $item->price - $discount;
                }
            }
        }
        $gs = Generalsetting::findOrFail(1);
        $storedItem['price'] = $item->price * $storedItem['qty'];
        $storedItem['item_price'] = $item->price;
        $storedItem['price_shopping_point'] = $item->price_shopping_point * $storedItem['qty'];
        $storedItem['price_shopping_point_amount'] = $item->price_shopping_point * $storedItem['qty'] * $gs->sp_vnd_exchange_rate;

        if($storedItem['is_shopping_point_used'] == 1){
            $maxpoint = intval($storedItem['price_shopping_point']);
            $storedItem['shopping_point_used'] = $maxpoint;
            $storedItem['shopping_point_amount'] = $maxpoint * $gs->sp_vnd_exchange_rate;
            $storedItem['shopping_point_payment_remain'] = $storedItem['price_shopping_point_amount'] - $storedItem['shopping_point_amount'];
            $storedItem['exchange_rate'] = $gs->sp_vnd_exchange_rate;
        }

        $storedItem['product_sub_amount'] = $storedItem['price'] + $storedItem['price_shopping_point_amount'] - $storedItem['shopping_point_amount'];
	    $storedItem['product_final_amount'] = $storedItem['product_sub_amount'];

        $this->items[$id] = $storedItem;
        $this->totalQty += $storedItem['qty'];
    }

// **************** ADDING QUANTITY ENDS *******************


// **************** REDUCING QUANTITY *******************

    public function reducing($item, $id, $size_qty, $size_price) {
        $storedItem = ['qty' => 0,
            'size_key' => 0,
            'size_qty' =>  $item->size_qty,
            'size_price' => $item->size_price,
            'size' => $item->size,
            'color' => $item->color,
            'stock' => $item->stock,
            'price' => $item->price
            , 'price_shopping_point' => $item->price_shopping_point
            , 'price_shopping_point_amount' => $item->price_shopping_point
            , 'percent_shopping_point' => $item->percent_shopping_point
            , 'item' => $item
            , 'license' => ''
            , 'dp' => '0'
            , 'keys' => ''
            , 'values' => ''
            , 'item_price' => $item->price
            , 'item_price_shopping_point' => $item->price_shopping_point
            , 'is_shopping_point_used' => 1
            , 'shopping_point_used' => 0
            , 'shopping_point_amount' => 0
            , 'shopping_point_payment_remain' => 0
            , 'exchange_rate' => 0
            , 'is_rebate_paid' => 0
            , 'rebate_bonus' => 0
            , 'rebate_amount' => 0
            , 'rebate_in' => 0
            , 'shop_coupon_code' => ''
            , 'shop_coupon_amount' => 0
            , 'shop_coupon_value' => 0
            , 'shop_coupon_times' => 0
            , 'shop_coupon_used' => 0
            , 'product_sub_amount' => 0
            , 'product_final_amount' => 0
        ];
        if ($this->items) {
            if (array_key_exists($id, $this->items)) {
                $storedItem = $this->items[$id];
            }
        }
        $storedItem['qty']--;
        if($item->stock != null){
            $storedItem['stock']++;
        }

        // CURRENCY ISSUE CHECK IT CAREFULLY

        $item->price += (double)$size_price;
        if(!empty($item->whole_sell_qty))
        {
            $len = count($item->whole_sell_qty);
            foreach($item->whole_sell_qty as $key => $data1)
            {
                if($storedItem['qty'] < $item->whole_sell_qty[$key])
                {
                    if($storedItem['qty'] < $item->whole_sell_qty[0])
                    {
                        Session::forget('current_discount');
                        break;
                    }

                    $whole_discount[$id] = $item->whole_sell_discount[$key-1];
                    Session::put('current_discount',$whole_discount);
                    break;
                }

            }
            if(Session::has('current_discount')) {
                    $data = Session::get('current_discount');
                if (array_key_exists($id, $data)) {
                    $discount = $item->price * ($data[$id] / 100);
                    $item->price = $item->price - $discount;
                }
            }
        }
        $gs = Generalsetting::findOrFail(1);
        $storedItem['price'] = $item->price * $storedItem['qty'];
        $storedItem['item_price'] = $item->price;
        $storedItem['price_shopping_point'] = $item->price_shopping_point * $storedItem['qty'];
        $storedItem['price_shopping_point_amount'] = $item->price_shopping_point * $storedItem['qty'] * $gs->sp_vnd_exchange_rate;

        if($storedItem['is_shopping_point_used'] == 1){
            $maxpoint = intval($storedItem['price_shopping_point']);
            $storedItem['shopping_point_used'] = $maxpoint;
            $storedItem['shopping_point_amount'] = $maxpoint * $gs->sp_vnd_exchange_rate;
            $storedItem['shopping_point_payment_remain'] = $storedItem['price_shopping_point_amount'] - $storedItem['shopping_point_amount'];
            $storedItem['exchange_rate'] = $gs->sp_vnd_exchange_rate;
        }
        $storedItem['product_sub_amount'] = $storedItem['price'] + $storedItem['price_shopping_point_amount'] - $storedItem['shopping_point_amount'];
	    $storedItem['product_final_amount'] = $storedItem['product_sub_amount'];
        $this->items[$id] = $storedItem;
        $this->totalQty--;
    }

// **************** REDUCING QUANTITY ENDS *******************

    public function updateLicense($id,$license) {

        $this->items[$id]['license'] = $license;
    }

    public function updateColor($item, $id,$color) {

        $this->items[$id]['color'] = $color;
    }

    public function removeItem($id) {
        $this->totalQty -= $this->items[$id]['qty'];
        $this->totalPrice -= $this->items[$id]['price'];
        unset($this->items[$id]);
            if(Session::has('current_discount')) {
                    $data = Session::get('current_discount');
                if (array_key_exists($id, $data)) {
                    unset($data[$id]);
                    Session::put('current_discount',$data);
                }
            }
    }

// **************** REFRESH ITEMS *******************
    public function refresh_new_fields ()
    {
        foreach($this->items as $item){
            if(!isset($item['shop_coupon_code'])){
                $item['shop_coupon_code'] = '';
            }
            if(!isset($item['shop_coupon_amount'])){
                $item['shop_coupon_amount'] = 0;
            }
            if(!isset($item['shop_coupon_value'])){
                $item['shop_coupon_value'] = 0;
            }
            if(!isset($item['shop_coupon_times'])){
                $item['shop_coupon_times'] = 0;
            }
            if(!isset($item['shop_coupon_used'])){
                $item['shop_coupon_used'] = 0;
            }
            if(!isset($item['price_shopping_point'])){
                $item['price_shopping_point'] = 0;
            }
            if(!isset($item['price_shopping_point_amount'])){
                $item['price_shopping_point_amount'] = 0;
            }
            if(!isset($item['shopping_point_amount'])){
                $item['shopping_point_amount'] = 0;
            }
            if(!isset($item['percent_shopping_point'])){
                $item['percent_shopping_point'] = 0;
            }
            if(!isset($item['item_price_shopping_point'])){
                $item['item_price_shopping_point'] = 0;
            }
            if(!isset($item['product_sub_amount'])){
                $item['product_sub_amount'] = 0;
            }
            if(!isset($item['product_final_amount'])){
                $item['product_final_amount'] = 0;
            }
            $size = $item['size'];
            $color =  $item['color'];
            $values = $item['values'];
            $this->items[$item['item']->id.$size.$color.str_replace(str_split(' ,'),'',$values)] = $item;
        }
    }
}
