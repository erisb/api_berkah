<?php


namespace App\Http\Controllers\BorrowerAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

use App\Borrower;
use App\BorrowerLogScorring;
use App\BorrowerPersyaratanPendanaan;
use App\BorrowerPendanaan;
use App\BorrowerLogPendanaan;
use App\BorrowerScorringTotal;
use App\BorrowerScorringPersonal;
use App\BorrowerScorringPendanaan;
use App\BorrowerTipePendanaan;
use App\Borrowerdeskripsiproyek;

use App\BniEnc;

use App\BorrowerInvoice;
use App\BorrowerPembayaran;
use App\BorrowerLogPembayaran;
use App\Proyek;
use DB;

class ProsessController extends Controller
{

    //Development
    private const CLIENT_ID = '19811';
    private const KEY = '711fb67e17808c9c1aba34308292560f';
    private const API_URL = 'https://apibeta.bni-ecollection.com/';
    // set some constant

  public function postJenisPendanaan(Request $request)
  {
    $newData = new BorrowerTipePendanaan;
    $newData->pendanaan_nama = $request->pendanaanNama;
    $newData->pendanaan_keterangan = $request->pendanaanKeterangan;
    $newData->save();

    $response = 'Success';

    return response($response);
  }

  public function postDeleteJenis(Request $request)
  {
    $deleteData = BorrowerTipePendanaan::where('tipe_id',$request->pendanaanId)->first();
    $deleteData->delete();

    $deleteList = BorrowerPersyaratanPendanaan::where('tipe_id',$request->pendanaanId)->get();

    for($i=0;$i < sizeof($deleteList);$i++)
    {
      $deleteList[$i]->delete();
    }

    $response = 'Success';

    return response($response);
    
  }

  
  public function updateJenisPendanaan(Request $request)
  {
    $getData = BorrowerTipePendanaan::where('tipe_id',$request->pendanaanId)->first();
    $getData->pendanaan_nama = $request->pendanaanNama;
    $getData->pendanaan_keterangan = $request->pendanaanKeterangan;
    $getData->save();

    $response = 'Success';

    return response($response);

  }


  public function updateTipeJenis(Request $request)
  {

    if($request->idList)
    {
      for($i=0;$i < count($request->idList);$i++)
      {
        $getList = BorrowerPersyaratanPendanaan::where('persyaratan_id',$request->idList[$i])->first();
        $getList->persyaratan_nama = $request->listValue[$i];
        $getList->save();
      }

    }

    if($request->checkBox)
    {
      $updateMandatory = BorrowerPersyaratanPendanaan::whereIn('persyaratan_id',$request->checkBox)->get();
      for($i=0; $i < count($updateMandatory);$i++)
      {
        $updateMandatory[$i]->persyaratan_mandatory = 1;
        $updateMandatory[$i]->save();
      }
    }

    if($request->checkBox && $request->pendanaanId)
    {
      $UnUpdateMandatory = BorrowerPersyaratanPendanaan::whereNotIn('persyaratan_id',$request->checkBox)->where('tipe_id',$request->pendanaanId)->get();
  
      for($i=0;$i < count($UnUpdateMandatory);$i++)
      {
        $UnUpdateMandatory[$i]->persyaratan_mandatory = 0;
        $UnUpdateMandatory[$i]->save();
      }

    }

    if($request->deleteList)
    {
      $deleteData = BorrowerPersyaratanPendanaan::whereIn('persyaratan_id',$request->deleteList)->get();
      for($i=0;$i < count($deleteData);$i++)
      {
        $deleteData[$i]->delete();
      }
    }

    $response = 'Success';

    return response($response);

  }



  public function newPostTipeJenis(Request $request)
  {
    // print_r($request->data);
    // $dataItem = json_encode($request->data);
    $getItem = json_decode($request->data);

    // print_r($dataItem);
    // echo $getItem->idListNew;
    // var_dump($getItem->listValue);
    for($i=0;$i < sizeof($getItem->listValue);$i++)
    {
        $saveData = new BorrowerPersyaratanPendanaan;
        $saveData->tipe_id = $getItem->idListNew;
        $saveData->persyaratan_type = $getItem->addSelected[$i];
        $saveData->persyaratan_nama = $getItem->listValue[$i];
        $saveData->persyaratan_mandatory = $getItem->mandatoryList[$i];
        $saveData->save();
    }


    $response = 'Success';

    return response($response);
  }

