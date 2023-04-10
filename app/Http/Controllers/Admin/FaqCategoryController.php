<?php

namespace App\Http\Controllers\Admin;

use Datatables;
use App\Models\FaqCategory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Validator;

class FaqCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    //*** JSON Request
    public function datatables()
    {
        $datas = FaqCategory::orderBy('id','desc')->get();
        //--- Integrating This Collection Into Datatables
        return Datatables::of($datas)
            ->addColumn('action', function(FaqCategory $data) {
                return '<div class="action-list"><a data-href="' . route('admin-faq-category-edit',$data->id) . '" class="edit" data-toggle="modal" data-target="#modal1"> <i class="fas fa-edit"></i>Edit</a><a href="javascript:;" data-href="' . route('admin-faq-category-delete',$data->id) . '" data-toggle="modal" data-target="#confirm-delete" class="delete"><i class="fas fa-trash-alt"></i></a></div>';
            })
            ->toJson();//--- Returning Json Data To Client Side
    }

    //*** GET Request
    public function index()
    {
        return view('admin.faq-category.index');
    }

    //*** GET Request
    public function create()
    {
        return view('admin.faq-category.create');
    }

    //*** POST Request
    public function store(Request $request)
    {
        //--- Validation Section
        $rules = [
            'name' => 'unique:faq_categories',
            'slug' => 'unique:faq_categories'
        ];
        $customs = [
            'name.unique' => 'This name has already been taken.',
            'slug.unique' => 'This slug has already been taken.'
        ];
        $validator = Validator::make($request->all(), $rules, $customs);
        if ($validator->fails()) {
            return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }
        //--- Validation Section Ends

        //--- Logic Section
        $data = new FaqCategory;
        $input = $request->all();
        $data->fill($input)->save();
        //--- Logic Section Ends

        //--- Redirect Section
        $msg = 'New Data Added Successfully.';
        return response()->json($msg);
        //--- Redirect Section Ends
    }

    //*** GET Request
    public function edit($id)
    {
        $data = FaqCategory::findOrFail($id);
        return view('admin.faq-category.edit',compact('data'));
    }

    //*** POST Request
    public function update(Request $request, $id)
    {
        //--- Validation Section
        $rules = [
            'name' => 'unique:faq_categories,name,'.$id,
            'slug' => 'unique:faq_categories,slug,'.$id
        ];
        $customs = [
            'name.unique' => 'This name has already been taken.',
            'slug.unique' => 'This slug has already been taken.'
        ];
        $validator = Validator::make($request->all(), $rules, $customs);
        if ($validator->fails()) {
            return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }
        //--- Validation Section Ends

        //--- Logic Section
        $data = FaqCategory::findOrFail($id);
        $input = $request->all();
        $data->update($input);
        //--- Logic Section Ends

        //--- Redirect Section
        $msg = 'Data Updated Successfully.';
        return response()->json($msg);
        //--- Redirect Section Ends

    }

    //*** GET Request
    public function destroy($id)
    {
        $data = FaqCategory::findOrFail($id);

        //--- Check If there any blogs available, If Available Then Delete it
        if($data->faqs->count() > 0)
        {
            foreach ($data->faqs as $element) {
                $element->delete();
            }
        }
        $data->delete();
        //--- Redirect Section
        $msg = 'Data Deleted Successfully.';
        return response()->json($msg);
        //--- Redirect Section Ends
    }
}
