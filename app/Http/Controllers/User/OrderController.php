<?php

namespace App\Http\Controllers\User;

use Auth;
use Datatables;
use Carbon\Carbon;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\PaymentGateway;
use App\Http\Controllers\Controller;
use App\Models\KolConfig;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\User;
use App\Exports\Orders\Reports\KOLConsumerBonusForUser;

class OrderController extends Controller
{
    const IS_VENDOR  = '2'; //active
    const PREFERRED  = '1';
    const KOL  = '1';
    const SPECIAL_KOL  = '1';
    const RANKING  = '1';
    const EMAIL_VERIFIED = 'Yes';
    const VAT = 2000000;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function orders()
    {
        $user = Auth::guard('web')->user();
        $orders = Order::where('user_id','=',$user->id)->orderBy('id','desc')->get();
        // if(count($orders) == 0){
        //     $orders = Order::where('customer_email','=',$user->email)->orderBy('created_at','desc')->get();
        // }
        return view('user.order.index',compact('user','orders'));
    }

    public function ordertrack()
    {
        $user = Auth::guard('web')->user();
        return view('user.order-track',compact('user'));
    }

    public function trackload($id)
    {
        $order = Order::where('order_number','=',$id)->first();
        $datas = array('Pending','Processing','On Delivery','Completed');
        return view('load.track-load',compact('order','datas'));
    }

    public function order($id)
    {
        $user = Auth::guard('web')->user();
        $order = Order::findOrfail($id);
        $oldCart = unserialize(bzdecompress(utf8_decode($order->cart)));
        $cart = new Cart($oldCart);
        return view('user.order.details',compact('user','order','cart'));
    }

    public function order_received($id)
    {
        $order = Order::findOrfail($id);
        $order->customer_received = true;
        $order->save();
        return response()->json(1);
    }

    public function orderdownload($slug,$id)
    {
        $user = Auth::guard('web')->user();
        $order = Order::where('order_number','=',$slug)->first();
        $prod = Product::findOrFail($id);
        if(!isset($order) || $prod->type == 'Physical' || $order->user_id != $user->id)
        {
            return redirect()->back();
        }
        return response()->download(public_path('assets/files/'.$prod->file));
    }

    public function orderprint($id)
    {
        $user = Auth::guard('web')->user();
        $order = Order::findOrfail($id);
        $oldCart = unserialize(bzdecompress(utf8_decode($order->cart)));
        $cart = new Cart($oldCart);
        return view('user.order.print',compact('user','order','cart'));
    }

    public function trans()
    {
        $id = $_GET['id'];
        $trans = $_GET['tin'];
        $order = Order::findOrFail($id);
        $order->txnid = $trans;
        $order->update();
        $data = $order->txnid;
        return response()->json($data);
    }

    /////////////////////// KOL Bonus//////////////////////////////////////

    public function getKol($date)
    {
        $data = KolConfig::where('kol_date', '=', $date)->first();

        if ($data == null) {
            return response()->json(['errors' => 'Vui lòng liên hệ quản trị hệ thống để biết thêm chi tiết.'], 400);
        }

        return response()->json($data);
    }

    public function kolConsumerBonusForUser()
    {
        $config = KolConfig::where('kol_date', '=',  date('m-Y'))->first();
        $user = Auth::user();

       // dd(var_dump(in_array($user->id, $arr)));
        // if (in_array($user->id, $arr)) {
        //     $check = 1 ;
        // } else {
        //     return redirect()->route('user-dashboard');
        // }

        if ($config == null  || $config->count()==0) {
            $errors = ['errors' => 'Please set config for '.date('m-Y')];
            return view('user.order.reports.kol-consumer-bonus-user',compact('errors','config','user'));

        }
        return view('user.order.reports.kol-consumer-bonus-user',compact('config', 'user'));
    }

