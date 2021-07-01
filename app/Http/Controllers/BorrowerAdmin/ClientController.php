<?php

namespace App\Http\Controllers\BorrowerAdmin;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;


use App\Borrower;
use App\BorrowerDetails;
use App\BorrowerTipePendanaan;
use App\BorrowerPendanaan;
use DB;

class ClientController extends Controller
{
  public function getTableJenis()
  {

    $dataGet = BorrowerTipePendanaan::all();
    $i=1;
    $data= array();
    foreach($dataGet as $item)
    {
        $column['id'] = (string) $item->tipe_id;
        $column['no'] = (string) $i++;
        $column['jenisPendanaan'] = (string) $item->pendanaan_nama;
        $column['keteranganPendanaan'] = (string) $item->pendanaan_keterangan;

        $data[] = $column;   
    }

    
    $parsingJSON = array("data" => $data);

    echo json_encode($parsingJSON);

  }


  public function tableBorrowerData()
  {
    // $getData = Borrower::leftJoin('brw_users_details','brw_users.brw_id','=','brw_users_details.brw_id')
    //                    ->leftJoin('brw_scorring_personal','brw_users.brw_id','=','brw_scorring_personal.brw_id')
    //                    ->leftJoin('brw_pendanaan','brw_users.brw_id','=','brw_pendanaan.brw_id')
    //                    ->leftJoin('brw_scorring_total','brw_scorring_total.pendanaan_id','=','brw_pendanaan.pendanaan_id')
    //                   //  ->leftJoin('brw_scorring_pendanaan_new','brw_pendanaan.pendanaan_id','=','brw_scorring_pendanaan_new.pendanaan_id')
    //                    ->where('brw_users.status','pending')
    //                    ->where('brw_pendanaan.status',0)
    //                    ->whereNotNull('brw_users_details.brw_id')
    //                    ->whereNotNull('brw_pendanaan.brw_id')
    //                    ->whereNull('brw_scorring_total.pendanaan_id')
    //                    ->get(
    //                      [
    //                         'brw_users.brw_id',
    //                         'brw_users_details.nama',
    //                         'brw_scorring_personal.nilai',

    //                         'brw_pendanaan.pendanaan_id',
    //                         'brw_pendanaan.pendanaan_nama'
    //                      ]
    //                    );

    // {"data" : "id"},
    //           {"data" : "idPendanaan"},
    //           {"data" : "no"},
    //           {"data" : "pendanaanBorrower"},
    //           {"data" : "namaBorrower"},
    //           {"data" : "nilaiBorrower"},

    $statususer = array('active','pending');
    $getData = BorrowerPendanaan::select('brw_pendanaan.brw_id','brw_pendanaan.pendanaan_id','brw_pendanaan.pendanaan_nama','brw_users_details.nama','brw_users_details.brw_type','brw_users_details.nm_bdn_hukum','brw_users_details.tgl_lahir','brw_users_details.ktp','brw_scorring_personal.nilai as nilai_personal')
    ->leftjoin('brw_users','brw_users.brw_id','=','brw_pendanaan.brw_id')
    ->leftjoin('brw_users_details','brw_users_details.brw_id','=','brw_pendanaan.brw_id')
    ->leftjoin('brw_scorring_personal','brw_scorring_personal.brw_id','=','brw_pendanaan.brw_id')
    ->wherein('brw_users.status',$statususer)
    ->where('brw_pendanaan.status','0')
    ->orderby('pendanaan_id','ASC')
    ->get();
  // echo count($getData)."-";print_r($getData);die();
    $i = 1;
    $data= array();

    foreach($getData as $item)
    {
      if($item->tgl_lahir == NULL || $item->tgl_lahir == 0){
        $tgl_lahir = "-";
      }else{
        $date=date_create($item->tgl_lahir);
        $tgl_lahir = date_format($date,"d M Y");
      }
      $column['id'] = (string) $item->brw_id;
      $column['idPendanaan'] = (string) $item->pendanaan_id;
      $column['no'] = (string) $i++;
      $column['pendanaanBorrower'] = (string) $item->pendanaan_nama;
      if($item->brw_type == 2){
        $judulnama = $item->nama.' / '.$item->nm_bdn_hukum;
      }else{
        $judulnama = $item->nama;
      }
      $column['namaBorrower'] = (string) $judulnama;
      $column['nilaiBorrower'] = (string) $item->nilai_personal;
      $column['tgl_lahir'] = (string) $tgl_lahir;
      $column['ktp'] = (string) $item->ktp;
      $column['brw_type'] = (string) $item->brw_type;
     

      $data[] = $column;   
    }
    $parsingJSON = array('data' => $data);

    echo json_encode($parsingJSON);
  }

