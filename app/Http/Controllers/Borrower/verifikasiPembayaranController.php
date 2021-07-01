<?php

// use DB;
namespace App\Http\Controllers;
use App\Event;
use App\Brw_pendanaan;
use App\Brw_rekening;
use App\Proyek;
use App\gambarProyek;
use App\deskripsiProyek;
use App\BorrowerDanaTerkumpul;
use App\BorrowerPendanaan;
use Illuminate\Http\Request;
use App\BorrowerDetails;
use DB;

class verifikasiPembayaranController extends Controller
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

    public function getdataPendanaanCair(){
        $data = Brw_pendanaan::select('brw_pendanaan.*','brw_users_details.nama')
        ->where('status', '7')
        ->where('status_dana','1')
        ->leftjoin('brw_users_details','brw_pendanaan.brw_id','=','brw_users_details.brw_id')
        ->get();
        $countData = $data->count();
        $status  = false;
        if($countData == 0){
            $status = false;
        }else{
            $status = true;
        }
        $kirim = ['data' => $data, 'status' => $status];
        return response()->json($kirim); 
    }

    public function allPendanaanCair($brw_id){
        return response()->json(Brw_pendanaan::where('brw_id',$brw_id)->whereIn('status',array(1,2,3,4,0,6))->get());
    }

}