    public function datatablesKOLConsumerBonusForUser($from)
    {
        $config = KolConfig::where('kol_date', '=', $from)->first();

        if ($config == null) {

            return response()->json(['errors' => 'Please set config for '.date('m-Y')], 400);
        }

        $datas = app('App\Http\Controllers\Admin\ApiUserController')->kolConsumerBonusDataForUser($from);

        if ($datas->count()>0) {
            foreach ($datas as  $data) {
                $l1 = $l2 = 0;

                if ($data->total_order < $config->number_orders_l1) {
                    $l1 +=1;
                }
                if ($data->total_order < $config->number_orders_l2) {
                    $l2 +=1;
                }

                if ($data->total_user < $config->number_users_l1) {
                    $l1 +=1;
                }
                if ($data->total_user < $config->number_users_l2) {
                    $l2 +=1;
                }
                if ($data->total_new_shop < $config->total_shops_l1) {
                    $l1 +=1;
                }
                if ($data->total_new_shop < $config->total_shops_l2) {
                    $l2 +=1;
                }

                if ($data->total_affiliate_member < $config->number_affiliate_member_l1) {
                    $l1 +=1;
                }
                if ($data->total_affiliate_member < $config->number_affiliate_member_l2) {
                    $l2 +=1;
                }

                $data->l1 = $l1;
                $data->l2 = $l2;
                
                /*
                if ($data->special_kol == 1) {
                    $data->bonus = ($config->con_bonus_l1 *  $data->total_amount) / 100;
                } elseif ($data->revenue_total_sales >  $config->revenue_l1) {
                    $data->bonus = ($config->con_bonus_l1 *  $data->total_amount) / 100;
                } elseif ($data->revenue_total_sales >  $config->revenue_l2 && $data->revenue_total_sales <  $config->revenue_l1) {
                    $data->bonus = ($config->con_bonus_l2 *  $data->total_amount) / 100;
                } else {
                     if ($l1 == 0) {
                        $data->bonus =  ($config->con_bonus_l1 *  $data->total_amount) / 100;
                    } elseif ($l2 == 0) {
                        $data->bonus =  ($config->con_bonus_l2 *  $data->total_amount) / 100;
                    } else {
                        $data->bonus = 0;
                    }
                }
                */
                if ($data->special_kol == 'yes') {
                    $data->con_bonus = $config->con_bonus_l1."%";
                } elseif ($data->revenue_total_sales >  $config->revenue_l1) {
                    $data->con_bonus = $config->con_bonus_l1."%";
                } elseif ($data->revenue_total_sales >  $config->revenue_l2 && $data->revenue_total_sales < $config->revenue_l1) {
                    $data->con_bonus = $config->con_bonus_l2."%";
                } else {
                    if ($l1 === 0) {
                        $data->con_bonus = $config->con_bonus_l1."%";
                    } elseif ($l2 === 0) {
                        $data->con_bonus = $config->con_bonus_l2."%";
                    } else {
                        $data->con_bonus = '0%';
                    }
                }

                if ($data->special_kol == 'yes') {
                    $data->old_aff_con_bonus = $config->con_bonus_l1."%";
                } elseif ($data->total_amount >  $config->revenue_l1) {
                    $data->old_aff_con_bonus = $config->con_bonus_l1."%";
                } elseif ($data->total_amount >  $config->revenue_l2 && $data->total_amount < $config->revenue_l1) {
                    $data->old_aff_con_bonus = $config->con_bonus_l2."%";
                } else {
                    if ($l1 === 0) {
                        $data->old_aff_con_bonus = $config->con_bonus_l1."%";
                    } elseif ($l2 === 0) {
                        $data->old_aff_con_bonus = $config->con_bonus_l2."%";
                    } else {
                        $data->old_aff_con_bonus = '0%';
                    }
                }
                
                $bonus_1 = $data->revenue_total_sales * (float)$data->con_bonus / 100;
                
                $bonus_2 = $data->total_amount * (float)$data->old_aff_con_bonus / 100;

                $data->bonus = (float) max($bonus_1, $bonus_2);
                
                $data->total_bonus = $data->total_affiliate_bonus +  $data->bonus;

                
                if ($data->total_bonus >= static::VAT) {
                    $data->vat = 10;
                    $data->total_bonus = $data->total_bonus - (($data->total_bonus * 10) / 100);
                } else {
                    $data->vat = 0;
                }
            }
        }

       return  Datatables::of($datas)
            ->addColumn('kol_info', function($data) use ($config) {
                $info = '<span>'. $data->referral_id .'</span><br>'
                    .'<span>'. $data->name .'</span><br>'
                    .'<span>'. $data->kol_email .'</span><br>'
                    .'<span>'. $data->kol_bankname .'</span><br>'
                    .'<span>'. $data->kol_bankaccount .'</span><br>'
                    .'<span>'. $data->kol_bankbumber .'</span><br>'
                    .'<span>'. $data->kol_bankaddress .'</span><br>' ;
                    if ($data->special_kol == 1)  {
                        $info .= '<span style="color:red;font-weight:bold">Special KOL</span><br>' ;
                    } elseif ($data->revenue_total_sales >  $config->revenue_l1) {
                        $info .= '<span style="color:#6318ba;font-weight:bold">Shop Revenue L1</span><br>' ;
                    } elseif ($data->revenue_total_sales >  $config->revenue_l2) {
                        $info .= '<span style="color:#6318ba;font-weight:bold">Shop Revenue L2</span><br>' ;
                    }
                return $info;
            })
             ->addColumn('kol_info_bank_info', function($data) {
                $info = '<span>'. $data->kol_bankname .'</span><br>'.'<span>'. $data->kol_bankaccount .'</span><br>'.'<span>'. $data->kol_bankbumber .'</span><br>'.'<span>'. $data->kol_bankaddress .'</span>';
                return $info;
            })
            ->addColumn('total_order', function($data) use ($config) {    ///////////////  target 1
                $info = '<span>'. $data->total_order .'</span><br>'.'<span>';
                return $info;
            })
            ->addColumn('total_user', function($data) use ($config) {             ///////////////  target 2
                $info = '<span>'. $data->total_user .'</span><br>'.'<span>';
                return $info;
            })
            ->addColumn('total_new_shop', function($data) use ($config) {           ///////////////  target 3
                $info = '<span>'. $data->total_new_shop .'</span><br>'.'<span>';
                return $info;
            })
            ->addColumn('total_affiliate_member', function($data) use ($config) {       ///////////////  target 4
                $info = '<span>'. $data->total_affiliate_member .'</span><br>'.'<span>';
                return $info;
            })
            ->addColumn('total_amount', function($data) {
                $total =  number_format($data->total_amount, 0, ',', ',');
                $info = '<span>'. $total .'</span><br>'.'<span>';
                return $info;
            })
            ->addColumn('total_order_user_new', function($data) {
                $info = '<span>'. $data->total_order_user_new .'</span><br>'.'<span>';
                return $info;
            })
            ->addColumn('total_amount_user_new', function($data) {
                $total_amount_user_new =  number_format($data->total_amount_user_new, 0, ',', ',');
                $info = '<span>'. $total_amount_user_new .'</span><br>'.'<span>';
                return $info;
            })
            ->addColumn('total_order_user_exits', function($data) {
                $info = '<span>'. $data->total_order_user_exits .'</span><br>'.'<span>';
                return $info;
            })
            ->addColumn('total_amount_user_exits', function($data) {
                $total_amount_user_exits =  number_format($data->total_amount_user_exits, 0, ',', ',');
                $info = '<span>'. $total_amount_user_exits .'</span><br>'.'<span>';
                return $info;
            })
            ->addColumn('kol_consumer_bonus_rate', function($data) use ($config) {
                if ($data->special_kol == 1) {
                    $info = '<span style="font-weight:bold">'. $config->con_bonus_l1 .' %</span><br>'.'<span>';
                } elseif ($data->revenue_total_sales >  $config->revenue_l1) {
                    $info = '<span style="font-weight:bold">'. $config->con_bonus_l1 .' %</span><br>'.'<span>';
                } elseif ($data->revenue_total_sales >  $config->revenue_l2 && $data->revenue_total_sales < $config->revenue_l1) {
                    $info = '<span style="font-weight:bold">'. $config->con_bonus_l2 .' %</span><br>'.'<span>';
                } else {
                    if ($data->l1 === 0) {
                        $info = '<span style="font-weight:bold">'. $config->con_bonus_l1 .' %</span><br>'.'<span>';
                    } elseif ($data->l2 === 0) {
                        $info = '<span style="font-weight:bold">'. $config->con_bonus_l2 .' %</span><br>'.'<span>';
                    } else {
                        $info = '<span>0 %</span><br>'.'<span>';
                    }
                }
                return $info;
            })
            ->addColumn('bonus', function($data) use ($config) {

                $data->bonus =  number_format($data->bonus, 0, ',', ',') . " đ";
                $info = '<span>'. $data->bonus .' </span><br>'.'<span>';
                return $info;
            })
            ->addColumn('total_affiliate_bonus', function($data) {
                $bonus =  number_format($data->total_affiliate_bonus, 0, ',', ',');
                $info = '<span>'.  $bonus  .'</span><br>'.'<span>';
                return $info;
            })
            ->addColumn('revenue_total_sales', function($data) use ($config) {
                $data->revenue_total_sales =  number_format($data->revenue_total_sales, 0, ',', ','). " đ";
                $info = '<span>'.  $data->revenue_total_sales  .'</span><br>'.'<span>';
                return $info;
            })
            ->addColumn('total_bonus', function($data) use ($config) {
                $data->total_bonus =  number_format($data->total_bonus, 0, ',', ','). " đ";
                $info = '<span>'.  $data->total_bonus  .'</span><br>'.'<span>';
                return $info;
            })
            ->addColumn('vat', function($data) {
                $info = '<span>'. $data->vat .'%</span><br>'.'<span>';
                return $info;
            })
            ->rawColumns([
                'kol_info',
                'total_user',
                'total_order',
                'total_new_shop',
                'total_amount',
                'total_affiliate_member',
                'total_affiliate_bonus',
                'total_order_user_new',
                'total_amount_user_new',
                'total_order_user_exits',
                'total_amount_user_exits',
                'revenue_total_sales',
                'kol_consumer_bonus_rate',
                'bonus',
                'vat',
                'total_bonus'
            ])
            ->toJson(); //--- Returning Json Data To Client Side
    }

