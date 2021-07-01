<?php
namespace App\Http\Controllers\SosialAdmin;

use App\Http\Controllers\Controller;
//use App\Http\Middleware\StatusPendanaanSosial;
use Illuminate\Http\Request;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;
use App\User;

class AdminController extends Controller
{

  public function __construct(){

    $dataPendanaan = DB::connection('mysql2')->select('select * from pendanaan_sosial where id_tipe_pendanaan IN (1,3,6) and id_status_pendanaan = 2 and status_batas_waktu = 0');
    if ($dataPendanaan)
    {
        $tglSekarang = Carbon::now()->format('Y-m-d');
        foreach ($dataPendanaan as $pendanaan)
        {
          
          $pendanaanSelesaiPenggalangan = Carbon::parse($pendanaan->selesai_penggalangan)->format('Y-m-d');
          
          
          if ($tglSekarang > $pendanaanSelesaiPenggalangan)
          {
            //die($pendanaan->id_pendanaan_sosial) ;
              //$getStatus = DB::connection('mysql2')->select("select status from list_pendanaan_masuk where id_pendanaan_sosial = $pendanaan->id_pendanaan_sosial");
              //$cekStatus = !empty($getStatus) ? $getStatus->status : 0;
              //if ($cekStatus != 2)
              //{
                  $this->updateProyek($pendanaan->id_pendanaan_sosial,4); // penggalangan selesai
              //}
          }
        }
    }
  }

  private function updateProyek($id,$status)
  {
    if ($id)
    {
        $update = DB::connection('mysql2')->table('pendanaan_sosial')->where('id_pendanaan_sosial', $id)->update(['id_status_pendanaan' => $status]);

        return $update;
    }
  }
  

  private function encrypt($value){
    return Crypt::encrypt($value);
  }

  private function decrypt($value){
    return Crypt::decrypt($value);
  }
   /******************************************** LANDING PAGE *************************************************/

   public function select_landing_t_indikator(){
    DB::beginTransaction();

    try{
      $select_procedure = DB::connection('mysql2')->select('select total_pendanaan_sosial, total_donatur, total_ziswaf from t_indikator');

      $data = [
        'total_pendanaan_sosial'=>empty($select_procedure[0]->total_pendanaan_sosial) ? 0 : $select_procedure[0]->total_pendanaan_sosial,
        'total_donatur'=>empty($select_procedure[0]->total_donatur) ? 0 : $select_procedure[0]->total_donatur,
        'total_ziswaf'=>empty($select_procedure[0]->total_ziswaf) ? 0 : $select_procedure[0]->total_ziswaf
      ];
      return response()->json($data);
    }catch (\Throwable $th) {
      DB::rollback();
    }
   }

   public function select_url_zakat(){
    DB::beginTransaction();

    try{
      $select_url_maal = DB::connection('mysql2')->select('select id_pendanaan_sosial from pendanaan_sosial where id_tipe_pendanaan = 2 order by id_pendanaan_sosial desc limit 1');
      $select_url_profesi = DB::connection('mysql2')->select('select id_pendanaan_sosial from pendanaan_sosial where id_tipe_pendanaan = 4 order by id_pendanaan_sosial desc limit 1');
      
      $data = [
        'url_maal' => empty($select_url_maal[0]->id_pendanaan_sosial) ? '' : $this->encrypt($select_url_maal[0]->id_pendanaan_sosial),
        'url_profesi'=> empty($select_url_profesi[0]->id_pendanaan_sosial) ? '' : $this->encrypt($select_url_profesi[0]->id_pendanaan_sosial)
      ];
      return response()->json($data);
    }catch (\Throwable $th) {
      DB::rollback();
    }
   }


   // list pendanaan sosial landing page
   public function select_pendanaan_landing(){
    DB::beginTransaction();
    try {

    $select_procedure = DB::connection('mysql2')->select('CALL select_pendanaan_landing_page()');
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
        //'id_encrypt'=> $item->id_pendanaan,
        'id_encrypt'=> $this->encrypt($item->id_pendanaan),
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
        'berita'=>$item->berita,
        'created_at'=>$item->created_at
      ];
      $x++;
    }