  public function postTotalScoring(Request $request)
  {
    //brw user
    $getData = Borrower::where('brw_id',$request->idBorrower)->first();
    if(!empty($getData)){
      if($getData->status == 'pending')
      {
        $getData->status = 'active';
        $getData->save();
      }
    }
    
    //brwscoringpersonal
    $cekSPersonal = BorrowerScorringPersonal::where('brw_id',$request->idBorrower)->first();
    $savePersonal = new BorrowerScorringPersonal;
    if(empty($cekSPersonal)){
      $savePersonal->brw_id = $getData->brw_id;
      $savePersonal->nilai = $request->scorePersonal;
      $savePersonal->save();
      $idPersonal = $savePersonal->scorring_personal_id;
    }else{
      $savePersonal = BorrowerScorringPersonal::where('brw_id',$request->idBorrower)->update(['nilai' => $request->scorePersonal]);
      $idPersonal = BorrowerScorringPersonal::orderby('updated_at','desc')->first()->scorring_personal_id;;
    }
    
    //brwscoringpendanaan
    $cekSPendanaan = BorrowerScorringPendanaan::where('pendanaan_id',$request->idPendanaan)->first();
    $savePendanaan = new BorrowerScorringPendanaan;
    if(empty($cekSPendanaan)){
      $savePendanaan->pendanaan_id = $request->idPendanaan;
      $savePendanaan->scorring_judul = " ";
      $savePendanaan->scorring_nilai = $request->scorePendanaan;
      $savePendanaan->user_create = 'admin brw';
      $savePendanaan->save();
      $idPendanaan = $savePendanaan->scorring_pendanaan_id;
    }else{
      $savePendanaan = BorrowerScorringPendanaan::where('pendanaan_id',$request->idPendanaan)->update(['scorring_nilai' => $request->scorePendanaan]);
      $idPendanaan =  BorrowerScorringPendanaan::orderby('updated_at','desc')->first()->scorring_pendanaan_id;
    }
    
    //brwlogscoring
      $saveLog = new BorrowerLogScorring;
      $saveLog->scorring_personal_id = $idPersonal;
      $saveLog->scorring_pendanaan_id = $idPendanaan;
      $saveLog->user_create = 'admin brw';
      $saveLog->save();
    
    //get data pendaan
    $getPendanaan = BorrowerPendanaan::where('pendanaan_id',$request->idPendanaan)->first();

    //set deskripsi proyek tbl deskripsi_proyek
    $deskripsiproyek = new Borrowerdeskripsiproyek;
    $deskripsiproyek->deskripsi = $getPendanaan->detail_pendanaan;
    $deskripsiproyek->save();

    //simpanproyek
    $newProyek = new Proyek;
    
    $newProyek->nama = $getPendanaan->pendanaan_nama;
    $newProyek->akad = $getPendanaan->pendanaan_akad;
    $newProyek->total_need = $getPendanaan->pendanaan_dana_dibutuhkan;
    $newProyek->harga_paket = 1000000;
    $newProyek->tgl_mulai = $getPendanaan->estimasi_mulai;
    $newProyek->tenor_waktu = $getPendanaan->durasi_proyek;
    $newProyek->profit_margin = 0;
    $newProyek->terkumpul = 0;
    $newProyek->status = 1;
    $newProyek->id_deskripsi = $deskripsiproyek->id;
    $newProyek->save();
    
    //updatebrwpendanaan
    $updateBrwPendanaan = BorrowerPendanaan::where('pendanaan_id', $request->idPendanaan)->update(['status' => 1,'id_proyek' => $newProyek->id]);
    

    $total = $request->scorePendanaan + $request->scorringPersonal;
    $saveData = new BorrowerScorringTotal;
    
    $saveData->pendanaan_id = $request->idPendanaan;
    $saveData->brw_id = $request->idBorrower;
    $saveData->scorring_total = $total;
    $saveData->scorring_grade = '';
    $saveData->scorring_grade = 'A';
    $saveData->save();
    // die();
    //$response = 'Success';
    
    $response = [
        'status' => 'Success',
        'proyek_id' => $newProyek->id

    ];
    return response()->json($response);

  }


