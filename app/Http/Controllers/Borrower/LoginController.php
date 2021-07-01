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
class LoginController extends Controller
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

    public function Login(Request $request){
        $password = $request->password;

        $borrower = Borrower::where('username', $request->username)->first();
        
       if (is_null($borrower)) {
            $response = [
                'status' => 'gagal_data',
                'message' => 'data tidak ditemukan',
                'data_borrower' => 'null', 
                'brw_type' => 'null']
                ;
            
         }
        
        else if(!Hash::check($request->password, $borrower->password)) {   

            $response = [
                'status' => 'gagal_password',
                'message' => 'password anda salah',
                'brw_type' =>"null"]
                ;
    
        }
        else if ($borrower['status'] == "suspend"){
            $response = [
                'status' => 'suspend',
                'message' => 'account anda telah kami suspend',
                'data_borrower' => $borrower, 
                'brw_type' =>"null"]
                ;
         }else{

            $borrowerDetails = BorrowerDetails::where('brw_id', $borrower->brw_id)->first();
            $plafon = Brw_rekening::where('brw_id',$borrower->brw_id)->first();
            $pendanaan = Brw_pendanaan::select('brw_pendanaan.*', 'proyek.nama','brw_invoice.tgl_jatuh_tempo')->leftjoin('brw_invoice','brw_pendanaan.id_proyek', '=' ,'brw_invoice.proyek_id')->leftjoin('proyek','brw_pendanaan.id_proyek', '=' ,'proyek.id')->where('brw_pendanaan.brw_id',$borrower->brw_id)->whereIn('brw_pendanaan.status',array(0,1,2,3))->get();
            $pnd = array();
            if(empty($pendanaan)){$pnd['nama'] = "tidak ada Proyek";$pnd['tgl_jatuh_tempo'] = "tidak tersedia";}else{$pnd = $pendanaan;}
            $response = [
                'status' => 'sukses',
                'message' => 'data ditemukan',
                'data_borrower' => $borrower, 
                'brw_type' => $borrowerDetails === null ? null : $borrowerDetails->brw_type,
                'brw_nama' => $borrower === null ? null : $borrower->username,
                'brw_ptotal' => $plafon === null ? null : $plafon->total_plafon,
                'brw_ppake' => $plafon === null ? null : $plafon->total_terpakai,
                'brw_psisa' => $plafon === null ? null : $plafon->total_sisa,
                'data_pendanaan' => $pnd
            ];
            
        }
       
        
        
        return response()->json($response);
    }

   
}
