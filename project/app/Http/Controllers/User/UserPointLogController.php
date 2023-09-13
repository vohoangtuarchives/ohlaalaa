<?php

namespace App\Http\Controllers\User;

use Datatables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class UserPointLogController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(){
        return view('user.userpointlog.index');
    }

    public function datatable($type, $from = null, $to = null){
        $types = array($type);
        $user = Auth::guard('web')->user();
        if($type=='all'){
            $types = array('Daily Convert',
                'Rebate Bonus',
                'Affiliate Bonus',
                'Merchant Sales Bonus',
                'Use Shopping',
                'Order Declined',
                'Global Transfer',
                'Order Completed',
                'Order - Shop Declined',
                'Buying Package Bonus',
                'KOL Consumer Bonus',
                'KOL Affiliate Bonus',
                'Admin Transfer Point'
            );
        }

        $point_logs = null;
        if($from == null){
            $point_logs = DB::table('user_point_logs')
                ->where('user_id','=',$user->id)
                ->whereIn('log_type',$types);
        }
        else{
            $nDays = 1;
            $to = date("Y-m-d",strtotime($to . '+ '.$nDays.'days'));
            $point_logs = DB::table('user_point_logs')
                ->where('user_id','=',$user->id)
                ->whereIn('log_type',$types)
                ->whereBetween('created_at',[$from, $to]);
        }

        $result = DB::query()->fromSub($point_logs, 't')
            ->select('t.*',
                DB::raw('DATE_FORMAT(t.created_at,"%d-%m-%y") as created_at1'))
            ->orderBy('t.id','desc')
            ->get();

        return Datatables::of($result)
            ->editColumn('reward_point', function($data) {
                return number_format($data->reward_point);
            })
            ->editColumn('shopping_point', function($data) {
                return number_format($data->shopping_point);
            })
            ->editColumn('amount_bonus', function($data) {
                return number_format($data->amount_bonus);
            })
            ->editColumn('exchange_rate', function($data) {
                return $data->exchange_rate.'%';
            })
            ->addColumn('action', function($data) {
                return $data->order_ref_id > 0 ? '<a href="' . route('user-order', $data->order_ref_id) . '" > <i class="fas fa-eye"></i> Details</a>' : '';
            })
            ->rawColumns(['action'])
            ->toJson();
    }
}