  public function rejectScorringBorrower(Request $request)
  {
    $getData = Borrower::where('brw_id',$request->idBorrower)->first();
    $status =  $getData->status;
    
    if($status == 'active'){
      
      //update status brw_pendanaan
      $updatePendanaan = BorrowerPendanaan::where('pendanaan_id',$request->idPendanaan)->first();
      $updatePendanaan->status = 5;
      $updatePendanaan->save(); 

      //update brw log pendanaan
      $saveLogPendanaan = new BorrowerLogPendanaan;
      $saveLogPendanaan->pendanaan_id = $request->idPendanaan;
      $saveLogPendanaan->brw_id = $getData->brw_id;
      $saveLogPendanaan->status = 0;
      $saveLogPendanaan->keterangan = "pendanaan ditolak";
      $saveLogPendanaan->save();

    }elseif($status == 'pending'){
      
      $updateUser = Borrower::where('brw_id',$request->idBorrower)->first();
      $updateUser->status = 'rejected';
      $updateUser->save();

      $saveLogPendanaan = new BorrowerLogPendanaan;
      $saveLogPendanaan->pendanaan_id = $request->idPendanaan;
      $saveLogPendanaan->brw_id = $getData->brw_id;
      $saveLogPendanaan->status = 0;
      $saveLogPendanaan->keterangan = "pendanaan dan user ditolak";
      $saveLogPendanaan->save();
    }

    $response = 'Success';
    return response($response);

  }

  public function getListJenis(Request $request)
  {
    // echo $request->tipe_id;die;
    $getList = BorrowerPersyaratanPendanaan::where('tipe_id',$request->tipe_id)->where('persyaratan_type',1)->get();
    
    return response($getList);
    // var_dump($getList);
  }

  
  public function getListJenisA(Request $request)
  {
    $getList = BorrowerPersyaratanPendanaan::where('tipe_id',$request->tipe_id)->where('persyaratan_type',2)->get();
    
    return response($getList);
  }
  
  public function getListJenisB(Request $request)
  {
    $getList = BorrowerPersyaratanPendanaan::where('tipe_id',$request->tipe_id)->where('persyaratan_type',3)->get();
    
    return response($getList);

  }

  public function generateVABNI_Borrower($username, $id_proyek){
        
        $date = \Carbon\Carbon::now()->addYear(4);
        $data_proyek = Proyek::select('tgl_mulai', 'id')->where('id', $id_proyek)->first();
        $year =  substr($data_proyek->tgl_mulai,2,2);
        $last_digit = sprintf("%04d", $data_proyek->id);
        

        $user = Borrower::where('username', $username)->first();
        $data = [
            'type' => 'createbilling',
            'client_id' => self::CLIENT_ID,
            'trx_id' => $user->brw_id,
            'trx_amount' => '0',
            'customer_name' => $user->username,
            'customer_email' => $user->email,
            'virtual_account' => '988'.self::CLIENT_ID.'02'.$year.$last_digit,
            'datetime_expired' => $date->format('Y-m-d').'T'.$date->format('H:i:sP'),
            'billing_type' => 'o',
        ];

        $encrypted = BniEnc::encrypt($data,self::CLIENT_ID,self::KEY);

        $client = new Client(); //GuzzleHttp\Client
        $result = $client->post(self::API_URL, [
            'json' => [
                'client_id' => self::CLIENT_ID,
                'data' => $encrypted,
            ]
        ]);

        $result = json_decode($result->getBody()->getContents());
        // var_dump($result);die;
        if($result->status !== '000'){
          $status = 'false';
            return $status;
        }
        else{
            $decrypted = BniEnc::decrypt($result->data,self::CLIENT_ID,self::KEY);
            //return json_encode($decrypted);
            $updateDetails = [
                'va_number' =>  $decrypted['virtual_account']
            ];
            
            BorrowerPendanaan::where('brw_id',$user->brw_id)
            ->where('id_proyek',$id_proyek)
                ->update($updateDetails);
            
                $status = 'true';
                return $status;
            // return view('pages.user.add_funds')->with('message','VA Generate Success!');
        }
    }

