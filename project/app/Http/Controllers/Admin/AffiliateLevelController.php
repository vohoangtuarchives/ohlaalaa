<?php

namespace App\Http\Controllers\Admin;
use Datatables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\AffiliateLevelConfig;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\AffiliateLevelPackageConfig;

class AffiliateLevelController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.affiliatelevel.index');
    }

    public function datatables()
    {
         $datas = AffiliateLevelConfig::orderBy('level','asc')->get();
         //--- Integrating This Collection Into Datatables
         return Datatables::of($datas)
            ->addColumn('action', function($data) {
                $package_list = '<a href="' . route('admin-affiliatelevel-package-index',$data->id) . '" class="edit"> <i class="fas fa-edit"></i>Packages</a>';
                return '<div class="action-list">'.$package_list.'<a data-href="' . route('admin-affiliatelevel-edit',$data->id) . '" class="edit" data-toggle="modal" data-target="#modal1"> <i class="fas fa-edit"></i>Edit</a><a href="javascript:;" data-href="' . route('admin-affiliatelevel-delete',$data->id) . '" data-toggle="modal" data-target="#confirm-delete" class="delete"><i class="fas fa-trash-alt"></i></a></div>';
            })
            ->toJson();//--- Returning Json Data To Client Side
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.affiliatelevel.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $rules = [
            'name' => 'unique:affiliate_level_configs',
            'level' => 'unique:affiliate_level_configs'
             ];
        $customs = [
            'name.unique' => 'This name has already been taken.',
            'level.unique' => 'This level has already been taken.',
                ];
        // return response()->json('passed!');
        $validator = Validator::make($request->all(), $rules, $customs);
        if ($validator->fails()) {
            return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }
        $data = new AffiliateLevelConfig;
        $input = $request->all();
        $data->fill($input)->save();
        $msg = 'New Data Added Successfully.';
        return response()->json($msg);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $data = AffiliateLevelConfig::findOrFail($id);
        return view('admin.affiliatelevel.edit',compact('data'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $rules = [
            'name' => 'unique:affiliate_level_configs,name,'.$id,
            'level' => 'unique:affiliate_level_configs,level,'.$id
             ];
        $customs = [
            'name.unique' => 'This name has already been taken.',
            'level.unique' => 'This level has already been taken.',
            ];
        $validator = Validator::make($request->all(), $rules, $customs);
        if ($validator->fails()) {
            return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }
        $data = AffiliateLevelConfig::findOrFail($id);
        $input = $request->all();
        $data->update($input);
        $msg = 'Data Updated Successfully.';
        return response()->json($msg);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $data = AffiliateLevelConfig::findOrFail($id);
        $data->delete();
        //--- Redirect Section
        $msg = 'Data Deleted Successfully.';
        return response()->json($msg);
        //--- Redirect Section Ends
    }

    public function package_index($id)
    {
        $data = AffiliateLevelConfig::findOrFail($id);
        return view('admin.affiliatelevelpackage.index',compact('data'));
    }

    public function package_datatables($id)
    {
         $datas = DB::table('affiliate_level_package_configs as t')
         ->where('affiliate_level_id','=',$id)
         ->join('package_configs as t1', 't.package_config_id', '=', 't1.id')
         ->select(
            't.*',
            't1.name'
         )
         ->orderBy('t1.sort_index','asc')
         ->get();
         return Datatables::of($datas)
            ->editColumn('affiliate_bonus', function($data) {
                return $data->affiliate_bonus.' %';
            })
            ->addColumn('action', function($data) {
                return '<div class="action-list"><a data-href="' . route('admin-affiliatelevel-package-edit',$data->id) . '" class="edit" data-toggle="modal" data-target="#modal1"> <i class="fas fa-edit"></i>Edit</a></div>';
            })
            ->toJson();
    }

    public function package_edit($id)
    {
        $data = AffiliateLevelPackageConfig::findOrFail($id);
        return view('admin.affiliatelevelpackage.edit',compact('data'));
    }

    public function package_update(Request $request, $id)
    {
        $data = AffiliateLevelPackageConfig::findOrFail($id);
        $input = $request->all();
        $input['last_update_by'] = Auth::guard('admin')->user()->id;
        $data->update($input);
        $msg = 'Data Updated Successfully.';
        return response()->json($msg);
    }
}
