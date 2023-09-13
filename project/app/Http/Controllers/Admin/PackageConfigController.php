<?php

namespace App\Http\Controllers\Admin;

use Datatables;
use Illuminate\Http\Request;
use App\Models\PackageConfig;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\AffiliateLevelPackageConfig;

class PackageConfigController extends Controller
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
        return view('admin.packageconfig.index');
    }

    public function datatables()
    {
         $datas = PackageConfig::orderBy('sort_index','asc')->get();
         //--- Integrating This Collection Into Datatables
         return Datatables::of($datas)
            ->editColumn('bonus_sp', function($data) {
                return number_format($data->bonus_sp);
            })
            ->editColumn('bonus_rp', function($data) {
                return number_format($data->bonus_rp);
            })
            ->editColumn('rebate_bonus', function($data) {
                return $data->rebate_bonus.' %';
            })
            ->editColumn('price', function($data) {
                return number_format($data->price);
            })
            ->editColumn('allow_buy', function($data) {
                return $data->allow_buy == 1 ? 'Yes' : 'No';
            })
            ->addColumn('action', function(PackageConfig $data) {
                return '<div class="action-list"><a data-href="' . route('admin-packageconfig-edit',$data->id) . '" class="edit" data-toggle="modal" data-target="#modal1"> <i class="fas fa-edit"></i>Edit</a><a href="javascript:;" data-href="' . route('admin-packageconfig-delete',$data->id) . '" data-toggle="modal" data-target="#confirm-delete" class="delete"><i class="fas fa-trash-alt"></i></a></div>';
            })
            ->toJson();//--- Returning Json Data To Client Side
    }

    public function create()
    {
        return view('admin.packageconfig.create');
    }

    public function store(Request $request)
    {
        $rules = [
            'name' => 'unique:package_configs',
            'sort_index' => 'unique:package_configs'
             ];
        $customs = [
            'name.unique' => 'This name has already been taken.',
            'sort_index.unique' => 'This sort index has already been taken.',
                ];
        // return response()->json('passed!');
        $validator = Validator::make($request->all(), $rules, $customs);
        if ($validator->fails()) {
            return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }
        $data = new PackageConfig;
        $input = $request->all();
        $input['last_update_by'] = Auth::guard('admin')->user()->id;
        $data->fill($input)->save();
        $this->create_affiliate_level_package($data->id);
        $msg = 'New Data Added Successfully.';
        return response()->json($msg);
    }

    public function edit($id)
    {
        $data = PackageConfig::findOrFail($id);
        return view('admin.packageconfig.edit',compact('data'));
    }

    public function update(Request $request, $id)
    {
        $rules = [
            'name' => 'unique:package_configs,name,'.$id,
            'sort_index' => 'unique:package_configs,sort_index,'.$id
             ];
        $customs = [
            'name.unique' => 'This name has already been taken.',
            'sort_index.unique' => 'This sort index has already been taken.',
            ];
        $validator = Validator::make($request->all(), $rules, $customs);
        if ($validator->fails()) {
            return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }
        $data = PackageConfig::findOrFail($id);
        $input = $request->all();
        $input['last_update_by'] = Auth::guard('admin')->user()->id;
        $data->update($input);
        $this->create_affiliate_level_package($id);
        $msg = 'Data Updated Successfully.';
        return response()->json($msg);
    }

    public function destroy($id)
    {
        $data = PackageConfig::findOrFail($id);
        $data->delete();
        //--- Redirect Section
        $msg = 'Data Deleted Successfully.';
        return response()->json($msg);
        //--- Redirect Section Ends
    }

    public function create_affiliate_level_package($id){
        $levels = DB::table('affiliate_level_configs')
            ->whereNotExists(function ($query) use($id) {
                $query->select(DB::raw(1))
                    ->from('affiliate_level_package_configs')
                    ->where('affiliate_level_package_configs.package_config_id','=', $id)
                    ->whereColumn('affiliate_level_package_configs.affiliate_level_id', 'affiliate_level_configs.id');
            })
            ->get();

        foreach($levels as $lv){
            $config = new AffiliateLevelPackageConfig;
            $config->affiliate_level_id = $lv->id;
            $config->package_config_id = $id;
            $config->affiliate_bonus = 0;
            $config->last_update_by = Auth::guard('admin')->user()->id;
            $config->save();
        }
        return true;
    }

    public function register_index()
    {
        return view('admin.packageconfig.register');
    }
}