  public function DataBorrower()
  {

      //  $updateStatusBorrower = DB::table('brw_users')
      //           ->where('brw_id', $request->brw_id)
      //           ->update(['status' => "pending"]);
    //$dataBorrower  = DB::table('brw_users')->join('brw_users_details', 'brw_users_details.brw.id', '=', 'brw_users.brw_id')->get();
    $dataBorrower = Borrower::select('brw_users.brw_id as borrower_id','brw_users.username', 'brw_users.email','brw_users.status','brw_users_details.nama')
    ->leftjoin('brw_users_details','brw_users_details.brw_id','=','brw_users.brw_id')
    ->get();

      // $dataGet = Borrower::all();
      // $i=1;
       $data= array();

      foreach($dataBorrower as $item)
      { 
          
          //$column = array();
          $column[] = (string) $item->borrower_id;
          $column[] = (string) $item->nama;
          $column[] = (string) $item->email;
          $column[] = (string) $item->username;
          $column[] = (string) $item->status;
         

          $data[] = $column;   
      }

    
      $parsingJSON = array("data" => $dataBorrower);

      echo json_encode($parsingJSON);

  }

  public function DetailsDataBorrower($borrower_id)
  {

  
    $dataBorrowerDetails =  Borrower::leftJoin('brw_users_details','brw_users.brw_id','=','brw_users_details.brw_id')
    ->where('brw_users.brw_id', $borrower_id)
    ->first();
    
    $dataBorrowerRekening = Borrower::leftjoin("brw_rekening", "brw_users.brw_id",'=','brw_rekening.brw_id')
    ->where('brw_users.brw_id', $borrower_id)
    ->first();
    

   
    
    if($dataBorrowerDetails['brw_type'] == 1 or $dataBorrowerDetails['brw_type'] == 3){
      $dataBorrowerAhliWaris = Borrower::leftjoin("brw_ahli_waris", "brw_users.brw_id",'=','brw_ahli_waris.brw_id')
      ->where('brw_users.brw_id', $borrower_id)
      ->first();
    
      $parsingJSON = array( "data" => $dataBorrowerDetails,"data_aw" => $dataBorrowerAhliWaris, "data_rekening" => $dataBorrowerRekening);
   
    }else{

      $dataBorrowerPengurus = Borrower::leftjoin("brw_pengurus", "brw_users.brw_id",'=','brw_pengurus.brw_id')
      ->where('brw_users.brw_id', $borrower_id)
      ->first();
      $parsingJSON = array( "data" => $dataBorrowerDetails,"data_pengurus" => $dataBorrowerPengurus, "data_rekening" => $dataBorrowerRekening);
    
    }
    
    echo json_encode($parsingJSON);

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

    public function DataJenisKelamin(){
        
      $getJenisKelamin = DB::table('m_jenis_kelamin')->select('id_jenis_kelamin', 'jenis_kelamin')->get();
        //$borrower = Borrower::where('username', $request->username)->first();
        
      echo "[";
        $i = 0;
        foreach($getJenisKelamin as $data){
            if($i >0){
                echo ",\r\n"; 
            }
            echo "{\"id\"  : \"$data->id_jenis_kelamin\", \"text\":\"$data->jenis_kelamin\"}"; 
            $i++;
        }
    
      echo "]";
        
    }

    public function DataAgama(){
        
      $getAgama = DB::table('m_agama')->select('id_agama', 'agama')->get();
        //$borrower = Borrower::where('username', $request->username)->first();
        
      echo "[";
        $i = 0;
        foreach($getAgama as $data){
            if($i >0){
                echo ",\r\n"; 
            }
            echo "{\"id\"  : \"$data->id_agama\", \"text\":\"$data->agama\"}"; 
            $i++;
        }
    
      echo "]";
        
    }

    public function DataNikah(){
        
      $getKawin = DB::table('m_kawin')->select('id_kawin', 'jenis_kawin')->get();
        //$borrower = Borrower::where('username', $request->username)->first();
        
      echo "[";
        $i = 0;
        foreach($getKawin as $data){
            if($i >0){
                echo ",\r\n"; 
            }
            echo "{\"id\"  : \"$data->id_kawin\", \"text\":\"$data->jenis_kawin\"}"; 
            $i++;
        }
    
      echo "]";
        
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
      ->where('kode_kota', '=',$id)->get();
       //var_dump($getKota);die;     
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

      public function GantiDataKota($id){
		 
	  
        $getKota = DB::table('m_provinsi_kota')->select('id_provinsi','kode_kota','kode_provinsi', 'nama_provinsi', 'nama_kota')
        ->where('kode_provinsi', '=',$id)->get();
         //var_dump($getKota);die;     
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

      public function DataBidangOnline(){
		 
	  
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

      public function DataPengalaman(){
		 
	  
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

        public function DataDokumenBorrower(){
     
    
        $getDokumen = DB::table('brw_persyaratan_pendanaan')->select('persyaratan_nama','nama_folder')->where('persyaratan_mandatory', '=',1)->groupby('persyaratan_nama')->get();
        
        $i=0; 
            echo "[";
            foreach($getDokumen as $data){
                if($i >0){
                    echo ",\r\n"; 
                }
                echo "{\"id\"  : \"$data->persyaratan_nama\", \"text\":\"$data->persyaratan_nama\"}"; 
                $i++;
            }
        
        echo "]";
       
      }



}