    public function checkUser()
    {
        $user =  User::select('id')
        ->where('is_vendor', '=', static::IS_VENDOR)
        ->where(function($query)  {
            $query->where('kol', '=', static::KOL)
            ->orWhere('special_kol', '=', static::SPECIAL_KOL)
            ->orWhere('preferred', '=', static::PREFERRED);
        })->pluck('id')->toArray();

        return  $user;
    }

    public function exportKOLConsumerBonusForUser($from)
    {
        $datas = app('App\Http\Controllers\Admin\ApiUserController')->kolConsumerBonusDataForUser($from);
        $config = KolConfig::where('kol_date', '=', $from)->first();

        if ($datas->count()>0) {

            foreach($datas as $data){
                $total_bonus = $l1 = $l2 = 0;

                if ($data->special_kol == 1) {
                    $data->special_kol = 'yes';
                } else {
                    $data->special_kol = '';
                }

                if ($data->total_order < $config->number_orders_l1) {
                    $l1 +=1;
                }
                if ($data->total_order < $config->number_orders_l2) {
                    $l2 +=1;
                }
                if ($data->total_user < $config->number_users_l1) {
                    $l1 +=1;
                }
                if ($data->total_user < $config->number_users_l2) {
                    $l2 +=1;
                }
                if ($data->total_new_shop < $config->total_shops_l1) {
                    $l1 +=1;
                }
                if ($data->total_new_shop < $config->total_shops_l2) {
                    $l2 +=1;
                }
                if ($data->total_affiliate_member < $config->number_affiliate_member_l1) {
                    $l1 +=1;
                }
                if ($data->total_affiliate_member < $config->number_affiliate_member_l2) {
                    $l2 +=1;
                }

                if ($data->total_new_shop == 0) {
                    $data->total_new_shop = '0';
                }

                if ($data->total_affiliate_member == 0) {
                    $data->total_affiliate_member = '0';
                }

                if ($data->total_order_user_new == 0) {
                    $data->total_order_user_new = '0';
                }

                if ($data->total_amount_user_new == 0) {
                    $data->total_amount_user_new = '0';
                }

                if ($data->total_order_user_exits == 0) {
                    $data->total_order_user_exits = '0';
                }

                if ($data->total_amount_user_exits == 0) {
                    $data->total_amount_user_exits = '0';
                }

                if ($data->total_amount == 0) {
                    $data->total_amount = '0';
                }
                if ($data->total_affiliate_bonus == 0) {
                    $data->total_affiliate_bonus = '0';
                } else {
                    $total_bonus += $data->total_affiliate_bonus;
                }


                if ($data->special_kol == 'yes') {
                    $data->con_bonus = $config->con_bonus_l1."%";
                } elseif ($data->revenue_total_sales >  $config->revenue_l1) {
                    $data->con_bonus = $config->con_bonus_l1."%";
                } elseif ($data->revenue_total_sales >  $config->revenue_l2 && $data->revenue_total_sales < $config->revenue_l1) {
                    $data->con_bonus = $config->con_bonus_l2."%";
                } else {
                    if ($l1 === 0) {
                        $data->con_bonus = $config->con_bonus_l1."%";
                    } elseif ($l2 === 0) {
                        $data->con_bonus = $config->con_bonus_l2."%";
                    } else {
                        $data->con_bonus = '0%';
                    }
                }

                $bonus = 0;
                if ($data->special_kol == 'yes') {
                    $bonus =  ($config->con_bonus_l1 *  $data->total_amount) / 100;
                } elseif ($data->revenue_total_sales >  $config->revenue_l1) {
                    $bonus =  ($config->con_bonus_l1 *  $data->total_amount) / 100;
                } elseif ($data->revenue_total_sales >  $config->revenue_l2 && $data->revenue_total_sales < $config->revenue_l1) {
                    $bonus =  ($config->con_bonus_l2 *  $data->total_amount) / 100;
                } else {
                     if ($l1 === 0) {
                        $bonus =  ($config->con_bonus_l1 *  $data->total_amount) / 100;
                    } elseif ($l2 === 0) {
                        $bonus =  ($config->con_bonus_l2 *  $data->total_amount) / 100;
                    } else {
                        $bonus = 0;
                    }
                }
                $total_bonus += $bonus;
                $data->bonus =  number_format($bonus, 0, ',', ',') . " đ";

                
                if ($total_bonus >= static::VAT) {
                    $data->vat = '10%';
                    $data->total_bonus = $total_bonus - (($total_bonus * 10) / 100);
                } else {
                    $data->vat = '0%';
                }

                $data->total_bonus =  number_format($total_bonus, 0, ',', ',') . " đ";

                $data->revenue_total_sales =  number_format($data->revenue_total_sales, 0, ',', ',') . " đ";
            }
        }
        $file_name = 'order_kol_consumer_bonus_for_'.$from.'___'.Carbon::now()->format('Y-m-d H:m:s').'.xlsx';
        return Excel::download(new KOLConsumerBonusForUser($datas, $config), $file_name, null, []);
    }

}
