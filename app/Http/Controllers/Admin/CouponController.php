<?php

namespace App\Http\Controllers\Admin;

use Validator;
use Datatables;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Coupon;
use App\Models\CouponVendor;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CouponController extends Controller
{
   public function __construct()
    {
        $this->middleware('auth:admin');
    }

    //*** JSON Request
    public function datatables()
    {
         $datas = Coupon::orderBy('id','desc')->get();
         //--- Integrating This Collection Into Datatables
         return Datatables::of($datas)
                            ->editColumn('type', function(Coupon $data) {
                                $type = $data->type == 0 ? "Discount By Percentage" : "Discount By Amount";
                                return $type;
                            })
                            ->editColumn('price', function(Coupon $data) {
                                $curr = null;
                                if (Session::has('df_currency')) {
                                    $curr = Session::get('df_currency');
                                }
                                $price = $data->type == 0 ? $data->price.'%' : $data->price.($curr != null ? $curr->sign : '');
                                return $price;
                            })
                            ->addColumn('status', function(Coupon $data) {
                                $class = $data->status == 1 ? 'drop-success' : 'drop-danger';
                                $s = $data->status == 1 ? 'selected' : '';
                                $ns = $data->status == 0 ? 'selected' : '';
                                return '<div class="action-list"><select class="process select droplinks '.$class.'"><option data-val="1" value="'. route('admin-coupon-status',['id1' => $data->id, 'id2' => 1]).'" '.$s.'>Activated</option><<option data-val="0" value="'. route('admin-coupon-status',['id1' => $data->id, 'id2' => 0]).'" '.$ns.'>Deactivated</option>/select></div>';
                            })
                            ->addColumn('action', function(Coupon $data) {
                                return '<div class="action-list"><a href="' . route('admin-coupon-edit',$data->id) . '"> <i class="fas fa-edit"></i>Edit</a><a href="javascript:;" data-href="' . route('admin-coupon-delete',$data->id) . '" data-toggle="modal" data-target="#confirm-delete" class="delete"><i class="fas fa-trash-alt"></i></a></div>';
                            })
                            ->rawColumns(['status','action'])
                            ->toJson(); //--- Returning Json Data To Client Side
    }

    //*** GET Request
    public function index()
    {
        return view('admin.coupon.index');
    }

    //*** GET Request
    public function create()
    {
        return view('admin.coupon.create');
    }

    //*** POST Request
    public function store(Request $request)
    {
        //--- Validation Section
        $rules = ['code' => 'unique:coupons'];
        $customs = ['code.unique' => 'This code has already been taken.'];
        $validator = Validator::make($request->all(), $rules, $customs);

        if ($validator->fails()) {
          return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }
        //--- Validation Section Ends

        //--- Logic Section
        $data = new Coupon();
        $input = $request->all();
        $input['start_date'] = Carbon::parse($input['start_date'])->format('Y-m-d');
        $input['end_date'] = Carbon::parse($input['end_date'])->format('Y-m-d');
        $data->fill($input)->save();
        //--- Logic Section Ends

        //--- Redirect Section
        $msg = 'New Data Added Successfully.'.'<a href="'.route("admin-coupon-index").'">View Coupon Lists</a>';
        return response()->json($msg);
        //--- Redirect Section Ends
    }

    //*** GET Request
    public function edit($id)
    {
        $data = Coupon::findOrFail($id);
        return view('admin.coupon.edit',compact('data'));
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
        $data = Coupon::findOrFail($id);
        $input = $request->all();
        $input['start_date'] = Carbon::parse($input['start_date'])->format('Y-m-d');
        $input['end_date'] = Carbon::parse($input['end_date'])->format('Y-m-d');
        $data->update($input);
        //--- Logic Section Ends

        //--- Redirect Section
        $msg = 'Data Updated Successfully.'.'<a href="'.route("admin-coupon-index").'">View Coupon Lists</a>';
        return response()->json($msg);
        //--- Redirect Section Ends
    }
      //*** GET Request Status
      public function status($id1,$id2)
        {
            $data = Coupon::findOrFail($id1);
            $data->status = $id2;
            $data->update();
        }


    //*** GET Request Delete
    public function destroy($id)
    {
        $data = Coupon::findOrFail($id);
        $data->delete();
        //--- Redirect Section
        $msg = 'Data Deleted Successfully.';
        return response()->json($msg);
        //--- Redirect Section Ends
    }

    //vendor coupon
    //*** JSON Request
    public function vendor_datatables()
    {
         $datas = CouponVendor::orderBy('id','desc')->get();
         //--- Integrating This Collection Into Datatables
         return Datatables::of($datas)
                            ->editColumn('type', function(CouponVendor $data) {
                                $type = $data->type == 0 ? "Discount By Percentage" : "Discount By Amount";
                                return $type;
                            })
                            ->editColumn('price', function(CouponVendor $data) {
                                $curr = null;
                                if (Session::has('df_currency')) {
                                    $curr = Session::get('df_currency');
                                }
                                $price = $data->type == 0 ? $data->price.'%' : $data->price.($curr != null ? $curr->sign : '');
                                return $price;
                            })
                            ->editColumn('code', function(CouponVendor $data) {
                                return '<div style="color:red;">'.$data->code.'</div>';
                            })
                            ->addColumn('status', function(CouponVendor $data) {
                                $class = $data->status == 1 ? 'drop-success' : 'drop-danger';
                                $s = $data->status == 1 ? 'selected' : '';
                                $ns = $data->status == 0 ? 'selected' : '';
                                return '<div class="action-list"><select class="process select droplinks '.$class.'"><option data-val="1" value="'. route('admin-coupon-vendor-status',['id1' => $data->id, 'id2' => 1]).'" '.$s.'>Activated</option><<option data-val="0" value="'. route('admin-coupon-vendor-status',['id1' => $data->id, 'id2' => 0]).'" '.$ns.'>Deactivated</option>/select></div>';
                            })
                            ->addColumn('vendor_name', function(CouponVendor $data) {
                                return $data->vendor()->first()->name;

                            })
                            ->addColumn('email', function(CouponVendor $data) {
                                return $data->vendor()->first()->email;

                            })
                            ->addColumn('action', function(CouponVendor $data) {
                                return '<div class="action-list"><a href="' . route('admin-coupon-vendor-edit',$data->id) . '"> <i class="fas fa-edit"></i>Edit</a><a href="javascript:;" data-href="' . route('admin-coupon-vendor-delete',$data->id) . '" data-toggle="modal" data-target="#confirm-delete" class="delete"><i class="fas fa-trash-alt"></i></a></div>';
                            })
                            ->rawColumns(['status','action', 'code'])
                            ->toJson(); //--- Returning Json Data To Client Side
    }

    //*** GET Request
    public function vendor_index()
    {
        return view('admin.coupon_vendor.index');
    }

    //*** GET Request
    public function vendor_create()
    {
        return view('admin.coupon_vendor.create');
    }

    //*** POST Request
    public function vendor_store(Request $request)
    {
        $vendor = User::where('email','=',$request['email'])
            ->where('is_vendor', '=', 2)
            ->first();
        if($vendor == null){
            return response()->json(array('errors' => ['Invalid email! Make sure it is a Vendor Email']));
        }
        //--- Validation Section
        $rules = ['code' => 'unique:coupons'];
        $customs = ['code.unique' => 'This code has already been taken.'];
        $validator = Validator::make($request->all(), $rules, $customs);

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
            $input['vendor_id'] = $vendor->id;
            $input['admin_issuer_id'] = Auth::guard('admin')->user()->id;
            $input['admin_updated_date'] = Carbon::now()->format('Y-m-d H:m:s');
            $data->fill($input)->save();
        }
        catch (\Exception $e){
            return response()->json(array('errors' => [$e->getMessage()]));
       }

        //--- Logic Section Ends

        //--- Redirect Section
        $msg = 'New Data Added Successfully.'.'<a href="'.route("admin-coupon-vendor-index").'">View Coupon Lists</a>';
        return response()->json($msg);
        //--- Redirect Section Ends
    }

    //*** GET Request
    public function vendor_edit($id)
    {
        $data = CouponVendor::findOrFail($id);
        return view('admin.coupon_vendor.edit',compact('data'));
    }

    //*** POST Request
    public function vendor_update(Request $request, $id)
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
        $input['admin_issuer_id'] = Auth::guard('admin')->user()->id;
        $input['admin_updated_date'] = Carbon::now()->format('Y-m-d H:m:s');
        $data->update($input);
        //--- Logic Section Ends

        //--- Redirect Section
        $msg = 'Data Updated Successfully.'.'<a href="'.route("admin-coupon-vendor-index").'">View Coupon Lists</a>';
        return response()->json($msg);
        //--- Redirect Section Ends
    }

      //*** GET Request Status
    public function vendor_status($id1,$id2)
    {
        $data = CouponVendor::findOrFail($id1);
        $data->status = $id2;
        $data->update();
    }


    //*** GET Request Delete
    public function vendor_destroy($id)
    {
        $data = CouponVendor::findOrFail($id);
        $data->delete();
        //--- Redirect Section
        $msg = 'Data Deleted Successfully.';
        return response()->json($msg);
        //--- Redirect Section Ends
    }
}
