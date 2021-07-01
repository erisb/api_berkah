<?php

namespace App\Http\Controllers\Borrower;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

use Illuminate\Http\Request;
//use Illuminate\Support\Facades\DB;
use App\Borrower;
use App\BorrowerDetails;
use App\BorrowerAhliWaris;
use App\BorrowerRekening;
use App\BorrowerPengurus;
use App\BorrowerInvoice;
use DB;

class DataController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    public function test(){
        echo "radi";
    }
    
    public function DataPendidikan(){
        
       $getPendidikan = DB::table('m_pendidikan')->select('id_pendidikan', 'pendidikan')->get();
       //$borrower = Borrower::where('username', $request->username)->first();
       
		echo "[";
        $i = 0;
        foreach($getPendidikan as $data){
            if($i >0){
                echo ",\r\n"; 
            }
            echo "{\"id\"  : \"$data->id_pendidikan\", \"text\":\"$data->pendidikan\"}"; 
            $i++;
        }
		
		echo "]";
        
    }


    public function CheckNIK($nik){
        $checkNIK = DB::table('brw_users_details')
            //->whereIn('brw_type',[1,3])
            ->where('ktp', '=', $nik)
            ->first();

        if(!$checkNIK){
            $response = [
                'status' => 'kosong',
                'message' => 'KTP Tidak Ditemukan'
            ];

        }else{
            $response = [
                'status' => 'ada',
                'message' => 'KTP Ditemukan'
            ];
        }

        return response()->json($response);
        
    }

    public function CheckNIKBH($nik){
        $checkNIK = DB::table('brw_users_details')
            //->whereIn('brw_type',[1,3])
            ->where('ktp', '=', $nik)
            ->first();

        if(!$checkNIK){
            $response = [
                'status' => 'kosong',
                'message' => 'KTP Tidak Ditemukan'
            ];

        }else{
            $response = [
                'status' => 'ada',
                'message' => 'KTP Ditemukan'
            ];
        }

        return response()->json($response);
        
    }

    public function CheckNOTLP($noTLP){
        $CheckNOTLP = DB::table('brw_users_details')
            //->whereIn('brw_type',[1,3])
            ->where('no_tlp', '=', $noTLP)
            ->first();

        if(!$CheckNOTLP){
            $response = [
                'status' => 'kosong',
                'message' => 'No TLP Tidak Ditemukan'
            ];

        }else{
            $response = [
                'status' => 'ada',
                'message' => 'No TLP Ditemukan'
            ];
        }

        return response()->json($response);
        
    }

    public function DataProvinsi(){
		 
        $getProv = DB::table('m_provinsi_kota')->select('kode_provinsi', 'nama_provinsi')
            ->groupBy('nama_provinsi')
            ->get();
        echo "[";
        $i = 0;
        foreach($getProv as $data){
            if($i >0){
                echo ",\r\n"; 
            }
            echo "{\"id\"  : \"$data->kode_provinsi\", \"text\":\"$data->nama_provinsi\"}"; 
            $i++;
        }
		
		echo "]";
   
    }
    
    public function DataKota($id){
		 
	  
        $getKota = DB::table('m_provinsi_kota')->select('id_provinsi','kode_kota','kode_provinsi', 'nama_provinsi', 'nama_kota')
        ->where('kode_provinsi', '=',$id)->get();
        
        echo "[";
        $i = 0;
        foreach($getKota as $data){
            if($i >0){
                echo ",\r\n"; 
            }
            echo "{\"id\"  : \"$data->kode_kota\", \"text\":\"$data->nama_kota\"}"; 
            $i++;
        }
		
		echo "]";
    }
    

   public function DataPekerjaan(){
		 
	  
		$getPekerjaan = DB::table('m_pekerjaan')->select('id_pekerjaan', 'pekerjaan')->get();
		
		$i=0; 
        echo "[";
        foreach($getPekerjaan as $data){
            if($i >0){
                echo ",\r\n"; 
            }
            echo "{\"id\"  : \"$data->id_pekerjaan\", \"text\":\"$data->pekerjaan\"}"; 
            $i++;
        }
		
		echo "]";
   
    }

    public function DataBank(){
		 
	  
		$getBank = DB::table('m_bank')->select('kode_bank', 'nama_bank')->get();
		
		$i=0; 
        echo "[";
        foreach($getBank as $data){
            if($i >0){
                echo ",\r\n"; 
            }
            echo "{\"id\"  : \"$data->kode_bank\", \"text\":\"$data->nama_bank\"}"; 
            $i++;
        }
		
		echo "]";
   
    }

    public function DataBidangPekerjaan(){
		 
	  
		$getBidangPekerjaan = DB::table('m_bidang_pekerjaan')->select('id_bidang_pekerjaan', 'bidang_pekerjaan')->get();
		
		$i=0; 
        echo "[";
        foreach($getBidangPekerjaan as $data){
            if($i >0){
                echo ",\r\n"; 
            }
            echo "{\"id\"  : \"$data->id_bidang_pekerjaan\", \"text\":\"$data->bidang_pekerjaan\"}"; 
            $i++;
        }
		
		echo "]";
   
    }

    public function DataPengalamanPekerjaan(){
		 
	  
		$getpengalamanPekerjaan = DB::table('m_pengalaman_kerja')->select('id_pengalaman_kerja', 'pengalaman_kerja')->get();
		
		$i=0; 
        echo "[";
        foreach($getpengalamanPekerjaan as $data){
            if($i >0){
                echo ",\r\n"; 
            }
            echo "{\"id\"  : \"$data->id_pengalaman_kerja\", \"text\":\"$data->pengalaman_kerja\"}"; 
            $i++;
        }
		
		echo "]";
   
    }
    public function DataPendapatan(){
		 
	  
		$getPendapatan = DB::table('m_pendapatan')->select('id_pendapatan', 'pendapatan')->get();
        
        $i=0; 
        echo "[";
        foreach($getPendapatan as $data){
            if($i >0){
                echo ",\r\n"; 
            }
            echo "{\"id\"  : \"$data->id_pendapatan\", \"text\":\"$data->pendapatan\"}"; 
            $i++;
        }
		
		echo "]";

    }   

    public function TipePendanaan(){
		 
	  
		$getTipePendanaan = DB::table('brw_tipe_pendanaan')->select('tipe_id', 'pendanaan_nama')->get();
		
		$i=0; 
        echo "[";
        foreach($getTipePendanaan as $data){
            if($i >0){
                echo ",\r\n"; 
            }
            echo "{\"id\"  : \"$data->tipe_id\", \"text\":\"$data->pendanaan_nama\"}"; 
            $i++;
        }
		
		echo "]";
   
    }

    public function JenisJaminan(){
		 
	  
		$getJenisJaminan = DB::table('m_jenis_jaminan')->select('id_jenis_jaminan', 'jenis_jaminan')->get();
		
		$i=0; 
        echo "[";
        foreach($getJenisJaminan as $data){
            if($i >0){
                echo ",\r\n"; 
            }
            echo "{\"id\"  : \"$data->id_jenis_jaminan\", \"text\":\"$data->jenis_jaminan\"}"; 
            $i++;
        }
		
		echo "]";
   
    }

    public function JenisKelamin(){
		 
	  
		$getTipePendanaan = DB::table('m_jenis_kelamin')->select('id_jenis_kelamin', 'jenis_kelamin')->get();
		
		
		return response()->json($getTipePendanaan);
   
    }

    public function Agama(){
		 
	  
		$getTipeAgama = DB::table('m_agama')->select('id_agama', 'agama')->get();
		
		
		return response()->json($getTipeAgama);
   
    }

    public function StatusPerkawinan(){
		 
	  
		$getTipePendanaan = DB::table('m_kawin')->select('id_kawin', 'jenis_kawin')->get();
		
		$i=0; 
        echo "[";
        foreach($getTipePendanaan as $data){
            if($i >0){
                echo ",\r\n"; 
            }
            echo "{\"id\"  : \"$data->id_kawin\", \"text\":\"$data->jenis_kawin\"}"; 
            $i++;
        }
		
		echo "]";
   
    }

    public function BidangPekerjaanOnline(){
		 
	  
		$getTipePendanaan = DB::table('m_online')->select('id_online', 'tipe_online')->get();
		
		$i=0; 
        echo "[";
        foreach($getTipePendanaan as $data){
            if($i >0){
                echo ",\r\n"; 
            }
            echo "{\"id\"  : \"$data->id_online\", \"text\":\"$data->tipe_online\"}"; 
            $i++;
        }
		
		echo "]";
   
    }

    public function KepemilikanRumah(){
		 
	  
		$getTipePendanaan = DB::table('m_kepemilikan_rumah')->select('id_kepemilikan_rumah', 'kepemilikan_rumah')->get();
		
		$i=0; 
        echo "[";
        foreach($getTipePendanaan as $data){
            if($i >0){
                echo ",\r\n"; 
            }
            echo "{\"id\"  : \"$data->id_kepemilikan_rumah\", \"text\":\"$data->kepemilikan_rumah\"}"; 
            $i++;
        }
		
		echo "]";
   
    }

    public function PersyaratanPendanaan($tipe_borrower, $tipe_pendanaan){
		 
	  
        $getPersyaratan = DB::table('brw_persyaratan_pendanaan')->select('persyaratan_id','tipe_id', 'user_type', 'persyaratan_nama', 'persyaratan_mandatory')
        ->where('tipe_id', '=', $tipe_pendanaan)
        ->where('user_type', '=', $tipe_borrower)
        ->get();
        echo "[";
        $i = 0;
        foreach($getPersyaratan as $data){
            
            if($i >0){
				echo ",\r\n"; 
			}
			echo "{\"persyaratan_id\"  : \"$data->persyaratan_id\", \"tipe_id\"  : \"$data->tipe_id\", \"user_type\":\"$data->user_type\", \"persyaratan_nama\":\"$data->persyaratan_nama\", \"persyaratan_mandatory\":\"$data->persyaratan_mandatory\"}"; 
			$i++;
			
        }
		echo "]";
        
    }

    public function PersyaratanPendanaanProsesPengajuan($brw_id,$user_type, $tipe_id){
		 
	  
        // $getPersyaratan = DB::table('brw_persyaratan_insert')
        // ->where('brw_id', '=', $brw_id)
        // ->where('tipe_id', '=', $tipe_pendanaan)
        // ->where('user_type', '=', $tipe_borrower)
        // ->get();

        $getPersyaratan = DB::select("SELECT brw_persyaratan_insert.*, brw_persyaratan_pendanaan.persyaratan_nama FROM brw_persyaratan_insert INNER JOIN brw_persyaratan_pendanaan ON brw_persyaratan_insert.persyaratan_id = brw_persyaratan_pendanaan.persyaratan_id WHERE brw_id = $brw_id AND brw_persyaratan_insert.tipe_id = $tipe_id AND brw_persyaratan_insert.user_type = $user_type");
        
        if(empty($getPersyaratan)){
            $getPersyaratan = DB::select("SELECT persyaratan_id,tipe_id,user_type,persyaratan_nama, persyaratan_mandatory as checked FROM brw_persyaratan_pendanaan WHERE tipe_id = $tipe_id AND user_type = $user_type");
            
            
        }

        echo "[";
        $i = 0;
        foreach($getPersyaratan as $data){
            
            if($i >0){
				echo ",\r\n"; 
			}
			echo "{\"persyaratan_id\"  : \"$data->persyaratan_id\", \"tipe_id\"  : \"$data->tipe_id\", \"user_type\":\"$data->user_type\", \"persyaratan_nama\":\"$data->persyaratan_nama\", \"persyaratan_mandatory\":\"$data->checked\"}"; 
			$i++;
			
        }
		echo "]";
        
    }

    public function statusbrw($brw_id){
        $getstatus = Borrower::where('brw_id', $brw_id)->first();
        // dd($getstatus);
        $response = [
            'statusBrw' => $getstatus->status
        ];
        return response()->json($response);
    }

    public function cek_password(Request $request){
        $aidi = explode("*dsi*",base64_decode($request->id));

        $cekpassword = Borrower::where('brw_id', $aidi[0])->first();
        if(!Hash::check($aidi[1], $cekpassword->password)){
            $response = [
                'status' => 'beda'
            ];
        }else{
            $response = [
                'status' => 'sama'
            ];
        }
        return response()->json($response);       
    }

    public function getProfileBrw(Request $request){
        $id = $request->id;
        $getBrwUser = BorrowerDetails::where('brw_id', $id)->first();
        $getBrwUserAhliWaris = BorrowerAhliWaris::where('brw_id', $id)->first();
        $getBrwUserRekening = BorrowerRekening::where('brw_id', $id)->first();
        $getBrwUserPengurus = BorrowerPengurus::where('brw_id', $id)->first();

        $parsingJSON = array('data'=>$getBrwUser, 'data_ahliwaris'=>$getBrwUserAhliWaris === null ? null : $getBrwUserAhliWaris, 'data_rekening'=>$getBrwUserRekening, 'data_pengurus'=>$getBrwUserPengurus);
        echo json_encode($parsingJSON);
    }

    public function listInvoice($brw_id, $proyek_id){
        $list_invoice = BorrowerInvoice::where('brw_id', $brw_id)->where('proyek_id', $proyek_id)->get();

        $data= array();
        foreach($list_invoice as $data_value)
        {
            $column['invoice_id'] = (string) $data_value->invoice_id;
            $column['brw_id'] = (string) $data_value->brw_id;
            $column['proyek_id'] = (string) $data_value->proyek_id;
            $column['dana_pokok'] = (string) $data_value->dana_pokok;
            $column['imbal_hasil'] = (string) $data_value->imbal_hasil;
            $column['total_bayar'] = (string) $data_value->total_bayar;
            $column['tgl_jatuh_tempo'] = (string) $data_value->tgl_jatuh_tempo;

            $data[] = $column;   
        }

        
        $parsingJSON = array("data" => $data);

        echo json_encode($parsingJSON);
    }
    
}
