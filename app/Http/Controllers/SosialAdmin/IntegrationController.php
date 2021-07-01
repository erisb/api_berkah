<?php


namespace App\Http\Controllers\SosialAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use DB;
use Illuminate\Support\Facades\Storage;
use App\BniEnc;
use Carbon\Carbon;

class IntegrationController extends Controller
{

	public function __construct(){
		date_default_timezone_set('Asia/Jakarta');
	}

    public function generateVA_BNI_Sosial($id_user){
		
		$bni_id = env('BNIS_VA_CLIENT');
        $bni_key = env('BNIS_VA_KEY');
		$bni_url = env('BNIS_VA_API_URL');
		$bni_cid = env('BNIS_VA_CID');

		$date = \Carbon\Carbon::now()->addYear(4);
		
		$select_user = DB::connection('mysql2')->select("select nama_lengkap, email, no_hp from users where id = '$id_user'");
		$nomor_hp = $this->generate_number($select_user[0]->no_hp);
		$va_number = $bni_id.$bni_cid.$nomor_hp;

		/////////////////////////// BUAT PERSIAPAN INTEGRASI BNI JGN DI HAPUS//////////////////////////////////
		$data = [
			'type' => 'createbilling',
			'client_id' => $bni_cid,
			'trx_id' => $id_user,
			'trx_amount' => '0',
			'customer_name' => $select_user[0]->nama_lengkap,
			'customer_email' => $select_user[0]->email,
			'virtual_account' => $va_number,
			'datetime_expired' => $date->format('Y-m-d').'T'.$date->format('H:i:sP'),
			'billing_type' => 'o',
		];
		$encrypted = BniEnc::encrypt($data,$bni_cid,$bni_key);

		$client = new Client(); //GuzzleHttp\Client
		$request = $client->post($bni_url, [
			'json' => [
				'client_id' => $bni_cid,
				'data' => $encrypted,
			]
		]);

		$result = json_decode($request->getBody()->getContents());

		$call_log = DB::connection('mysql2')->select("call log_va_number( '$result->status', '$id_user')");

		if($result->status !== '000'){
			return $result->status;
		}
		else{
			$decrypted = BniEnc::decrypt($result->data,$bni_cid,$bni_key);
			
			$va_number=$decrypted['virtual_account'];

			$call_db = DB::connection('mysql2')->select("call update_va_number( '$id_user','$va_number')");
			if($call_db[0]->v_out == 'Sukses'){
				return $result->status;
			}else{
				return $result->status;
			}
		}
	}
	
	public function generate_number($no_hp){
		if(substr($no_hp,0,2) == "08"){
			$nomor_hp = substr($no_hp,2,8);
		}elseif(substr($no_hp,0,2)== "62"){
			$nomor_hp = substr($no_hp,3,8);
		}elseif(substr($no_hp,0,3)== "+62"){
			$nomor_hp = substr($no_hp,4,8);
		}else{
			$nomor_hp = substr($no_hp,0,8);
		}

		if(strlen($nomor_hp)<8){
			$nomor_hp = str_pad($nomor_hp,8,'0',STR_PAD_RIGHT);
			return $nomor_hp;
        }else{
			$nomor_hp = $nomor_hp;
			return $nomor_hp;
		}
	}

