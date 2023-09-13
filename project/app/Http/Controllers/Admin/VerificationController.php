<?php

namespace App\Http\Controllers\Admin;

use Auth;
use Datatables;
use Carbon\Carbon;
use App\Models\Verification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Users\VendorVerification;

class VerificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    //*** JSON Request
    public function datatables($status, $from = null, $to = null, $email = null)
    {
        $datas = $this->data_result($status, $from, $to, $email)
            ->select("id", "text", "status", "user_id", "approved_at")
            ->get();
        return Datatables::of($datas)
            ->addColumn('name', function($data) {
                return  '<a href="'. route('admin-vendor-show',$data->user_id) .'" target="_blank">'.$data->user->owner_name.'</a>';
            })
            ->addColumn('email', function($data) {
                return  $data->user->email;
            })
            ->editColumn('text', function($data) {
                $details = mb_strlen($data->text,'utf-8') > 250 ? mb_substr($data->text,0,250,'utf-8').'...' : $data->text;
                return  $details;
            })
            ->editColumn('approved_at', function($data) {
                $rs = isset($data->approved_at) ? Carbon::parse($data->approved_at)->format('d-m-Y H:m') : '';
                return $rs;
            })
            ->addColumn('status', function($data) {
                $class = $data->status == 'Pending' ? '' : ($data->status == 'Verified' ? 'drop-success' : 'drop-danger');
                $s = $data->status == 'Verified' ? 'selected' : '';
                $ns = $data->status == 'Declined' ? 'selected' : '';
                return '<div class="action-list"><select class="process select vendor-droplinks '.$class.'">'.
                    '<option value="'. route('admin-vr-st',['id1' => $data->id, 'id2' => 'Pending']).'" '.$s.'>Pending</option>'.
                '<option value="'. route('admin-vr-st',['id1' => $data->id, 'id2' => 'Verified']).'" '.$s.'>Verified</option>'.
                '<option value="'. route('admin-vr-st',['id1' => $data->id, 'id2' => 'Declined']).'" '.$ns.'>Declined</option></select></div>';
            })
            ->addColumn('action', function($data) {
                return '<div class="action-list"><a href="javascript:;" class="set-gallery" data-toggle="modal" data-target="#setgallery"><input type="hidden" value="'.$data->id.'"><i class="fas fa-paperclip"></i> View Attachments</a><a href="javascript:;" data-href="' . route('admin-vr-delete',$data->id) . '" data-toggle="modal" data-target="#confirm-delete" class="delete"><i class="fas fa-trash-alt"></i></a></div>';
            })
            ->rawColumns(['name','status','action'])
            ->toJson(); //--- Returning Json Data To Client Side
    }

    public function data_result($status, $from = null, $to = null, $email = null)
    {
        $statuss=array($status);
        if($status=='all'){
            $statuss = array('Pending','Verified','Declined');
        }

        $datas = Verification::whereIn('verifications.status',$statuss);
        if($email != null){
            $datas = $datas->whereExists(function ($q) use ($email) {
                $q->select(DB::raw(1))
                    ->from('users')
                    ->whereColumn('users.id', 'verifications.user_id')
                    ->where('users.email', 'like', '%'.$email.'%');
            });
        }

        if($from != null){
            $nDays = 1;
            $to = date("Y-m-d",strtotime($to . '+ '.$nDays.'days'));
            $datas = $datas
                ->whereBetween('approved_at',[$from, $to]);
        }
        return $datas;
    }

    public function export($status, $from = null, $to = null)
    {
        $datas = $this->data_result($status, $from, $to)
            ->leftJoin('users as vendor', 'vendor.id', '=', 'verifications.user_id')
            ->select("vendor.owner_name as name", "vendor.email", "verifications.text", "verifications.status", DB::raw('DATE_FORMAT(approved_at,"%Y-%m-%d") as approved_at'))->get();
        $file_name = 'vendor-verification_'.Carbon::now()->format('Y-m-d H:m:s').'.xlsx';
        return Excel::download(new VendorVerification($datas), $file_name, null, []);
    }

    public function index()
    {
        $now = Carbon::now()->format('Y-m-d');
        return view('admin.verify.index', compact('now'));
    }

    public function pending()
    {
        return view('admin.verify.pending');
    }

    public function show()
    {
        $data[0] = 0;
        $id = $_GET['id'];
        $prod1 = Verification::findOrFail($id);
        $prod = explode(',', $prod1->attachments);
        if(count($prod))
        {
            $data[0] = 1;
            $data[1] = $prod;
            $data[2] = $prod1->text;
            $data[3] = ''.route('admin-vr-st',['id1' => $prod1->id, 'id2' => 'Verified']).'';
            $data[4] = ''.route('admin-vr-st',['id1' => $prod1->id, 'id2' => 'Declined']).'';
        }
        return response()->json($data);
    }


    public function edit($id)
    {
        $data = Order::find($id);
        return view('admin.order.delivery',compact('data'));
    }


    //*** POST Request
    public function update(Request $request, $id)
    {
        //--- Logic Section
        $data = Order::findOrFail($id);

        $input = $request->all();


        // Then Save Without Changing it.
        $input['status'] = "completed";
        $data->update($input);
        //--- Logic Section Ends


        //--- Redirect Section
        $msg = 'Status Updated Successfully.';
        return response()->json($msg);
        //--- Redirect Section Ends

    }


    //*** GET Request
    public function status($id1,$id2)
    {
        $ve = Verification::findOrFail($id1);
        if($id2 == 'Verified'){
            $ve->approved_at = date("Y-m-d H:m:s");
            $ve->approved_by = Auth::guard('admin')->user()->id;
            $user = $ve->user;
            $user->verified_at = $ve->approved_at;
            $user->save();
        }
        $ve->status = $id2;
        $ve->update();
        //--- Redirect Section
        $msg[0] = 'Status Updated Successfully.';
        return response()->json($msg);
        //--- Redirect Section Ends

    }


    //*** GET Request
    public function destroy($id)
    {
        $data = Verification::findOrFail($id);
        $photos =  explode(',',$data->attachments);
        foreach($photos as $photo){
            unlink(public_path().'/assets/images/attachments/'.$photo);
        }
        $data->delete();
        //--- Redirect Section
        $msg = 'Data Deleted Successfully.';
        return response()->json($msg);
        //--- Redirect Section Ends

    }






}
