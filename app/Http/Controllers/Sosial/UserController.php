<?php


namespace App\Http\Controllers\Sosial;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Support\Facades\Crypt;


class UserController extends Controller
{

  private function encrypt($value){
    return Crypt::encrypt($value);
  }

  private function decrypt($value){
    return Crypt::decrypt($value);
  }

  /******************************************** DASHBOARD *************************************************/

  // hitung jumlah pendanaan
  public function select_dashboard_user($id_user){
    $select_jumlah_dashboard_user = DB::connection('mysql2')->select("CALL select_jumlah_dashboard_user($id_user)");
    $select_va_number = DB::connection('mysql2')->select("select va_number from users where id = $id_user");
    if($select_jumlah_dashboard_user){
      $data_jumlah=[
        'jumlah_total'=>$select_jumlah_dashboard_user[0]->v_count_total = $select_jumlah_dashboard_user[0]->v_count_total ? $select_jumlah_dashboard_user[0]->v_count_total : 0,
        'jumlah_pembayaran'=>$select_jumlah_dashboard_user[0]->v_count_pembayaran = $select_jumlah_dashboard_user[0]->v_count_pembayaran ? $select_jumlah_dashboard_user[0]->v_count_pembayaran : 0,
        'jumlah_selesai'=>$select_jumlah_dashboard_user[0]->v_count_selesai = $select_jumlah_dashboard_user[0]->v_count_selesai ? $select_jumlah_dashboard_user[0]->v_count_selesai : 0
      ];
    }
    else{
      $data_jumlah=[];
    }

    $data=[
      'jumlah_pendanaan'=>$data_jumlah,
      'va_number' => array('full'=>$select_va_number[0]->va_number, 'split'=>substr($select_va_number[0]->va_number, 0,4)),

    ];

    return response()->json($data);

  }

   // list data pendanaan
   public function list_pendanaan($id){

    DB::beginTransaction();
    try {

    $select_procedure = DB::connection('mysql2')->select("CALL select_list_pendanaan_user($id)");
    $data=array();
    $i = 1;

    $x =0;
    $pendanaan[] = "";
   
    foreach ($select_procedure as $item) {

      if($item->id_status_pendanaan==1){
        $status_pendanaan = 'Pengajuan';
      }elseif ($item->id_status_pendanaan==2) {
        $status_pendanaan = 'Aktif';
      }elseif ($item->id_status_pendanaan==3) {
        $status_pendanaan = 'Penggalangan Terpenuhi';
      }elseif ($item->id_status_pendanaan==4) {
        $status_pendanaan = 'Penggalangan Selesai';
      }else{
        $status_pendanaan = 'Pendanaan Selesai';
      }
      
      $dana_masuk = "";
      $percent    = "";
      $number    = "";
      
      if($item->dana_masuk == null){
        $dana_masuk = 0;
      }else{

        $dana_masuk =  $item->dana_masuk;
        $percent = $item->total_dibutuhkan != 0 ? round($dana_masuk / ($item->total_dibutuhkan / 100),2) : 0;
        $number = number_format($percent,0);
      }
      
      $pendanaan[$x]=[

        'no' => $i++,
        'id_pendanaan_sosial'=>$item->id_pendanaan,
        'id_encrypt'=>$this->encrypt($item->id_pendanaan),
        'id_m_user'=>$item->id_m_user,
        'id_tipe_pendanaan'=>$item->id_tipe_pendanaan,
        'nama_pendanaan'=>$item->nama_pendanaan,
        'nama_yayasan'=>$item->nama_yayasan,
        'alamat'=>$item->alamat,
        'total_dibutuhkan'=> number_format($item->total_dibutuhkan,0, ',', '.'),
        'mulai_pendanaan'=>$item->mulai_pendanaan,
        'selesai_pendanaan'=>$item->selesai_pendanaan,
        'masa_pendanaan'=>$item->masa_pendanaan,
        'mulai_penggalangan'=>$item->mulai_penggalangan,
        'selesai_penggalangan'=>$item->selesai_penggalangan,
        'masa_penggalangan'=>$item->masa_penggalangan,
        'id_status_pendanaan'=>$status_pendanaan,
        'status_batas_waktu'=> $item->status_batas_waktu,
        'dana_masuk'=> number_format($dana_masuk,0, ',', '.'),
        'file' => env('APILINK').'/admin_sosial/tampilPoto/'.$this->encrypt($item->id_pendanaan),
        'percent'=>$percent,
        'cerita'=>$item->cerita,
        'created_at'=>$item->created_at
      ];
      $x++;
    }

    $data=[
      'pendanaan'=>$pendanaan == null ? "" : $pendanaan
    ];
    
    } catch (\Throwable $th) {
      $response =  $th;
      DB::rollback();
      echo $response;
    }
      DB::commit();
      return response()->json($data);

   }

   public function list_pendanaan_pembayaran_proses($id_user, $id_pendanaan){
     

    DB::beginTransaction();
    try {

    $select_procedure = DB::connection('mysql2')->select("CALL select_list_pembayaran_proses($id_user, $id_pendanaan)");
    $data=array();
    $i = 1;

    $x =0;
    $pendanaan[] = "";
   
    foreach ($select_procedure as $item) {
      
      $pendanaan[$x]=[

        'no' => $i++,
        'id_pendanaan_sosial'=>$item->id_pendanaan_sosial,
        'id_encrypt'=>$this->encrypt($item->id_pendanaan_sosial),
        'nama_pendanaan'=>$item->nama_pendanaan,
        'dana'=> number_format($item->dana_masuk,0, ',', '.'),
        'tanggal'=>$item->tanggal
      ];
      $x++;
    }

    $data=[
      'pendanaan'=>$pendanaan == null ? "" : $pendanaan
    ];
    
    } catch (\Throwable $th) {
      $response =  $th;
      DB::rollback();
      echo $response;
    }
      DB::commit();
      return response()->json($data);

   }

   public function list_pendanaan_pembayaran_selesai($id_user, $id_pendanaan){

    DB::beginTransaction();
    try {

    $select_procedure = DB::connection('mysql2')->select("CALL select_list_pembayaran_selesai($id_user, $id_pendanaan)");
    $data=array();
    $i = 1;

    $x =0;
    $pendanaan[] = "";
   
    foreach ($select_procedure as $item) {
      
      $pendanaan[$x]=[

        'no' => $i++,
        'id_pendanaan_sosial'=>$item->id_pendanaan_sosial,
        'id_encrypt'=>$this->encrypt($item->id_pendanaan_sosial),
        'nama_pendanaan'=>$item->nama_pendanaan,
        'dana'=>number_format($item->dana_masuk,0, ',', '.'),
        'tanggal'=>$item->tanggal
      ];
      $x++;
    }

    $data=[
      'pendanaan'=>$pendanaan == null ? "" : $pendanaan
    ];
    
    } catch (\Throwable $th) {
      $response =  $th;
      DB::rollback();
      echo $response;
    }
      DB::commit();
      return response()->json($data);
     
   }
  
   /*########################################## END DASHBOARD ##############################################*/
  
    

}