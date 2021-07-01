<?php


namespace App\Http\Controllers\SosialAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as Image;
use Barryvdh\DomPDF\Facade as PDF;
use App\Http\Controllers\SosialAdmin\IntegrationController;
use Illuminate\Support\Facades\Crypt;

class TransferController extends Controller
{

  private function encrypt($value){
    return Crypt::encrypt($value);
  }

  private function decrypt($value){
    return Crypt::decrypt($value);
  }

   public function check_donasi_temp(Request $request){
    
      $id_pendanaan_sosial = $this->decrypt($request->id_pendanaan_sosial);
      $nominal = $request->nominal;
      $id_user=$request->id_user;

      $get_va = DB::connection('mysql2')->select("select va_number from users where id = '$id_user'");
      if($get_va[0]->va_number == null){
        $integration_controller = new IntegrationController;
        $generate_va = $integration_controller->generateVA_BNI_Sosial($id_user);
        if($generate_va!=='000'){
          return response()->json(['status' => 'failed_generate_va']);
        }else{
          $call_db = DB::connection('mysql2')->select("call check_donasi_temp('$id_pendanaan_sosial','$id_user')");

          if($call_db[0]->v_out=='Delete'){
            return response()->json(['status' => $call_db[0]->v_out, 'dana_masuk'=>$call_db[0]->dana_masuk, 'nama_pendanaan'=>$call_db[0]->nama_pendanaan]);
          }else{
            return response()->json(['status' => $call_db[0]->v_out]);
          }
        }
      }else{
        $call_db = DB::connection('mysql2')->select("call check_donasi_temp('$id_pendanaan_sosial','$id_user')");
        if($call_db[0]->v_out=='Delete'){
          return response()->json(['status' => $call_db[0]->v_out, 'dana_masuk'=>$call_db[0]->dana_masuk, 'nama_pendanaan'=>$call_db[0]->nama_pendanaan]);
        }else{
          return response()->json(['status' => $call_db[0]->v_out]);
        }
      }

   }

   public function add_donasi_temp(Request $request){
    
    $id_pendanaan_sosial = $this->decrypt($request->id_pendanaan_sosial);
    $nominal = $request->nominal;
    $id_user=$request->id_user;

    DB::beginTransaction();
    try {
    $call_db = DB::connection('mysql2')->select(
      "call add_donasi_temp(
        '$id_pendanaan_sosial',
        '$nominal',
        '$id_user'
      )"
    );
    
    if($call_db[0]->v_out == 'Sukses'){
        $id_temp_pendanaan = $call_db[0]->id_temp;
        $response='Sukses Insert';
      }else{
        $id_temp_pendanaan = '';
        $response='Gagal Insert';
    }
    } catch (\Throwable $th) {
      $response =  $th;
      DB::rollback();
    }
      DB::commit();
      return response()->json(['status' => $response, 'id_temp_pendanaan'=>$id_temp_pendanaan]);
  }

  public function get_temp_donasi(Request $request){

    $id_temp = $request->id_temp;
    $id_user = $request->id_user;

    $status_pembayaran = DB::connection('mysql2')->select("select COUNT(id_temp) AS status_pembayaran FROM temp_pendanaan_masuk WHERE id_temp = '$id_temp' AND id_users = '$id_user' limit 1");
    if($status_pembayaran[0]->status_pembayaran == 1){
      $call_db = DB::connection('mysql2')->select("select a.va_number, a.id_pendanaan_sosial, a.dana_masuk, a.no_invoice, b.name, b.email, b.no_hp, c.nama_pendanaan from temp_pendanaan_masuk a 
      join users b on a.id_users = b.id 
      join pendanaan_sosial c on c.id_pendanaan_sosial = a.id_pendanaan_sosial
      where a.id_users = '$id_user' and a.id_temp = '$id_temp' limit 1");
    }else{
      $call_db = DB::connection('mysql2')->select("select a.va_number, a.id_pendanaan_sosial, a.dana_masuk, a.no_invoice, b.name, b.email, b.no_hp, c.nama_pendanaan from list_pendanaan_masuk a 
      join users b on a.id_users = b.id 
      join pendanaan_sosial c on c.id_pendanaan_sosial = a.id_pendanaan_sosial
      where a.id_users = '$id_user' ORDER BY a.id_list_pendanaan DESC LIMIT 1;");
    }    

    if($call_db==null){
      return response()->json(['status'=>'Gagal']);
    }else{
      $data=[
        'status'=>'Sukses',
        'id_pendanaan_sosial'=>$call_db[0]->id_pendanaan_sosial,
        'va_number'=>$call_db[0]->va_number,
        'dana_masuk'=>$call_db[0]->dana_masuk,
        'name'=>$call_db[0]->name,
        'email'=>$call_db[0]->email,
        'no_hp'=>$call_db[0]->no_hp,
        'nama_pendanaan'=>$call_db[0]->nama_pendanaan,
        'status_pembayaran'=>$status_pembayaran[0]->status_pembayaran,
        'no_invoice'=>$call_db[0]->no_invoice
      ];

      return response()->json($data);  
    }
  }

  public function download_invoice(Request $request){
    
    $pdf=PDF::loadView('invoice.invoice_template',
    [
        'id_temp'=>$request->id_temp,
        'id_user' => $request->id_user,
        'id_pendanaan_sosial' =>$request->id_pendanaan_sosial,
        'va_number'=>$request->va_number,
        'id_user'=>$request->id_user,
        'name'=>$request->name,
        'email' =>$request->email,
        'no_hp' =>$request->no_hp,
        'dana_masuk'=>$request->dana_masuk,
        'nama_pendanaan'=>$request->nama_pendanaan,
        'tgl_invoice'=> date("d/m/Y"),
        'no_invoice'=>$request->no_invoice
        ]
    );

    $pdf->setPaper('A4','portrait');
    $path = storage_path('app/public/invoice');
    $fileName =  'Invoice-'.$request->id_pendanaan_sosial.'-'.$request->id_user.'.pdf' ;
    $pdf->save($path . '/' . $fileName);

    $file = file_get_contents($path . '/' . $fileName);
    return response($file, 200)->header('Content-Type', 'pdf');
  }

}