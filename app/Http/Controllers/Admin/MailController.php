<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Mail;

class MailController extends Controller
{
    public function index(){
        return view("admin.email.sendmail");
    }
    public function send(Request $request){

        if($request->select == 'default'){
            $request->validate([
                'subject'=>'required',
                'email'=>'required',
                'body'=>'required'
                ]
            );
            $arrto = explode(',',$request->email);

            foreach($arrto as $item){
                $email_data = [
                    'recipient'=> $item,
                    'to' => $request->email,
                    'subject'=>$request->subject,
                    'body'=>$request->body
                ];

             $a =    Mail::send(['html'=>'admin.email.template-sendmail'],$email_data,function($message) use ($email_data){
                    $message->to($email_data['recipient'])
                    ->subject($email_data['subject']);
                });
            dd( $a);
            }
            return redirect('admin/admin-send-mail')->with('success', 'Gửi thành công !!!!');
        }
        else{
            $request->validate([
                'subject'=>'required',
                'body'=>'required'
                ]
            );
            $listEmailVendor = DB::table('users')->where('is_vendor',2)->select('email')->get()->toArray();
            foreach($listEmailVendor as $item){
                $email_data = [
                        'recipient'=> $item->email,
                        'to' => $item->email,
                        'subject'=>$request->subject,
                        'body'=>$request->body
                    ];
                    Mail::send(['html'=>'admin.email.template-sendmail'],$email_data,function($message) use ($email_data){
                            $message->to($email_data['recipient'])
                            ->subject($email_data['subject']);
                        });
                    }
            return redirect('admin/admin-send-mail')->with('success','Gửi thành công !!!!');
        }
    }

}
