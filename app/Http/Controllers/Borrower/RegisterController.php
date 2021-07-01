<?php

namespace App\Http\Controllers\Borrower;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Borrower;
use App\BorrowerDetails;
use App\Brw_rekening;
use App\Brw_pendanaan;
use Illuminate\Http\Request;
use DB;
class RegisterController extends Controller
{

    public function Register(Request $request){

    //    $request->email;
    //    $request->username;
    //    $request->password;

        
        //$borrower = DB::table('brw_users')->where('username',$username)->first();
        $username = Borrower::where('username', $request->username)->first();
        $email = Borrower::where('email', $request->email)->first();
       
        if($username['username'] == $request->username){
            $response = [
                        'status' => 'gagal_username',
                        'message' => 'username atau email anda telah terdaftar',
                        'data_borrower' => "null"
                    ];
                    
                    return response()->json($response, 201);
        }
        if($email['email'] == $request->email){
            $response = [
                        'status' => 'gagal_email',
                        'message' => 'username atau email anda telah terdaftar',
                        'data_borrower' => "null"
                    ];
                    
                    return response()->json($response, 201);
        }else{
            $Borrower = new \App\Borrower();  
            $Borrower->username = $request->username;
            $Borrower->email = $request->email; 
            $Borrower->password = Hash::make($request->password); 
            $Borrower->email_verif = $request->email_verif;   
            $Borrower->status ='Not Active';   
            $Borrower->save();    
                
            $borrowerDetails = BorrowerDetails::where('brw_id', $Borrower->brw_id)->first();
            $plafon = Brw_rekening::where('brw_id',$Borrower->brw_id)->first();
            $pendanaan = Brw_pendanaan::select('brw_pendanaan.*', 'proyek.nama','brw_invoice.tgl_jatuh_tempo')
                ->leftjoin('brw_invoice','brw_pendanaan.id_proyek', '=' ,'brw_invoice.proyek_id')
                ->leftjoin('proyek','brw_pendanaan.id_proyek', '=' ,'proyek.id')
                ->where('brw_pendanaan.brw_id',$Borrower->brw_id)
                ->whereIn('brw_pendanaan.status',array(1,2,3))->get();
            $pnd = array();
            if(empty($pendanaan)){$pnd['nama'] = "tidak ada Proyek";$pnd['tgl_jatuh_tempo'] = "tidak tersedia";}else{$pnd = $pendanaan;}
            $response = [
                'status' => 'sukses',
                'message' => 'borrower berhasil ditambahkan',
                'data_borrower' => $Borrower,
                'brw_type' => $borrowerDetails === null ? null : $borrowerDetails->brw_type,
                'brw_nama' => $Borrower === null ? null : $Borrower->username,
                'brw_ptotal' => $plafon === null ? null : $plafon->total_plafon,
                'brw_ppake' => $plafon === null ? null : $plafon->total_terpakai,
                'brw_psisa' => $plafon === null ? null : $plafon->total_sisa,
                'data_pendanaan' => $pnd
            ];
            
            return response()->json($response, 201);
        }
       
       
    }
   
}
