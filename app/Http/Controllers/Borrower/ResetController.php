<?php

namespace App\Http\Controllers\Borrower;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Borrower;
use App\BorrowerDetails;
use App\Brw_rekening;
use App\Brw_pendanaan;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\ResetEmail;

class ResetController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function sendEmail(Request $request){
        $email = $request->email;
        $dataUser = Borrower::where('email',$email)->first();
        $cekUser = Borrower::where('email',$email)->count();

        if ($cekUser > 0)
        {
            Mail::to($email)->send(new ResetEmail($dataUser));
            $response = ['status' => '00', 'msg' => 'Berhasil Kirim'];
        }
        else
        {
            $response = ['status' => '01', 'msg' => 'Gagal Kirim'];
        }
        
        
        return response()->json($response);
    }

    public function changePassword(Request $request){
        $id = $request->aidi;
        $password = $request->password;

        $update = Borrower::where('brw_id', $id)->update(['password' => Hash::make($password)]);

        if($update){
            $response = ['status' => '00'];
        }else{
            $response = ['status' => '01'];
        }
        
        return response()->json($response);
    }
   
}