  public function prosesCairDana($brw_id, $proyek_id){
    $Rekening = DB::table('brw_rekening')
		  ->where('brw_rekening.brw_id','=', $brw_id)
		  ->first();
		
		$total_plafon = $Rekening->total_plafon;
		$total_sisa = $Rekening->total_sisa;
		$total_terpakai = $Rekening->total_terpakai;
		
		 // summary pendaan
		$pendanaanAktif = DB::table('pendanaan_aktif')
                ->where('proyek_id', $request->id_proyek)->sum('total_dana');

     // update status pendanaan
		$pendanaan = BorrowerPendanaan::where('brw_id',$request->brw_id)->where('id_proyek',$request->id_proyek)->first();  
		$pendanaan->status = 7; 
    $pendanaan->status_dana = 1; 
    $pendanaan->dana_dicairkan = $pendanaanAktif; 
		$pendanaan->update();
				
		// update plafon
		$plafon_terpakai 		= $total_terpakai + $pendanaanAktif;
		$plafon_sisa		 	  = $total_plafon -  $plafon_terpakai;
		
		// update plafon
		$Rekening_update    = BorrowerRekening::where('brw_id',$request->brw_id)->first();  
		$Rekening_update->total_terpakai = $plafon_terpakai; 
		$Rekening_update->total_sisa = $plafon_sisa; 
		$Rekening_update->update();
		
    // generate invoice 
    $this->generate_invoice($brw_id, $proyek_id);


  }

  public function generateInvoice($brw_id, $proyek_id){

   
    $pendanaan = DB::table('brw_pendanaan as a')
      ->join('proyek as b', 'a.id_proyek', '=', 'b.id')
      ->select('a.*','b.id', 'b.nama', 'b.profit_margin', 'b.total_need', 'b.harga_paket', 'b.terkumpul', 'b.status as status_proyek', 'b.status_tampil', 'b.tenor_waktu')
      ->where('a.id_proyek', $proyek_id)
      ->where('a.brw_id', $brw_id)
      ->first();

    $MarginProyek = $pendanaan->profit_margin;
    $marginDSI    = 5;
    $tenor_waktu  = $pendanaan->tenor_waktu;
    $hari_ni_invoice    = date('Ymd');
    $hari_ni_jth_tempo  = date('Y-m-d H:i:s');
    $terkumpul          = $pendanaan->dana_dicairkan;
    $dana_pokok         = floor(($terkumpul / $tenor_waktu)/100)*100;
    $imbal_hasil        = floor($dana_pokok*0.2);

    
    //cetak invoice berdasarkan tenor
    for($i=0; $i<$tenor_waktu; $i++){
      $ii = $i + 1;
      $tambahBulanInvoice = strtotime(date("Ymd", strtotime($hari_ni_invoice)) . "+".$i."month");
      $tambahBulanJthTempo = strtotime(date("Y-m-d H:i:s", strtotime($hari_ni_jth_tempo)) . "+".$i."month");
      

      //echo date('Y-m-d H:i:s',$tambahBulanJthTempo).'<br/>';
      $invoice_id = $brw_id.'/'.$proyek_id.'/'.date('Ymd',$tambahBulanInvoice);
      $brw_invoice = new BorrowerInvoice;
      $brw_invoice->invoice_id        = $invoice_id;
      $brw_invoice->brw_id            = $brw_id;
      $brw_invoice->proyek_id         = $pendanaan->id_proyek;
      $brw_invoice->pendanaan_nama    = $pendanaan->pendanaan_nama;
      $brw_invoice->dana_pokok        = $dana_pokok;
      $brw_invoice->imbal_hasil       = $imbal_hasil;
      $brw_invoice->total_bayar       = $dana_pokok+$imbal_hasil;
      $brw_invoice->tgl_jatuh_tempo   = date('Y-m-d H:i:s',$tambahBulanJthTempo);
      $brw_invoice->tgl_bayar         = "";
      $brw_invoice->status_pembayaran = 4;
      $brw_invoice->save();
      
      //echo date('Ymd',$dateOneMonthAdded).'<br/>';
      //$i+1;
    }

    //$uniqDate = date('dmY'); // ambil tanggal untuk jadiin uniq di invoice
    //echo json_encode($pendanaan);



     
      


  }

  public function pembayaran_invoice($brw_id, $proyek_id, $invoice_id){

  }
 

}