	public function bnis_response_transfer(Request $request){
		$bni_id  = env('BNIS_VA_CLIENT');
        $bni_key = env('BNIS_VA_KEY');
		$bni_url = env('BNIS_VA_API_URL');
		$bni_cid = env('BNIS_VA_CID');

		////////////////// JGN DIHAPUS BUAT CALLBACK BNI ENTAR ///////////////
        $data = $request->input('data');
        if($request->input('client_id') != $bni_cid){
            return response()->json([
                'status' => '999',
                'message' => 'Access Denied',
            ]);
        }
        
        $decrypted 		= BniEnc::decrypt($data,$bni_cid,$bni_key);
        $va_investor 	= empty($decrypted['virtual_account']) ? '' : $decrypted['virtual_account'];
		$payment_ntb 	= empty($decrypted['payment_ntb']) ? '' : $decrypted['payment_ntb'];
		$amount 		= empty($decrypted['payment_amount']) ? '' : $decrypted['payment_amount'];
		$customer_name 	= empty($decrypted['customer_name']) ? '' : $decrypted['customer_name'];
		// $status 		= empty($decrypted['status']) ? '' : $decrypted['status'];

		// $va_investor 	= $request->va;
		// $payment_ntb 	= $request->ntb;
		// $amount 		= $request->amount;
		// $customer_name 	= $request->name;
		// $status 		= $request->status;

		$check_ntb = DB::connection('mysql2')->select("select count(payment_ntb) as payment_ntb from log_transfer where payment_ntb = '$payment_ntb' and va_number = '$va_investor' and transfer_amount = '$amount'");

		if($check_ntb[0]->payment_ntb>0){
			return response()->json([
				'status' => '888',
				'message' => 'Payment NTB double',
			]);
		}else{
		
			$call_log_db = DB::connection('mysql2')->select("call log_transfer( '$va_investor','$customer_name', '$amount', '$payment_ntb','000')");

			$check_temp_payment = DB::connection('mysql2')->select("select COUNT(id_temp) as v_count_temp FROM temp_pendanaan_masuk WHERE va_number = '$va_investor' AND dana_masuk = '$amount'");

			if($check_temp_payment[0]->v_count_temp==0){
				return response()->json([
					'status' => '888',
					'message' => 'Temp Already Paid',
				]);
			}else{

				$call_db = DB::connection('mysql2')->select("call add_donasi( '$va_investor','$customer_name', '$amount')");

				if($call_db[0]->v_out=='Sukses'){
					if($call_db[0]->v_id_tipe_pendanaan==1){
						$this->success_transfer_sms($va_investor, $customer_name, $amount);
					}else{
						$register_basnaz = $this->register_basnaz($va_investor);
						if($register_basnaz['status_code']==000){
							$transfer_basnaz = $this->transfer_basnaz($va_investor, $register_basnaz['npwz'], $amount, $call_db[0]->v_id_tipe_pendanaan, $call_db[0]->v_id_pendanaan_sosial, $call_db[0]->v_no_invoice);
							if($transfer_basnaz['status_code']==000){
								return response()->json(['status'=>'Sukses', 'message'=>'Sukses']);	
							}else{
								return response()->json(['status'=>'Failed', 'message'=>'Gagal Transfer Basnaz']);
							}
						}else{
							return response()->json(['status'=>'Failed', 'message'=>'Gagal Register Basnaz']);
						}
					}
				}else{
					return response()->json(['status'=>'Failed', 'message'=>'Gagal Add Donasi']);
				}
			}
		}

		return response()->json([
            'status' => '000'
        ]);
	}


	public function updateBillingBNI(Request $request)
    {
		$bni_id  = env('BNIS_VA_CLIENT');
        $bni_key = env('BNIS_VA_KEY');
		$bni_url = env('BNIS_VA_API_URL');
		$bni_cid = env('BNIS_VA_CID');

        $trx_id = $request->input('trx_id');
        $amount = $request->input('amount');
        $name = $request->input('name');
        $email = $request->input('email');
        $phone = $request->input('phone');
        $expired = $request->input('expired');
        $description = $request->input('description');

        $data = [
            'client_id' => $bni_cid,
            'trx_id' => $trx_id,
            'trx_amount' => $amount,
            'customer_name' => $name,
            'customer_email' => $email, 
            'customer_phone' => $phone, 
            'datetime_expired' => $expired, 
            'description' => $description, 
            'type' => 'updateBilling'
        ];

        $encrypted = BniEnc::encrypt($data,$bni_cid,$bni_key);

        $client = new Client(); //GuzzleHttp\Client
        $result = $client->post($bni_url, [
            'json' => [
                'client_id' => $bni_cid,
                'prefix' => '988',
                'data' => $encrypted,
            ]
        ]);

        $result = json_decode($result->getBody()->getContents());

        if($result->status !== '000'){
            return $result->message;
        }
        else{
            $decrypted = BniEnc::decrypt($result->data,$bni_cid,$bni_key);
            return $decrypted;
         }
	}

