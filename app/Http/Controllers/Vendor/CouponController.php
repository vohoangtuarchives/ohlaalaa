<?php

namespace App\Http\Controllers\Vendor;

use Validator;
use Datatables;
use Carbon\Carbon;
use App\Models\CouponVendor;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class CouponController extends Controller
{
   public function __construct()
    {
        $this->middleware('auth');
    }

    //*** JSON Request
    public function datatables()
    {
        $user = Auth::user();
        $datas = CouponVendor::where('vendor_id', '=', $user->id)->orderBy('id','desc')->get();
        //--- Integrating This Collection Into Datatables
        return Datatables::of($datas)
                        ->editColumn('type', function(CouponVendor $data) {
                            $type = $data->type == 0 ? "Discount By Percentage" : "Discount By Amount";
                            return $type;
                        })
                        ->editColumn('price', function(CouponVendor $data) {
                            $price = $data->type == 0 ? $data->price.'%' : $data->price.'$';
                            return $price;
                        })
                        ->editColumn('code', function(CouponVendor $data) {
                            return '<div style="color:red;">'.$data->code.'</div>';
                        })
                        ->addColumn('status', function(CouponVendor $data) {
                            $class = $data->status == 1 ? 'drop-success' : 'drop-danger';
                            $s = $data->status == 1 ? 'selected' : '';
                            $ns = $data->status == 0 ? 'selected' : '';
                            return '<div class="action-list"><select class="process select droplinks '.$class.'"><option data-val="1" value="'. route('vendor-coupon-status',['id1' => $data->id, 'id2' => 1]).'" '.$s.'>Activated</option><<option data-val="0" value="'. route('vendor-coupon-status',['id1' => $data->id, 'id2' => 0]).'" '.$ns.'>Deactivated</option>/select></div>';
                        })
                        ->addColumn('action', function(CouponVendor $data) {
                            return '<div class="action-list"><a href="' . route('vendor-coupon-edit',$data->id) . '"> <i class="fas fa-edit"></i>Edit</a><a href="javascript:;" data-href="' . route('vendor-coupon-delete',$data->id) . '" data-toggle="modal" data-target="#confirm-delete" class="delete"><i class="fas fa-trash-alt"></i></a></div>';
                        })
                        ->rawColumns(['status','action', 'code'])
                        ->toJson(); //--- Returning Json Data To Client Side
    }

    //*** GET Request
    public function index()
    {
        return view('vendor.coupon.index');
    }

    //*** GET Request
    public function create()
    {
        return view('vendor.coupon.create');
    }

    //*** POST Request
    public function store(Request $request)
    {
        //--- Validation Section
        $rules = ['code' => 'unique:coupons'];
        $customs = ['code.unique' => 'This code has already been taken.'];
        $validator = Validator::make($request->all(), $rules, $customs);

        $user = Auth::user();

        if ($validator->fails()) {
          return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }
        //--- Validation Section Ends

        //--- Logic Section
        try{
            $data = new CouponVendor();
            $input = $request->all();
            $input['start_date'] = Carbon::parse($input['start_date'])->format('Y-m-d');
            $input['end_date'] = Carbon::parse($input['end_date'])->format('Y-m-d');
            $input['vendor_id'] = $user->id;
            $input['create_by'] = $user->id;
            $data->fill($input)->save();
        }
        catch (\Exception $e){
            return response()->json(array('errors' => [$e->getMessage()]));
       }

        //--- Logic Section Ends

        //--- Redirect Section
        $msg = 'New Data Added Successfully.'.'<a href="'.route("vendor-coupon-index").'"> View Coupon Lists</a>';
        return response()->json($msg);
        //--- Redirect Section Ends
    }

    //*** GET Request
    public function edit($id)
    {
        $data = CouponVendor::findOrFail($id);
        return view('vendor.coupon.edit',compact('data'));
    }

    //*** POST Request
    public function update(Request $request, $id)
    {
        //--- Validation Section

        $rules = ['code' => 'unique:coupons,code,'.$id];
        $customs = ['code.unique' => 'This code has already been taken.'];
        $validator = Validator::make($request->all(), $rules, $customs);

        if ($validator->fails()) {
          return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }
        //--- Validation Section Ends

        //--- Logic Section
        $data = CouponVendor::findOrFail($id);
        $input = $request->all();
        $input['start_date'] = Carbon::parse($input['start_date'])->format('Y-m-d');
        $input['end_date'] = Carbon::parse($input['end_date'])->format('Y-m-d');
        $data->update($input);
        //--- Logic Section Ends

        //--- Redirect Section
        $msg = 'Data Updated Successfully.'.'<a href="'.route("vendor-coupon-index").'"> View Coupon Lists</a>';
        return response()->json($msg);
        //--- Redirect Section Ends
    }

      //*** GET Request Status
    public function status($id1,$id2)
    {
        $data = CouponVendor::findOrFail($id1);
        $data->status = $id2;
        $data->update();
    }

    //*** GET Request Delete
    public function destroy($id)
    {
        $data = CouponVendor::findOrFail($id);
        $data->delete();
        //--- Redirect Section
        $msg = 'Data Deleted Successfully.';
        return response()->json($msg);
        //--- Redirect Section Ends
    }
}
