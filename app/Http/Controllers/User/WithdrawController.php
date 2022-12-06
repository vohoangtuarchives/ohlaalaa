<?php

namespace App\Http\Controllers\User;

use Auth;
use Validator;
use App\Models\User;
use App\Models\Currency;
use App\Models\Withdraw;
use Illuminate\Http\Request;
use App\Models\Generalsetting;
use Datatables;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class WithdrawController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:web');
    }

  	public function index()
    {
        $withdraws = Withdraw::where('user_id','=',Auth::guard('web')->user()->id)->where('type','=','user')->orderBy('id','desc')->get();
        $sign = Currency::where('is_default','=',1)->first();
        return view('user.withdraw.index',compact('withdraws','sign'));
    }

    public function affilate_code()
    {
        $user = Auth::guard('web')->user();
        return view('user.withdraw.affilate-code',compact('user'));
    }

    public function affilate_members()
    {
        $user = Auth::guard('web')->user();
        return view('user.withdraw.affiliate-members',compact('user'));
    }

    public function affilate_tree()
    {
        $user = Auth::guard('web')->user();
        return view('user.withdraw.affiliate-tree',compact('user'));
    }

    public function affilate_members_datatable($from = null, $to = null)
    {
        $user = Auth::guard('web')->user();
        $members = null;
        if($from == null){
            $members = DB::table('users')
                ->where('referral_user_id','=',$user->id);
        }
        else{
            $nDays = 1;
            $to = date("Y-m-d",strtotime($to . '+ '.$nDays.'days'));
            $members = DB::table('users')
                ->where('referral_user_id','=',$user->id)
                ->whereBetween('created_at',[$from, $to]);
        }

        $result = DB::query()->fromSub($members, 't')
            ->leftJoin('provinces as p', 't.CityID', '=', 'p.id')
            ->leftJoin('districts as d', 't.DistrictID', '=', 'd.id')
            ->leftJoin('wards as w', 't.ward_id', '=', 'w.id')
            ->orderBy('t.id','desc')
            ->select('t.name', 't.photo', 't.email', 't.phone',
                DB::raw('DATE_FORMAT(t.created_at,"%d-%m-%y") as created_at'),
                't.status',
                DB::raw('CONCAT(t.address, ", " , w.name, ", " , d.name, ", " , p.name) AS address')
            )
            ->get();

        return Datatables::of($result)
            ->addColumn('action', function($data) {
                return '';
            })
            ->rawColumns(['action'])
            ->toJson();
    }

    public function create()
    {
        $sign = Currency::where('is_default','=',1)->first();
        return view('user.withdraw.withdraw' ,compact('sign'));
    }

    public function store(Request $request)
    {

        $from = User::findOrFail(Auth::guard('web')->user()->id);
        $curr = Currency::where('is_default','=',1)->first();
        $withdrawcharge = Generalsetting::findOrFail(1);
        $charge = $withdrawcharge->withdraw_fee;

        if($request->amount > 0){

            $amount = $request->amount;
            $amount = round(($amount / $curr->value),2);
            if ($from->affilate_income >= $amount){
                $fee = (($withdrawcharge->withdraw_charge / 100) * $amount) + $charge;
                $finalamount = $amount + $fee;
                if ($from->affilate_income >= $finalamount){

                $from->affilate_income = $from->affilate_income - $finalamount;
                $from->update();

                $finalamount = number_format((float)$finalamount,2,'.','');
                $newwithdraw = new Withdraw();
                $newwithdraw['user_id'] = Auth::guard('web')->user()->id;
                $newwithdraw['method'] = $request->methods;
                $newwithdraw['acc_email'] = $request->acc_email;
                $newwithdraw['iban'] = $request->iban;
                $newwithdraw['country'] = $request->acc_country;
                $newwithdraw['acc_name'] = $request->acc_name;
                $newwithdraw['address'] = $request->address;
                $newwithdraw['swift'] = $request->swift;
                $newwithdraw['reference'] = $request->reference;
                $newwithdraw['amount'] = $finalamount;
                $newwithdraw['fee'] = $fee;
                $newwithdraw['type'] = 'user';
                $newwithdraw->save();

                return response()->json('Withdraw Request Sent Successfully.');
            }else{
                return response()->json(array('errors' => [ 0 => 'Insufficient Balance.' ]));
            }
            }else{
                return response()->json(array('errors' => [ 0 => 'Insufficient Balance.' ]));
            }
        }
        return response()->json(array('errors' => [ 0 => 'Please enter a valid amount.' ]));

    }
}