	public function inquiry_bni(Request $request)
    {
		$bni_id  = env('BNIS_VA_CLIENT');
        $bni_key = env('BNIS_VA_KEY');
		$bni_url = env('BNIS_VA_API_URL');
		$bni_cid = env('BNIS_VA_CID');

        $trx_id = $request->input('trx_id');
        $data = [
            'type' => 'inquirybilling',
            'client_id' => $bni_cid,
            'trx_id' => $trx_id,
        ];

        $encrypted = BniEnc::encrypt($data,$bni_cid,$bni_key);

        $client = new Client(); //GuzzleHttp\Client
        $result = $client->post($bni_url, [
            'json' => [
                'client_id' => $bni_cid,
                'prefix' => '988',
                'data' => $encrypted,
            ]
        ]);

        $result = json_decode($result->getBody()->getContents());

        if($result->status !== '000'){
            return $result->message;
        }
        else{
            $decrypted = BniEnc::decrypt($result->data,$bni_cid,$bni_key);
            return $decrypted;
         }
    }
	

	public function success_transfer_sms($va_number, $customer_name, $amount){
		
		$select = DB::connection('mysql2')->select("select name, email, no_hp from users where va_number = '$va_number'");

		$date = date("d-m-Y H:i");
		$to = $select[0]->no_hp;
		$amount = number_format($amount,0,",",".");
		
        // $to = '085966528825';
		$text =  "Terima kasih donasi Bapak/Ibu $customer_name sebesar Rp. $amount,- telah diterima pada $date. Semoga kebaikan Anda di balas berlipat ganda oleh Allah SWT dan membawa keberkahan bagi kita semua.";
		

        $pecah              = explode(",",$to);
        $jumlah             = count($pecah);
        $from               = "DANASYARIAH"; //Sender ID or SMS Masking Name, if leave blank, it will use default from telco
        // $username           = "smsvirodemo";
        // $password           = "qwerty@123";
        // $from               = "DANASYARIAH";
        $username           = env('SMSVIRO_USERNAME'); //your smsviro username
        $password           = env('SMSVIRO_PASSWORD'); //your smsviro password
        $postUrl            = env('SMSVIRO_URL'); # DO NOT CHANGE THIS
        
        for($i=0; $i<$jumlah; $i++){
            if(substr($pecah[$i],0,2) == "62" || substr($pecah[$i],0,3) == "+62"){
                $pecah = $pecah;
            }elseif(substr($pecah[$i],0,1) == "0"){
                $pecah[$i][0] = "X";
                $pecah = str_replace("X", "62", $pecah);
            }else{
                echo "Invalid mobile number format";
            }
            $destination = array("to" => $pecah[$i]);
            $message     = array("from" => $from,
                                 "destinations" => $destination,
                                 "text" => $text,
                                 "smsCount" => 20);
            $postData           = array("messages" => array($message));
            $postDataJson       = json_encode($postData);
            $ch                 = curl_init();
            $header             = array("Content-Type:application/json", "Accept:application/json");
            
            curl_setopt($ch, CURLOPT_URL, $postUrl);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 2);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postDataJson);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			$responseBody = json_decode($response);
            curl_close($ch);
        }  
	}
	
	public function register_basnaz($va_number){

		$client = new Client();
		$data = DB::connection('mysql2')->select("select nama_lengkap, email, no_hp from users where va_number = '$va_number'");
        
        $multipart_form = [
                               [
								'name'=>'amil',
								'contents'=>env('BASNAZ_AMIL')
							   ],
							   [
								'name'=>'org',
								'contents' => env('BASNAZ_ORG')
							   ],
							   [
								'name'=>'key',
								'contents' => env('BASNAZ_KEY')
							   ],
							   [
								'name'=>'tipe',
								'contents' => 'perorangan'
							   ],
							   [
								'name'=>'action',
								'contents' => 'register'
							   ],
							   [
								'name'=>'nama',
								'contents'=>$data[0]->nama_lengkap
							   ],
							   [
								'name'=>'email',
								'contents' => $data[0]->email
							   ],
							   [
								'name'=>'tanggal',
								'contents'=>  date('d/m/Y')
							   ],
							   [
								'name'=>'handphone',
								'contents'=>$data[0]->no_hp
							   ],
							   [
								'name'=>'verifikasi',
								'contents'=>'handphone'
							   ]
         ];
         $boundary = '----WebKitFormBoundary7MA4YWxkTrZu0gW';
         $cek = $client->post('https://simba.baznas.go.id/api/ajax_muzaki_register/',[
					'headers' => [  'Content-Type' => 'multipart/form-data; boundary=----WebKitFormBoundary7MA4YWxkTrZu0gW',
									'Postman-Token' => '3e478429-0f74-7bb3-c63b-1133925420aa',
									'Cache-Control' => 'no-cache'
                                ],     
            
                    'body' => new \GuzzleHttp\Psr7\MultipartStream($multipart_form, $boundary),
                    'verify' => false
				]);
				
		$response_API =  $cek->getBody()->getContents();
		$response = json_decode($response_API);

		$call_db = DB::connection('mysql2')->select("call log_register_basnaz('$response->status_code', '$response->status', '$va_number')");
		
		if($response->status_code==000){
			$data = array("status_code"=>$response->status_code, "status"=>$response->status, "npwz"=>$response->npwz);
		}else{
			$data = array("status_code"=>$response->status_code, "status"=>$response->status);
		}
        return $data;
	}

	public function transfer_basnaz($va_number, $npwz, $jumlah, $tipe_pendanaan, $id_pendanaan_sosial, $no_invoice){
		$client = new Client();

		if($tipe_pendanaan==2){
			$akun = env('BASNAZ_TIPE_MAAL');
		}elseif ($tipe_pendanaan==3) {
			$akun = env('BASNAZ_TIPE_INFAK');
		}elseif ($tipe_pendanaan==4) {
			$akun = env('BASNAZ_TIPE_PROFESI');
		}elseif ($tipe_pendanaan==5) {
			$akun = env('BASNAZ_TIPE_FITRAH');
		}elseif ($tipe_pendanaan==6) {
			$akun = env('BASNAZ_TIPE_KURBAN');
		}else{
			$akun='';
		}

		if($tipe_pendanaan==2 || $tipe_pendanaan==4){
			$kadar=2.5;
		}else{
			$kadar=0;
		}
		
        $multipart_form = [
                               [
								'name'=>'amil',
								'contents'=>env('BASNAZ_AMIL')
							   ],
							   [
								'name'=>'org',
								'contents' => env('BASNAZ_ORG')
							   ],
							   [
								'name'=>'key',
								'contents' => env('BASNAZ_KEY')
							   ],
							   [
								'name'=>'divisi',
								'contents' => env('BASNAZ_DIVISI')
							   ],
							   [
								'name'=>'program',
								'contents' => env('BASNAZ_PROGRAM')
							   ],
							   [
								'name'=>'via',
								'contents'=>env('BASNAZ_VIA')
							   ],
							   [
								'name'=>'subjek',
								'contents' => $npwz
							   ],
							   [
								'name'=>'tanggal',
								'contents'=>  date('d/m/Y')
							   ],
							   [
								'name'=>'akun',
								'contents'=>$akun
							   ],
							   [
								'name'=>'jumlah',
								'contents'=>$jumlah
							   ],
							   [
								'name'=>'kadar',
								'contents'=>$kadar
							   ],
							   [
								'name'=>'keterangan',
								'contents'=>$no_invoice
							   ]
         ];
         $boundary = '----WebKitFormBoundary7MA4YWxkTrZu0gW';
         $cek = $client->post('https://simba.baznas.go.id/api/ajax_transaksi_simpan',[
					'headers' => [  'Content-Type' => 'multipart/form-data; boundary=----WebKitFormBoundary7MA4YWxkTrZu0gW',
									'Postman-Token' => '3e478429-0f74-7bb3-c63b-1133925420aa',
									'Cache-Control' => 'no-cache'
                                ],     
            
                    'body' => new \GuzzleHttp\Psr7\MultipartStream($multipart_form, $boundary),
                    'verify' => false
				]);
				
		$response_API =  $cek->getBody()->getContents();
		$response = json_decode($response_API,true);

		$status_code = $response['status_code'];
		$status = $response['status'];
		$no_transaksi = empty($response['no_transaksi']) ? null : $response['no_transaksi'];
		$tanggal = empty($response['tanggal']) ? null : $response['tanggal'];
		$waktu = empty($response['waktu']) ? null : $response['waktu'];
		$bukti_transfer = empty($response['bsz']) ? null : $response['bsz'];
		$tanggal_lengkap = $tanggal.' '.$waktu;
		
		$call_db = DB::connection('mysql2')->select("call log_transfer_basnaz('$status_code', '$status', '$no_transaksi', '$tanggal_lengkap', '$bukti_transfer', $id_pendanaan_sosial, '$va_number')");

        return $response;
	}

	public function kalkulator_basnaz(Request $request){
		$client = new Client();

		$tipe_zakat 		 = $request->tipe_zakat;
		$pendapatan_perbulan = $request->pendapatan_perbulan;
		$pendapatan_lain 	 = $request->pendapatan_lain;
		$logam_mulia 		 = $request->logam_mulia;
		$tabungan 			 = $request->tabungan;
		$aset 				 = $request->aset;
		$hutang 			 = $request->hutang;

		if($tipe_zakat=='penghasilan'){
			$multipart_form = [
				[
				 'name'=>'jenis',
				 'contents'=>'penghasilan'
				],
				[
				 'name'=>'pendapatan_perbulan',
				 'contents' => $pendapatan_perbulan
				],
				[
				 'name'=>'pendapatan_lain',
				 'contents' => $pendapatan_lain
				],
				[
				 'name'=>'logam_mulia',
				 'contents' => ''
				],
				[
				 'name'=>'tabungan',
				 'contents' => ''
				],
				[
				 'name'=>'aset',
				 'contents'=> ''
				],
				[
				 'name'=>'hutang',
				 'contents' => ''
				]
			];
		}else{
			$multipart_form = [
				[
				 'name'=>'jenis',
				 'contents'=>'maal'
				],
				[
				 'name'=>'pendapatan_perbulan',
				 'contents' => ''
				],
				[
				 'name'=>'pendapatan_lain',
				 'contents' => ''
				],
				[
				 'name'=>'logam_mulia',
				 'contents' => $logam_mulia
				],
				[
				 'name'=>'tabungan',
				 'contents' => $tabungan
				],
				[
				 'name'=>'aset',
				 'contents'=> $aset
				],
				[
				 'name'=>'hutang',
				 'contents' => $hutang
				]
			];
		} 

         $boundary = '----WebKitFormBoundary7MA4YWxkTrZu0gW';
         $cek = $client->post('https://baznas.go.id/api/ajax_kalkulator_zakat',[
					'headers' => [  'Content-Type' => 'multipart/form-data; boundary=----WebKitFormBoundary7MA4YWxkTrZu0gW',
									'Postman-Token' => '3e478429-0f74-7bb3-c63b-1133925420aa',
									'Cache-Control' => 'no-cache'
                                ],     
            
                    'body' => new \GuzzleHttp\Psr7\MultipartStream($multipart_form, $boundary),
                    'verify' => false
				]);
				
		$response_API =  $cek->getBody()->getContents();
		$response = json_decode($response_API,true);

		return response()->json($response);  
	}
  
}