    $data=[
      'pendanaan'=> $pendanaan == null ? '' : $pendanaan
    ];
    
    } catch (\Throwable $th) {
      $response =  $th;
      DB::rollback();
      echo $response;
    }
      DB::commit();
      return response()->json($data);
  }

  // detail pendanaan landing page
  public function get_pendanaan_landing($id_pendanaan){
    $id_decrypt = $this->decrypt($id_pendanaan);
    DB::beginTransaction();
    try {

    $select_procedure = DB::connection('mysql2')->select("CALL get_pendanaan_landing_page($id_decrypt)");
    
    
    $data=array();
    $i = 1;

    $x =0;
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
      
      //$dana_masuk =  $item->dana_masuk === null ? "0": $item->dana_masuk;
      //echo $dana_masuk;die;
      $dana_masuk = "";
      $percent    = "";
      $number    = "";
      if($item->dana_masuk == null){
        $dana_masuk = 0;
      }else{

        $dana_masuk =  $item->dana_masuk;
        
        //$percent = ($item->total_dibutuhkan / 100)*$dana_masuk;
        $percent = $item->total_dibutuhkan != 0 ? round($dana_masuk / ($item->total_dibutuhkan / 100),2) : 0;
        $number = number_format($percent,0);
        
      }

      $pendanaan[$x]=[
        'no' => $i++,
        'id_pendanaan_sosial'=>$item->id_pendanaan_sosial,
        'id_m_user'=>$item->id_m_user,
        'id_tipe_pendanaan'=>$item->id_tipe_pendanaan,
        'nama_pendanaan'=>$item->nama_pendanaan,
        'nama_yayasan'=>$item->nama_yayasan,
        'alamat'=>$item->alamat,
        'total_dibutuhkan'=> number_format($item->total_dibutuhkan,0, ',', '.'),
        //'mulai_pendanaan'=>'2014/02/08',
        'mulai_pendanaan'=>$item->mulai_pendanaan,
        'selesai_pendanaan'=>$item->selesai_pendanaan,
        'masa_pendanaan'=>$item->masa_pendanaan,
        'mulai_penggalangan'=>$item->mulai_penggalangan,
        'selesai_penggalangan'=>$item->selesai_penggalangan,
        'selisih_hari'=>$item->hari,
        'masa_penggalangan'=>$item->masa_penggalangan,
        'id_status_pendanaan'=>$status_pendanaan,
        'status_batas_waktu'=>$item->status_batas_waktu,
        'dana_masuk'=> number_format($dana_masuk,0, ',', '.'),
        'percent'=>$number,
        'cerita'=>$item->cerita,
        'yayasan_foto' => env('APILINK').'/admin_sosial/tampilPotoYayasan/'.$this->encrypt($item->nama_yayasan),
        'yayasan_desk' => $item->deskripsi,
        'video'=>(string)$item->video,
        'foto'=> env('APILINK').'/admin_sosial/tampilPoto/'.$id_pendanaan,
        //'foto'=> env('APILINK').'/admin_sosial/tampilPoto/'.$this->encrypt($item->id_pendanaan_sosial),
        'berita'=>$item->berita,
        'created_at'=>$item->created_at
      ];
      $x++;
    }

    $data=[
      'pendanaan'=>$pendanaan
    ];
    
    } catch (\Throwable $th) {
      $response =  $th;
      DB::rollback();
      echo $response;
    }
      DB::commit();
      return response()->json($data);

  }

   // list menu all pendanaan sosial
   public function select_page_pendanaan_sosial(){
    
    DB::beginTransaction();
    try {

    $select_procedure = DB::connection('mysql2')->select('CALL select_page_all_pendanaan()');

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
        'id_encrypt'=> $this->encrypt($item->id_pendanaan),
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
        'hari'=> $item->hari,
        'file' => env('APILINK').'/admin_sosial/tampilPoto/'.$this->encrypt($item->id_pendanaan),
        'percent'=>$number,
        'cerita'=>$item->cerita,
        'berita'=>$item->berita,
        'created_at'=>$item->created_at
      ];

     
      $x++;
    }

    $data=[
      'pendanaan'=> $pendanaan == null ? '' : $pendanaan
    ];
    
    } catch (\Throwable $th) {
      $response =  $th;
      DB::rollback();
      echo $response;
    }
      DB::commit();
      return response()->json($data);
  }

  // list menu all pendanaan selesai
  public function select_page_pendanaan_sosial_selesai(){
    
    DB::beginTransaction();
    try {

    $select_procedure = DB::connection('mysql2')->select('CALL select_page_all_pendanaan_selesai()');

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
        'id_encrypt'=> $this->encrypt($item->id_pendanaan),
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
        'hari'=> $item->hari,
        'file' => env('APILINK').'/admin_sosial/tampilPoto/'.$this->encrypt($item->id_pendanaan),
        'percent'=>$percent,
        'cerita'=>$item->cerita,
        'berita'=>$item->berita,
        'created_at'=>$item->created_at
      ];

     
      $x++;
    }

    $data=[
      'pendanaan'=> $pendanaan == null ? '' : $pendanaan
    ];
    
    } catch (\Throwable $th) {
      $response =  $th;
      DB::rollback();
      echo $response;
    }
      DB::commit();
      return response()->json($data);
  }

  // list page all ziswaf
  public function select_page_ziswaf(){
    
    DB::beginTransaction();
    try {

    $select_procedure = DB::connection('mysql2')->select('CALL select_page_ziswaf()');
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
        'id_encrypt'=> $this->encrypt($item->id_pendanaan),
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
        'hari'=> $item->hari,
        'file' => env('APILINK').'/admin_sosial/tampilPoto/'.$this->encrypt($item->id_pendanaan),
        'percent'=>$number,
        'cerita'=> $item->cerita == "" ? '' : $item->cerita,
        'berita'=>$item->berita,
        'created_at'=>$item->created_at
      ];
      $x++;
    }

    $data=[
      'pendanaan'=> $pendanaan == null ? '' : $pendanaan
    ];
    
    } catch (\Throwable $th) {
      $response =  $th;
      DB::rollback();
      echo $response;
    }
      DB::commit();
      return response()->json($data);
  }


  /*########################################## END LANDING ##############################################*/
  
  /******************************************** PENDANAAN *************************************************/

  // list data pendanaan sosial admin
  public function select_pendanaan_admin(){
    
    DB::beginTransaction();
    try {

    $select_procedure = DB::connection('mysql2')->select('CALL select_pendanaan_sosial()');
    
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


      $pendanaan[$x]=[
        'no' => $i++,
        'id_pendanaan'=>$item->id_pendanaan_sosial,
        'id_tipe_pendanaan'=>$item->id_tipe_pendanaan,
        'nama_pendanaan'=>$item->nama_pendanaan,
        'id_status_pendanaan'=>$status_pendanaan,
      ];
      $x++;
    }

    $data=
      $pendanaan == "" ? '' : $pendanaan
      //'pendanaan'=>$pendanaan == "" ? '' : $pendanaan
    ;
    
    } catch (\Throwable $th) {
      $response =  $th;
      DB::rollback();
      //echo $response;
    }
      DB::commit();
      return response()->json($data);
  }

  // list tipe pendanaan
  public function fetch_add_pendanaan(){
    $select_m_tipe_pendanaan = DB::connection('mysql2')->select('CALL select_m_tipe_pendanaan()');

    $x=0;
    $i=1;
    foreach($select_m_tipe_pendanaan as $item){
      $data_m[$x]=[
        'no'=>$i++,
        'id'=>$item->id_m_funding_type,
        'nama'=>$item->name
      ];
    
    $x++;
    }

    $select_m_yayasan = DB::connection('mysql2')->select('CALL select_m_yayasan()');
    $y=0;
    $z=1;
    foreach($select_m_yayasan as $item){
      $data_y[$y]=[
        'no'=>$z++,
        'id'=>$item->id_m_yayasan,
        'nama'=>$item->nama
      ];
      $y++;
    }

    $data=[
      'pendanaan'=>$data_m,
      'yayasan'=>$data_y
    ];
    return response()->json($data);
  }

  // proses add pendanaan by admin
  public function add_pendanaan(Request $request){

    DB::beginTransaction();
    try {
      $id_m_user = "";
      $nama_pendanaan = $request->nama_pendanaan;
      $yayasan = $request->yayasan;
      $alamat = '';
      $id_tipe_pendanaan=$request->tipe_pendanaan_data;
      $total_dibutuhkan = $request->dana_dibutuhkan;
      $mulai_pendanaan = $request->start_funding;
      $selesai_pendanaan = $request->end_funding;
      $masa_pendanaan = $request->durasi_proyek_hari;
      $mulai_penggalangan = $request->start_funding;
      $selesai_penggalangan = $request->end_funding;
      $masa_penggalangan = $request->durasi_penggalangan_hari;
      $status_batas_waktu = $request->status_batas_waktu;

      $lokasi_gambar = 'app/pendanaan/';
      $video = $request->video;
      $deskripsi = $request->deskripsi;

      $call_db = DB::connection('mysql2')->select(
        "call add_pendanaan_sosial(
          '$id_m_user',
          '$id_tipe_pendanaan',
          '$nama_pendanaan', 
          '$yayasan',
          '$alamat',
          '$total_dibutuhkan',
          '$mulai_pendanaan', 
          '$selesai_pendanaan', 
          '$masa_pendanaan', 
          '$mulai_penggalangan', 
          '$selesai_penggalangan', 
          '$masa_penggalangan',
          '$status_batas_waktu',
          '$lokasi_gambar',
          '$video',
          '$deskripsi'
        )"
      );
    
    if($call_db[0]->v_out == 'Sukses'){

      $id_pendanaan_sosial = $call_db[0]->id_pendanaan_sosial;
        $response='Sukses';

    }else{
      $id_pendanaan_sosial = '';
      $response = 'Gagal';

    }
  } 
  catch (\Throwable $th) {
    $response =  $th;
    DB::rollback();
  }
      DB::commit();
      return response()->json(['status' => $response, 'id'=>$id_pendanaan_sosial]);
  }

  // proses edit pendanaan
  public function save_edit_pendanaan(Request $request){
    
    DB::beginTransaction();
    try {

      $id_pendanaan   = $request->id;
      $id_status_pendanaan   = $request->id_status_pendanaan;
      $nama_pendanaan = $request->nama_pendanaan;
      $yayasan  = $request->yayasan;
      $id_tipe_pendanaan= $request->tipe_pendanaan_data;
      $total_dibutuhkan = $request->dana_dibutuhkan;
      // $mulai_pendanaan = $request->start_funding;
      // $selesai_pendanaan = $request->end_funding;
      // $masa_pendanaan_hari = $request->durasi_proyek_hari;
      $mulai_penggalangan = $request->start_funding;
      $selesai_penggalangan = $request->end_funding;
      $masa_penggalangan_hari = $request->durasi_penggalangan_hari;
      // $status_batas_waktu = $request->status_batas_waktu;
      $status_batas_waktu = $request->status_batas_waktu;
      $video = $request->video;
      $deskripsi_cerita = $request->deskripsi;

      $setStatusPendanan = ""; 
      
      $getData = DB::connection('mysql2')->select("select * from pendanaan_sosial where id_pendanaan_sosial = $id_pendanaan ");
      
      if(($getData[0]->id_status_pendanaan == 4 || $getData[0]->id_status_pendanaan == 6) && ($getData[0]->selesai_penggalangan !== $selesai_penggalangan)){
      
      // if($getData[0]->id_status_pendanaan == 6  && $getData[0]->selesai_penggalangan !== $selesai_penggalangan){
        $setStatusPendanan .= 2;
      }else{
        $setStatusPendanan = $getData[0]->id_status_pendanaan;
        }

      $call_db = DB::connection('mysql2')->select(
        "call edit_pendanaan_sosial(
          '$id_pendanaan',
          '$id_tipe_pendanaan',
          '$nama_pendanaan',
          '$yayasan',
          '$total_dibutuhkan',
          '$mulai_penggalangan',
          '$selesai_penggalangan',
          '$masa_penggalangan_hari',
          '$setStatusPendanan',
          '$status_batas_waktu',
          '$deskripsi_cerita',
          '$video'
        )"
      );
    
      if($call_db[0]->v_out == 'Sukses'){
        $response = 'Sukses Insert';
      }else{
        $response = 'Gagal Insert';
      }
      
    }
    catch (\Throwable $th) {
        $response =  $th;
        DB::rollback();
    }
     
    DB::commit();
    return response()->json(['status' => $response]);
     
  }

  // upload foto pendanaan
  public function upload_gambar_pendanaan(Request $request)
  {

      if ($request->hasFile('myFile')) {
        // $image  = $request->file('myFile');
        // $name   = $request->id_pendanaan.'.'.$image->getClientOriginalExtension();
        // $destinationPath = storage_path('/app/public/pendanaan/');
        // $request->file('myFile')->move($destinationPath, $name);

        $image       = $request->file('myFile');
        $filename     = $request->id_pendanaan.'.'.$image->getClientOriginalExtension();

        $image_resize = Image::make($image->getRealPath());              
        //$image_resize->resize(300, 300);
        // 640 480
        $image_resize->resize(375, 535);
        $destinationPath = storage_path('/app/public/pendanaan/');
        $request->file('myFile')->move($destinationPath, $filename);
        //$image_resize->save(public_path('/app/public/pendanaan/' .$filename));
        
        $updateFileName = DB::connection('mysql2')->table('pendanaan_sosial_cerita')
              ->where('id_pendanaan_sosial', $request->id_pendanaan)
              ->update(['lokasi_gambar' => "/app/public/pendanaan/".$filename]);

        return response()->json(['data'=>"poto berhasil diupload"]);

      }else{

        return response()->json(['data'=>"gagal"]);
    
      }
  }

  // get data pendanaan by admin
  public function select_data_pendanaan($id_pendanaan){

    DB::beginTransaction();
    try {

    $get_data = DB::connection('mysql2')->select("CALL get_pendanaan_landing_page($id_pendanaan)");
    
    $data=array();
    $i = 1;

    $x =0;
      if($get_data[0]->id_status_pendanaan==1){
        $status_pendanaan = 'Pengajuan';
      }elseif ($get_data[0]->id_status_pendanaan==2) {
        $status_pendanaan = 'Aktif';
      }elseif ($get_data[0]->id_status_pendanaan==3) {
        $status_pendanaan = 'Penggalangan Terpenuhi';
      }elseif ($get_data[0]->id_status_pendanaan==4) {
        $status_pendanaan = 'Penggalangan Selesai';
      }else{
        $status_pendanaan = 'Pendanaan Selesai';
      }
      
      $dana_masuk = "";
      $percent    = "";
      $number    = "";
      
      if($get_data[0]->dana_masuk == null){
        $dana_masuk = 0;
      }else{

        $dana_masuk =  $get_data[0]->dana_masuk;
        $percent = $get_data[0]->total_dibutuhkan != 0 ? round($dana_masuk / ($get_data[0]->total_dibutuhkan / 100),2) : 0;
        $number = number_format($percent,0);
      }
      
      $pendanaan =[

        'id_pendanaan_sosial'=>$get_data[0]->id_pendanaan_sosial,
        'id_m_user'=>$get_data[0]->id_m_user,
        'id_tipe_pendanaan'=>$get_data[0]->id_tipe_pendanaan,
        'nama_pendanaan'=>$get_data[0]->nama_pendanaan,
        'nama_yayasan'=>$get_data[0]->nama_yayasan,
        'alamat'=>$get_data[0]->alamat,
        'total_dibutuhkan'=> $get_data[0]->total_dibutuhkan,
        //'total_dibutuhkan'=> number_format($get_data[0]->total_dibutuhkan,0, ',', '.'),
        'mulai_pendanaan'=> $get_data[0]->mulai_pendanaan,
        'selesai_pendanaan'=>$get_data[0]->selesai_pendanaan,
        'masa_pendanaan'=>$get_data[0]->masa_pendanaan,
        'mulai_penggalangan'=>$get_data[0]->mulai_penggalangan,
        'selesai_penggalangan'=>$get_data[0]->selesai_penggalangan,
        'masa_penggalangan'=>$get_data[0]->masa_penggalangan,
        'id_status_pendanaan'=>$status_pendanaan,
        'status_batas_waktu'=> $get_data[0]->status_batas_waktu,
        'dana_masuk'=> number_format($dana_masuk,0, ',', '.'),
        'file' => env('APILINK').'/admin_sosial/tampilPoto/'.$this->encrypt($id_pendanaan),
        'hari' => $get_data[0]->hari,
        'percent'=>$percent,
        'cerita'=>$get_data[0]->cerita,
        'berita'=>$get_data[0]->berita,
        'video'=>$get_data[0]->video,
        'created_at'=>$get_data[0]->created_at
      ];

    $data=[
      'pendanaan'=>$pendanaan
    ];

    
    } catch (\Throwable $th) {
      $response =  $th;
      DB::rollback();
      echo $response;
    }
      DB::commit();
      return response()->json($data);
  }

  

  public function delete_pendanaan($id_pendanaan){
    DB::beginTransaction();
    try {
    $select_procedure = DB::connection('mysql2')->select(DB::raw("CALL delete_pendanaan_sosial('$id_pendanaan')"));
    //$select_table = DB::connection('mysql2')->select("select * from pendanaan_sosial_cerita where id_pendanaan_sosial = $id_pendanaan");
    
    $response = array("status"=>"sukses");
    //$this->delete_gambar_pendanaan($select_table[0]->lokasi_gambar);
    } 
    catch (\Throwable $th) {
      $response =  array("status"=>"gagal", "message"=>$th);
      DB::rollback();
    }
      DB::commit();
      return response()->json($response);
  }

  public function delete_gambar_pendanaan($filename)
  {
    unlink($filename);
  }

  /*########################################## END PENDANAAN ##############################################*/

  

  /******************************************** NEWS ***************************************************/

  public function add_news(){
    
    DB::beginTransaction();
    try {
    $id_m_user = 10;
    $id_tipe_pendanaan=5;
    $nama_pendanaan = 'Zakat Jumatan';
    $alamat = 'Karawang';
    $total_dibutuhkan = 1000000;
    $mulai_pendanaan = '2020-04-03';
    $selesai_pendanaan = '2020-06-03';
    $masa_pendanaan = '90';
    $mulai_penggalangan = '2020-03-03';
    $selesai_penggalangan = '2020-04-02';
    $masa_penggalangan = '120';

    $lokasi_gambar = 'zela d dragonnest';
    $deskripsi = 'zela d dragonnest';

    $call_db = DB::connection('mysql2')->select(
      "call add_pendanaan_sosial(
        '$id_m_user',
        '$id_tipe_pendanaan',
        '$nama_pendanaan', 
        '$alamat',
        '$total_dibutuhkan',
        '$mulai_pendanaan', 
        '$selesai_pendanaan', 
        '$masa_pendanaan', 
        '$mulai_penggalangan', 
        '$selesai_penggalangan', 
        '$masa_penggalangan'
      )"
    );
    $response = 'Success';
    } catch (\Throwable $th) {
      $response =  $th;
      DB::rollback();
    }
      DB::commit();
      return response($response);
  }

  public function select_news(){
  
    DB::beginTransaction();
    try {
    $id_pendanaan = 1;

    $select_procedure = DB::connection('mysql2')->select(DB::raw("CALL select_pendanaan_sosial()"));
    
    print_r($select_procedure);die;
    $response = 'Success';
    } catch (\Throwable $th) {
      $response =  $th;
      DB::rollback();
    }
      DB::commit();
      return response($response);
  }

  public function edit_news(){
  
    DB::beginTransaction();
    try {
      $id_pendanaan = 3;
      $id_m_user = 10;
      $id_tipe_pendanaan=5;
      $nama_pendanaan = 'Zakat Yok Bro';
      $alamat = 'Karawang Digoyang Broo';
      $total_dibutuhkan = 9000000;
      $mulai_pendanaan = '2020-04-03';
      $selesai_pendanaan = '2020-06-03';
      $masa_pendanaan = '90';
      $mulai_penggalangan = '2020-03-03';
      $selesai_penggalangan = '2020-04-02';
      $masa_penggalangan = '120';
  
      $lokasi_gambar = 'zela d dragonnest';
      $deskripsi = 'zela d dragonnest';

      DB::connection('mysql2')->select(
        "call edit_pendanaan_sosial(
          '$id_pendanaan',
          '$id_m_user',
          '$id_tipe_pendanaan',
          '$nama_pendanaan', 
          '$alamat',
          '$total_dibutuhkan',
          '$mulai_pendanaan', 
          '$selesai_pendanaan', 
          '$masa_pendanaan', 
          '$mulai_penggalangan', 
          '$selesai_penggalangan', 
          '$masa_penggalangan'
        )"
      );
    
    $response = 'Success';
    } catch (\Throwable $th) {
      $response =  $th;
      DB::rollback();
    }
      DB::commit();
      return response($response);
  }

  public function delete_news(){
  
    DB::beginTransaction();
    try {
      $id_pendanaan = 1;

    $select_procedure = DB::connection('mysql2')->select(DB::raw("CALL delete_pendanaan_sosial('$id_pendanaan')"));
    
    $response = 'Success';
    } catch (\Throwable $th) {
      $response =  $th;
      DB::rollback();
    }
      DB::commit();
      return response($response);
  }

  /*########################################## END NEWS ##############################################*/


  /******************************************** YAYASAN *************************************************/

  // list data yayasan
  public function select_m_yayasan(){

    $select_m_yayasan = DB::connection('mysql2')->select('CALL select_m_yayasan()');
    $y=0;
    $z=1;
    foreach($select_m_yayasan as $item){
      $data_y[$y]=[
        'no'=>$z++,
        //'id'=>$item->id_m_yayasan,
        'id'=>$item->id_m_yayasan,
        'nama'=>$item->nama
      ];
      $y++;
    }

    $data=[
      'yayasan'=>$data_y
    ];
    return response()->json($data);
  }

  // add yayasan
  public function add_yayasan(Request $request){

    DB::beginTransaction();
    try {
    $nama_yayasan = $request->nama_yayasan;
    $alamat = $request->alamat;
    $deskripsi=$request->deskripsi;
    $email = $request->email;
    $telepon = $request->telepon;
    $password = $request->password;
    $confirm_password = $request->confirm_password;
    $url_youtube = $request->url_youtube;
    $url_linkedin = $request->url_linkedin;
    $url_website = $request->url_website;
    $id_twitter = $request->id_twitter;
    $id_instagram = $request->id_instagram;
    $id_facebook = $request->id_facebook;

    $call_db = DB::connection('mysql2')->select(
      "call add_m_yayasan(
        '$nama_yayasan',
        '$email',
        '$telepon', 
        '$alamat',
        '$deskripsi',
        '$password',
        '$url_youtube', 
        '$url_linkedin', 
        '$url_website', 
        '$id_twitter', 
        '$id_instagram', 
        '$id_facebook'
      )"
    );
    
    if($call_db[0]->v_out == 'Sukses'){
        $id_m_yayasan = $call_db[0]->id_m_yayasan;
        $response='Sukses Insert';
      }else{
        $id_m_yayasan = '';
        $response='Gagal Insert';
    }
    } catch (\Throwable $th) {
      $response =  $th;
      DB::rollback();
    }
      DB::commit();
      return response()->json(['status' => $response, 'id'=>$id_m_yayasan]);
      
  }

  // upload gambar yayasan
  public function upload_gambar_campaigner(Request $request)
    {
    
      
        if ($request->hasFile('myFile')) {

          $image  = $request->file('myFile');
          $name   = $request->id_m_yayasan.'.'.'jpg';
          $destinationPath = storage_path('/app/public/campaigner/');
          $request->file('myFile')->move($destinationPath, $name);
          
          $updateFileName = DB::connection('mysql2')->table('m_yayasan')
                ->where('id_m_yayasan', $request->id_m_yayasan)
                ->update(['foto_profile' => "/app/public/campaigner/".$name]);
 
          return response()->json(['data'=>"poto berhasil diupload"]);

        }else{

          return response()->json(['data'=>"gagal"]);
      
        }
    }

    public function select_edit_m_yayasan($id_yayasan){
      $id_decrypt = $id_yayasan;
      $select_m_yayasan = DB::connection('mysql2')->select("CALL select_edit_m_yayasan('$id_decrypt')");
      $y=0;
      $z=1;
        $data_y=[
          'id'=>$select_m_yayasan[0]->id_m_yayasan,
          'nama'=>$select_m_yayasan[0]->nama,
          'alamat'=>$select_m_yayasan[0]->alamat,
          'deskripsi'=>$select_m_yayasan[0]->deskripsi,
          'email'=>$select_m_yayasan[0]->email,
          'telepon'=>$select_m_yayasan[0]->telepon,
          'password'=>$select_m_yayasan[0]->password,
          'foto_profile'=>$select_m_yayasan[0]->foto_profile,
          'url_youtube'=>$select_m_yayasan[0]->url_youtube,
          'url_linkedin'=>$select_m_yayasan[0]->url_linkedin,
          'url_website'=>$select_m_yayasan[0]->url_website,
          'id_twitter'=>$select_m_yayasan[0]->id_twitter,
          'id_instagram'=>$select_m_yayasan[0]->id_instagram,
          'id_facebook'=>$select_m_yayasan[0]->id_facebook
        ];

      $data=[
        'yayasan'=>$data_y
      ];
      return response()->json($data);
    }

    public function edit_yayasan(Request $request){

      DB::beginTransaction();
      try {
      $id =$request->id;
      $nama_yayasan = $request->nama_yayasan;
      $alamat = $request->alamat;
      $deskripsi=$request->deskripsi;
      $email = $request->email;
      $telepon = $request->telepon;
      $password = $request->password;
      $confirm_password = $request->confirm_password;
      $url_youtube = $request->url_youtube;
      $url_linkedin = $request->url_linkedin;
      $url_website = $request->url_website;
      $id_twitter = $request->id_twitter;
      $id_instagram = $request->id_instagram;
      $id_facebook = $request->id_facebook;

      $call_db = DB::connection('mysql2')->select(
        "call edit_m_yayasan(
          '$id',
          '$nama_yayasan',
          '$email',
          '$telepon', 
          '$alamat',
          '$deskripsi',
          '$password',
          '$url_youtube', 
          '$url_linkedin', 
          '$url_website', 
          '$id_twitter', 
          '$id_instagram', 
          '$id_facebook'
        )"
      );
      
      if($call_db[0]->v_out == 'Sukses'){
        $response = 'Sukses Insert';
      }else{
        $response = 'Gagal Insert';
      }
      } catch (\Throwable $th) {
        $response =  $th;
        DB::rollback();
      }
        DB::commit();
        return response()->json(['status' => $response]);
    }

    public function delete_yayasan($id){
      
      DB::beginTransaction();
      try {

      $select_procedure = DB::connection('mysql2')->select(DB::raw("CALL delete_m_yayasan('$id')"));
      
      $response = array("status"=>"sukses");
      } catch (\Throwable $th) {
        $response =  array("status"=>"gagal", "message"=>$th);
        DB::rollback();
      }
        DB::commit();
        return response()->json($response);
    }


  /*########################################## END YAYASAN ##############################################*/


  
  /******************************************** OTHERS *************************************************/
  
    

    // function untuk menampilkan poto pendanaan
    public function tampilPoto($id){
      $id_decrypt = $this->decrypt($id);
      $selectImage = DB::connection('mysql2')->select("select * from pendanaan_sosial_cerita where id_pendanaan_sosial = ".$id_decrypt." ");
      
      $path_file = storage_path($selectImage[0]->lokasi_gambar);
      if (file_exists($path_file)) {
          $file = file_get_contents($path_file);
          return response($file, 200)->header('Content-Type', 'image/jpeg');
      }
      $res['success'] = false;
      $res['message'] = "Poto Tidak Ada";
      return $res;


    }

    // function untuk menampilkan poto yayasan
    public function tampilPotoYayasan($nama_yayasan){
      $decrypt_nama  = $this->decrypt($nama_yayasan);
      $selectImage = DB::connection('mysql2')->select("select * from m_yayasan where m_yayasan.nama = '".$decrypt_nama."' ");
      
      $path_file = storage_path($selectImage[0]->foto_profile);
      if (file_exists($path_file)) {
          $file = file_get_contents($path_file);
          return response($file, 200)->header('Content-Type', 'image/jpeg');
      }
      $res['success'] = false;
      $res['message'] = "Poto Tidak Ada";
      return $res;


    }

    /*########################################## END OTHERS ##############################################*/

    public function select_dashboard_admin(){
      $select_jumlah_dashboard_admin = DB::connection('mysql2')->select('CALL select_jumlah_dashboard_admin()');
      $select_dashboard_admin = DB::connection('mysql2')->select(
      "select a.id_pendanaan_sosial, a.nama_pendanaan, a.id_status_pendanaan, b.lokasi_gambar from pendanaan_sosial a 
      join pendanaan_sosial_cerita b 
      on a.id_pendanaan_sosial = b.id_pendanaan_sosial 
      ORDER BY a.id_pendanaan_sosial desc limit 5");

      $y=0;
      $z=1;

      if($select_dashboard_admin){
        foreach($select_dashboard_admin as $item){
          $data_p[$y]=[
            'no'=>$z++,
            'id'=>$item->id_pendanaan_sosial,
            'nama'=>$item->nama_pendanaan,
            'id_status_pendanaan'=>$item->id_status_pendanaan,
            'lokasi_gambar'=>$item->lokasi_gambar,
          ];
          $y++;
        }
      }else{
        $data_p=[];
      }

      if($select_jumlah_dashboard_admin){
        $data_j=[
          'jumlah_total'=>$select_jumlah_dashboard_admin[0]->v_count_total = $select_jumlah_dashboard_admin[0]->v_count_total ? $select_jumlah_dashboard_admin[0]->v_count_total : 0,
          'jumlah_berjalan'=>$select_jumlah_dashboard_admin[0]->v_count_berjalan = $select_jumlah_dashboard_admin[0]->v_count_berjalan ? $select_jumlah_dashboard_admin[0]->v_count_berjalan : 0,
          'jumlah_pengajuan'=>$select_jumlah_dashboard_admin[0]->v_count_pengajuan = $select_jumlah_dashboard_admin[0]->v_count_pengajuan ? $select_jumlah_dashboard_admin[0]->v_count_pengajuan : 0,
          'jumlah_selesai'=>$select_jumlah_dashboard_admin[0]->v_count_selesai = $select_jumlah_dashboard_admin[0]->v_count_selesai ? $select_jumlah_dashboard_admin[0]->v_count_selesai : 0
        ];
      }else{
        $data_j=[];
      }

      $data=[
        'pendanaan'=>$data_p,
        'jumlah_pendanaan'=>$data_j
      ];
      return response()->json($data);
    }

    /******************************************** DASHBOARD EDIT USER DONATUR *************************************************/

    // select edit user dashboard donatur
    public function select_edit_user($id){
      
      $select_edit_users = DB::connection('mysql2')->select("CALL select_edit_user('$id')");

      $y=0;
      $z=1;
        $data_y=[
          'id'=>$select_edit_users[0]->id,
          'name'=>$select_edit_users[0]->name,
          'email'=>$select_edit_users[0]->email,
          'password'=>$select_edit_users[0]->password,
          'no_hp'=>$select_edit_users[0]->no_hp,
          'va_number'=>$select_edit_users[0]->va_number,
        ];
      $data=[
        'users'=>$data_y
      ];
      return response()->json($data);
    }

    // select edit user dashboard donatur
    public function edit_user(Request $request){

      DB::beginTransaction();
      try {
      $id =$request->id;
      $name = $request->name;
      $email = $request->email;
      if (Hash::needsRehash($request->password)) {
        $plainPassword = Hash::make($request->password);
      
      }else if (!Hash::needsRehash($request->password)){
        $plainPassword = $request->password;
      }
      $password= $plainPassword;
      $no_hp = $request->no_hp;

      $call_db = DB::connection('mysql2')->select("CALL edit_password('$name', '$email', '$password')");
      
      if($call_db[0]->v_out == 'Sukses'){
        $response = 'Sukses Insert';
      }else{
        $response = 'Gagal Insert';
      }
      } catch (\Throwable $th) {
        $response =  $th;
        DB::rollback();
      }
        DB::commit();
        return response()->json(['status' => $response]);
    }

    /******************************************** DASHBOARD EDIT USER DONATUR *************************************************/


    /******************************************** START RIWAYAT MUTASI DASHBOARD ADMIN *************************************************/

    public function select_riwayat_mutasi_admin(){
      
      $select = DB::connection('mysql2')->select("CALL select_riwayat_mutasi_admin()");

      $y=0;
      $z=1;
      foreach($select as $item){
        $data_y[$y]=[
          'id'=>$z++,
          'id_pendanaan_sosial'=>$item->id_pendanaan_sosial,
          'tipe_pendanaan'=>$item->id_tipe_pendanaan,
          'nama_pendanaan'=>$item->nama_pendanaan,
          'dana_masuk'=>number_format($item->dana_masuk,0,",","."),
          'id_status_pendanaan'=>$item->id_status_pendanaan,
          'jumlah_donatur'=>$item->jumlah_donatur
        ];
      $y++;
    }

    $data=[
      'mutasi'=> $data_y == null ? '' : $data_y
    ];
      return response()->json($data);
    }

    public function select_riwayat_mutasi_admin_detail($id){
      
      $select = DB::connection('mysql2')->select("select a.id_users, a.dana_masuk, a.created_at, b.name FROM list_pendanaan_masuk a JOIN users b ON a.id_users = b.id WHERE a.id_pendanaan_sosial='$id' order by created_at desc");

      $y=0;
      $z=1;
      if($select==null){
        $data_y='';
      }else{
      foreach($select as $item){
        $sec = strtotime($item->created_at);
        $newdate = date ("d/m/Y H:i", $sec);  

          $data_y[$y]=[
            'id'=>$z++,
            'id_pendanaan_sosial'=>$item->id_users == null ? '' : $item->id_users,
            'dana_masuk'=>$item->dana_masuk == null ? '' : number_format($item->dana_masuk,0,",","."),
            'nama_donatur'=>$item->name == null ? '' : $item->name,
            'tgl_transfer'=>$newdate,
            'status'=>'Transfer'
          ];
        $y++;
      }
    }

    $data=[
      'mutasi'=> $data_y == null ? '' : $data_y
    ];
      return response()->json($data);
    }


    /******************************************** END RIWAYAT MUTASI DASHBOARD ADMIN *************************************************/

    
    /******************************************** START KELOLA PENGGUNA DASHBOARD ADMIN *************************************************/

    public function select_menu_dashboard_admin(){
      
      $select_menu = DB::connection('mysql2')->select("select id_m_role_menu, label, link FROM m_role_menu");
      $select_role = DB::connection('mysql2')->select("select id_m_role_user, nama, id_m_role_menu FROM m_role_user");
      $select_admin = DB::connection('mysql2')->select("select a.id, a.name, b.nama FROM users a join m_role_user b ON b.id_m_role_user = a.id_role_user WHERE a.id_status_user = 1");
      
      $y=0;
      $z=1;
      if($select_menu==null){
        $data_y='';
      }else{
      foreach($select_menu as $item){
          $data_y[$y]=[
            'id'=>$z++,
            'id_m_role_menu'=>$item->id_m_role_menu == null ? '' : $item->id_m_role_menu,
            'value'=>$item->label == null ? '' : $item->label,
            'checked'=>true
          ];
        $y++;
      }
    }

    $y=0;
    $z=1;
    if($select_role==null){
      $data_z='';
    }else{

      foreach($select_role as $item){

        $count = explode(',' , $item->id_m_role_menu);
        $count = count($count);
        $data_z[$y]=[
          'id'=>$z++,
          'id_m_role_user'=>$item->id_m_role_user == null ? '' : $item->id_m_role_user,
          'nama'=>$item->nama == null ? '' : $item->nama,
          'jumlah_akses'=>$count,
          'id_m_role_menu'=>$item->id_m_role_menu == null ? '' : $item->id_m_role_menu
        ];
        $y++;
      }
    }

    $y=0;
    $z=1;
    if($select_admin==null){
      $data_x='';
    }else{
      foreach($select_admin as $item){

        $data_x[$y]=[
          'id'=>$z++,
          'id_user'=>$item->id == null ? '' : $item->id,
          'username'=>$item->name == null ? '' : $item->name,
          'tipe_role'=>$item->nama == null ? '' : $item->nama
        ];
        $y++;
      }
    }

      $data=[
        'menu'=> $data_y == null ? '' : $data_y,
        'role'=> $data_z == null ? '' : $data_z,
        'user'=> $data_x == null ? '' : $data_x,
      ];
        return response()->json($data);
    }

    public function add_user_role_menu(Request $request){
      $role = rtrim($request->role,",");
      $role_name = $request->name;
      
      $insert = DB::connection('mysql2')->insert('insert into m_role_user (nama, id_m_role_menu) values (?, ?)',  [$role_name, $role]);

      if($insert){
        $response='sukses';
      }else{
        $response='gagal';
      }
        return response()->json($response);
    }

    public function edit_user_role_menu(Request $request){
      $role = rtrim($request->role,",");
      $role_name = $request->name;
      
      $update = DB::connection('mysql2')->table('m_role_user')->where('id_m_role_user', $request->id)->update(['nama'=>$role_name, 'id_m_role_menu'=>$role]);

      if($update){
        $response='sukses';
      }else{
        $response='gagal';
      }
        return response()->json($response);
    }

    public function select_edit_user_role_menu($id){
      
      $select_role = DB::connection('mysql2')->select("select id_m_role_user, nama, id_m_role_menu FROM m_role_user where id_m_role_user = '$id'");
      $id=empty($select_role[0]->id_m_role_menu) ? '' : $select_role[0]->id_m_role_menu;
      $nama_role=empty($select_role[0]->nama) ? '' : $select_role[0]->nama;

      $select_role = DB::connection('mysql2')->select("select id_m_role_menu, label, case when id_m_role_menu IN ($id) then 'checked' ELSE 'not checked' end as status_check FROM m_role_menu");

      $z=1;
      $y=0;
      foreach($select_role as $item){
        $data_z[$y]=[
          'id'=>$z++,
          'id_m_role_menu'=>$item->id_m_role_menu == null ? '' : $item->id_m_role_menu,
          'value'=>$item->label == null ? '' : $item->label,
          'checked'=>$item->status_check == 'checked' ? true : false
        ];
        $y++;
      }

      $data=[
        'role'=>$data_z,
        'nama_role'=>$nama_role
      ];
      
      return response()->json($data);
    }

    public function delete_user_role_menu($id){
      
      $delete = DB::connection('mysql2')->table('m_role_user')->where('id_m_role_user', $id)->delete();

      if($delete){
        $response='sukses';
      }else{
        $response='gagal';
      }
        return response()->json($response);
    }

    public function select_edit_user_admin($id){
      
      $select_role = DB::connection('mysql2')->select("select a.id, a.name, a.email, a.no_hp, b.nama as role_name, b.id_m_role_user FROM users a join m_role_user b on a.id_role_user = b.id_m_role_user where id = '$id'");

      $data=[
        'id_user'=>$select_role[0]->id == null ? '' : $select_role[0]->id,
        'username'=>$select_role[0]->name == null ? '' : $select_role[0]->name,
        'email'=>$select_role[0]->email == null ? '' : $select_role[0]->email,
        'no_hp'=>$select_role[0]->no_hp == null ? '' : $select_role[0]->no_hp,
        'role_name'=>$select_role[0]->role_name == null ? '' : $select_role[0]->role_name,
        'id_m_role_user'=>$select_role[0]->id_m_role_user == null ? '' : $select_role[0]->id_m_role_user
      ];
      
      return response()->json($data);
    }

   /******************************************** END KELOLA PENGGUNA DASHBOARD ADMIN *************************************************/

}