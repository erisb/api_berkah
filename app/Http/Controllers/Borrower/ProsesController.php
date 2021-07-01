<?php

namespace App\Http\Controllers\Borrower;

use App\Http\Controllers\Controller;
use App\Borrower;

use App\BorrowerDetails;
use App\BorrowerPendanaan;
use App\Borrowerjaminan;
use App\BorrowerPengurus;
use App\BorrowerPersyaratanInsert;
use App\BorrowerPersyaratanPendanaan;
use App\LogSP3Borrower;
use App\BorrowerInvoice;
use App\BorrowerPembayaran;
use App\BorrowerLogPembayaran;
use Illuminate\Http\Request;
use DB;

class ProsesController extends Controller
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

    public function proses_lengkapi_profile(Request $request){
        
        $type = $request->type_borrower;
        $explodeChecked = explode(',', $request->persyaratan_arr);
        $countPersyaratanChecked =  count($explodeChecked);
        $arrayChecked = array($request->persyaratan_arr);

        $explodeUnchecked = explode('|', $request->persyaratan_non_arr);
        $countPersyaratanUnchecked =  count($explodeUnchecked);
        $arrayUnchecked = array($request->persyaratan_non_arr);
        //echo $request->txt_detail_pendanaan;die;
        
        switch ($type) {
            case 1 : // perorangan pegawai
                
            // insert details borrower
            $Borrower = new \App\BorrowerDetails();  
            $Borrower->brw_id = $request->brw_id;
            $Borrower->nama = $request->txt_nm_pengguna_pribadi;
            $Borrower->brw_type = 1;
            $Borrower->nm_ibu = $request->txt_nm_ibu_pribadi;
            $Borrower->ktp = $request->txt_no_ktp_pribadi;
            $Borrower->npwp = $request->txt_npwp_pribadi;
            $Borrower->tgl_lahir = $request->txt_tgl_lahir;
            $Borrower->no_tlp = $request->txt_notlp_pribadi; 
            $Borrower->jns_kelamin = $request->txt_jns_kelamin;
            $Borrower->status_kawin = $request->txt_sts_nikah_pribadi;
            $Borrower->alamat = $request->txt_alamat_pribadi;

            $Borrower->domisili_alamat = $request->txt_alamat_domisili_pribadi;
            $Borrower->domisili_provinsi = $request->txt_provinsi_domisili_pribadi;
            $Borrower->domisili_kota = $request->txt_kota_domisili_pribadi;
            $Borrower->domisili_kecamatan = $request->txt_kecamatan_domisili_pribadi;
            $Borrower->domisili_kelurahan = $request->txt_kelurahan_domisili_pribadi;
            $Borrower->domisili_kd_pos = $request->txt_kd_pos_domisili_pribadi;

            $Borrower->provinsi = $request->txt_provinsi_pribadi;
            $Borrower->kota = $request->txt_kota_pribadi;
            $Borrower->kode_pos = $request->txt_kd_pos_pribadi;
            $Borrower->kecamatan = $request->txt_kecamatan_pribadi;
            $Borrower->kelurahan = $request->txt_kelurahan_pribadi;

            $Borrower->agama = $request->txt_agama;
            $Borrower->tempat_lahir = $request->txt_tmpt_lahir_pribadi; 
            $Borrower->pendidikan_terakhir = $request->txt_pendidikanT_pribadi; 
            $Borrower->pekerjaan = $request->txt_pekerjaan_pribadi;
            $Borrower->bidang_pekerjaan = $request->txt_bd_pekerjaan_pribadi;
            $Borrower->pengalaman_pekerjaan = $request->txt_pengalaman_kerja_pribadi; 
            $Borrower->pendapatan = $request->txt_pendapatan_bulanan_pribadi; 
            $Borrower->brw_pic = $request->url_pic_brw;
            $Borrower->brw_pic_ktp = $request->url_pic_brw_ktp;
            $Borrower->brw_pic_user_ktp = $request->url_pic_brw_dengan_ktp;
            $Borrower->brw_pic_npwp = $request->url_pic_brw_npwp;
            $Borrower->save();    

            // // insert data Ahli Waris
            $ahliWaris = new \App\BorrowerAhliWaris();  
            $ahliWaris->brw_id = $request->brw_id;
            $ahliWaris->nama_ahli_waris = $request->txt_nm_aw_pribadi; 
            $ahliWaris->nik = $request->txt_nik_aw_pribadi; 
            $ahliWaris->no_tlp = $request->txt_notlp_aw_pribadi; 
            $ahliWaris->email = $request->txt_email_aw_pribadi;
            $ahliWaris->provinsi = $request->txt_provinsi_aw_pribadi;
            $ahliWaris->kota = $request->txt_kota_aw_pribadi;
            $ahliWaris->kecamatan = $request->txt_kecamatan_aw_pribadi;
            $ahliWaris->kelurahan = $request->txt_kelurahan_aw_pribadi;
            $ahliWaris->kd_pos = $request->txt_kode_pos_aw_pribadi;
            $ahliWaris->alamat = $request->txt_alamat_aw_pribadi;  
            $ahliWaris->save();  

            // // insert data REkening
            $ahliWaris = new \App\BorrowerRekening();  
            $ahliWaris->brw_id = $request->brw_id;
            $ahliWaris->brw_norek = $request->txt_no_rekening; 
            $ahliWaris->brw_nm_pemilik = $request->txt_nm_pemilik; 
            $ahliWaris->brw_kd_bank = $request->txt_bank; 
            $ahliWaris->total_plafon = 2000000000; 
            $ahliWaris->total_terpakai = 0; 
            $ahliWaris->total_sisa = 0; 
            $ahliWaris->save();
            
            // insert checked persyaratan
            for($i= 0; $i<$countPersyaratanChecked; $i++){
                $PendanaanChecked = new \App\BorrowerPersyaratanInsert();  
                $PendanaanChecked->brw_id = $request->brw_id;
                $PendanaanChecked->tipe_id = $request->type_pendanaan_select;
                $PendanaanChecked->user_type = 1; 
                $PendanaanChecked->persyaratan_id = $explodeChecked[$i];
                $PendanaanChecked->checked = 1;
                $PendanaanChecked->save();  
            }
            
            // insert unchecked persyaratan
            for($i= 0; $i<$countPersyaratanUnchecked; $i++){
                $PendanaanUnchecked = new \App\BorrowerPersyaratanInsert();  
                $PendanaanUnchecked->brw_id = $request->brw_id;
                $PendanaanUnchecked->tipe_id = $request->type_pendanaan_select;
                $PendanaanUnchecked->user_type = 1; 
                $PendanaanUnchecked->persyaratan_id = $explodeUnchecked[$i];
                $PendanaanUnchecked->checked = 0;
                $PendanaanUnchecked->save();  
               
            }
            // insert data pendanaan
            $Pendanaan = new \App\BorrowerPendanaan();  
            $Pendanaan->id_proyek = '' ;
            $Pendanaan->brw_id = $request->brw_id; 
            $Pendanaan->pendanaan_nama = $request->txt_nm_pendanaan;
            $Pendanaan->pendanaan_tipe = $request->type_pendanaan_select;
            $Pendanaan->pendanaan_akad = $request->txt_jenis_akad_pendanaan;
            $Pendanaan->pendanaan_dana_dibutuhkan = $request->txt_dana_pendanaan;
            $Pendanaan->estimasi_mulai = $request->txt_estimasi_proyek;
            $Pendanaan->mode_pembayaran = $request->txt_pembayaran_pendanaan;
            $Pendanaan->metode_pembayaran = $request->txt_metode_pembayaran_pendanaan;
            $Pendanaan->durasi_proyek = $request->txt_durasi_pendanaan;
            $Pendanaan->detail_pendanaan = $request->txt_detail_pendanaan;
            $Pendanaan->status = 0;
            $Pendanaan->save(); 
            
            break;
            
            case 2 : // badan hukum
                // insert details borrower
                $Borrower = new \App\BorrowerDetails();  
                $Borrower->brw_id = $request->brw_id;
                $Borrower->nama = $request->txt_nm_anda_bdn_hukum;
                $Borrower->nm_bdn_hukum = $request->txt_nm_bdn_hukum; 
                $Borrower->jabatan = $request->txt_jabatan_anda_bdn_hukum; 
                $Borrower->brw_type = 2;
                $Borrower->ktp = $request->txt_nik_anda_bdn_hukum;
                $Borrower->npwp = $request->txt_npwp_bdn_hukum;
                $Borrower->no_tlp = $request->txt_notlp_anda_bdn_hukum; 
                $Borrower->alamat = $request->txt_alamat_bdn_hukum;
                $Borrower->provinsi = $request->txt_provinsi_bdn_hukum;
                $Borrower->kota = $request->txt_kota_bdn_hukum;
                $Borrower->kecamatan = $request->txt_kecamatan_bdn_hukum;
                $Borrower->kelurahan = $request->txt_kelurahan_bdn_hukum;
                $Borrower->kode_pos = $request->txt_kd_pos_bdn_hukum;
                $Borrower->bidang_perusahaan = $request->txt_bd_pekerjaan_bdn_hukum;
                $Borrower->bidang_online = $request->txt_bpo_bdn_hukum;
                $Borrower->pendapatan = $request->txt_revenueB_bdn_hukum; 
                $Borrower->total_aset = $request->txt_asset_bdn_hukum; 
                $Borrower->brw_pic = $request->url_pic_brw_bdn_hukum;
                $Borrower->brw_pic_ktp = $request->url_pic_brw_ktp_bdn_hukum;
                $Borrower->brw_pic_user_ktp = $request->url_pic_brw_dengan_ktp_bdn_hukum;
                $Borrower->brw_pic_npwp = $request->url_pic_brw_npwp_bdn_hukum;
                $Borrower->save();

                 // insert data pengurus
                 $ahliWaris = new \App\BorrowerPengurus();  
                 $ahliWaris->brw_id = $request->brw_id;
                 $ahliWaris->nm_pengurus = $request->txt_nm_pengurus_bdn_hukum; 
                 $ahliWaris->nik_pengurus = $request->txt_nik_pengurus_bdn_hukum; 
                 $ahliWaris->no_tlp = $request->txt_notlp_pengurus_bdn_hukum; 
                 $ahliWaris->jabatan = $request->txt_jabatan_pengurus_bdn_hukum;
                 $ahliWaris->save();
                
                // insert data Rekening
                $ahliWaris = new \App\BorrowerRekening();  
                $ahliWaris->brw_id = $request->brw_id;
                $ahliWaris->brw_norek = $request->txt_no_rekening_bdn_hukum; 
                $ahliWaris->brw_nm_pemilik = $request->txt_nm_pemilik_rekening_bdn_hukum; 
                $ahliWaris->brw_kd_bank = $request->txt_bank_bdn_hukum; 
                $ahliWaris->total_plafon = 2000000000; 
                $ahliWaris->total_terpakai = 0; 
                $ahliWaris->total_sisa = 0; 
                $ahliWaris->save();

                // insert checked persyaratan
                for($i= 0; $i<$countPersyaratanChecked; $i++){
                    $Pendanaan = new \App\BorrowerPersyaratanInsert();  
                    $Pendanaan->brw_id = $request->brw_id;
                    $Pendanaan->tipe_id = $request->type_pendanaan_select_bdn_hukum;
                    $Pendanaan->user_type = 2; 
                    $Pendanaan->persyaratan_id = $explodeChecked[$i];
                    $Pendanaan->checked = 1;
                    $Pendanaan->save();  
                }
                
                // // insert unchecked persyaratan
                for($i= 0; $i<$countPersyaratanUnchecked; $i++){
                    $PendanaanUnchecked = new \App\BorrowerPersyaratanInsert();  
                    $PendanaanUnchecked->brw_id = $request->brw_id;
                    $PendanaanUnchecked->tipe_id = $request->type_pendanaan_select_bdn_hukum;
                    $PendanaanUnchecked->user_type = 2; 
                    $PendanaanUnchecked->persyaratan_id = $explodeUnchecked[$i];
                    $PendanaanUnchecked->checked = 0;
                    $PendanaanUnchecked->save();  
                
                }
                // insert data pendanaan
                $Pendanaan = new \App\BorrowerPendanaan();  
                $Pendanaan->id_proyek = '' ;
                $Pendanaan->brw_id = $request->brw_id; 
                $Pendanaan->pendanaan_nama = $request->txt_nm_pendanaan;
                $Pendanaan->pendanaan_tipe = $request->type_pendanaan_select_bdn_hukum;
                $Pendanaan->pendanaan_akad = $request->txt_jenis_akad_pendanaan;
                $Pendanaan->pendanaan_dana_dibutuhkan = $request->txt_dana_pendanaan;
                $Pendanaan->estimasi_mulai = $request->txt_estimasi_proyek;
                $Pendanaan->mode_pembayaran = $request->txt_pembayaran_pendanaan;
                $Pendanaan->metode_pembayaran = $request->txt_metode_pembayaran_pendanaan;
                $Pendanaan->durasi_proyek = $request->txt_durasi_pendanaan;
                $Pendanaan->detail_pendanaan = $request->txt_detail_pendanaan;
                $Pendanaan->status = 0;
                $Pendanaan->save(); 
            break;

            case 3 : // perorangan wirausaha
               
            // insert details borrower
            $Borrower = new \App\BorrowerDetails();  
            $Borrower->brw_id = $request->brw_id;
            $Borrower->nama = $request->txt_nm_pengguna_pribadi;
            $Borrower->brw_type = 1;
            $Borrower->nm_ibu = $request->txt_nm_ibu_pribadi;
            $Borrower->ktp = $request->txt_no_ktp_pribadi;
            $Borrower->npwp = $request->txt_npwp_pribadi;
            $Borrower->tgl_lahir = $request->txt_tgl_lahir;
            $Borrower->no_tlp = $request->txt_notlp_pribadi; 
            $Borrower->jns_kelamin = $request->txt_jns_kelamin;
            $Borrower->status_kawin = $request->txt_sts_nikah_pribadi;
            $Borrower->alamat = $request->txt_alamat_pribadi;

            $Borrower->domisili_alamat = $request->txt_alamat_domisili_pribadi;
            $Borrower->domisili_provinsi = $request->txt_provinsi_domisili_pribadi;
            $Borrower->domisili_kota = $request->txt_kota_domisili_pribadi;
            $Borrower->domisili_kecamatan = $request->txt_kecamatan_domisili_pribadi;
            $Borrower->domisili_kelurahan = $request->txt_kelurahan_domisili_pribadi;
            $Borrower->domisili_kd_pos = $request->txt_kd_pos_domisili_pribadi;

            $Borrower->provinsi = $request->txt_provinsi_pribadi;
            $Borrower->kota = $request->txt_kota_pribadi;
            $Borrower->kode_pos = $request->txt_kd_pos_pribadi;
            $Borrower->kecamatan = $request->txt_kecamatan_pribadi;
            $Borrower->kelurahan = $request->txt_kelurahan_pribadi;
            
            $Borrower->agama = $request->txt_agama;
            $Borrower->tempat_lahir = $request->txt_tmpt_lahir_pribadi; 
            $Borrower->pendidikan_terakhir = $request->txt_pendidikanT_pribadi; 
            $Borrower->pekerjaan = $request->txt_pekerjaan_pribadi;
            $Borrower->bidang_pekerjaan = $request->txt_bd_pekerjaan_pribadi;
            $Borrower->pengalaman_pekerjaan = $request->txt_pengalaman_kerja_pribadi; 
            $Borrower->pendapatan = $request->txt_pendapatan_bulanan_pribadi; 
            $Borrower->brw_pic = $request->url_pic_brw;
            $Borrower->brw_pic_ktp = $request->url_pic_brw_ktp;
            $Borrower->brw_pic_user_ktp = $request->url_pic_brw_dengan_ktp;
            $Borrower->brw_pic_npwp = $request->url_pic_brw_npwp;
            $Borrower->save();    

            // // insert data Ahli Waris
            $ahliWaris = new \App\BorrowerAhliWaris();  
            $ahliWaris->brw_id = $request->brw_id;
            $ahliWaris->nama_ahli_waris = $request->txt_nm_aw_pribadi; 
            $ahliWaris->nik = $request->txt_nik_aw_pribadi; 
            $ahliWaris->no_tlp = $request->txt_notlp_aw_pribadi; 
            $ahliWaris->email = $request->txt_email_aw_pribadi;
            $ahliWaris->provinsi = $request->txt_provinsi_aw_pribadi;
            $ahliWaris->kota = $request->txt_kota_aw_pribadi;
            $ahliWaris->kecamatan = $request->txt_kecamatan_aw_pribadi;
            $ahliWaris->kelurahan = $request->txt_kelurahan_aw_pribadi;
            $ahliWaris->kd_pos = $request->txt_kode_pos_aw_pribadi;
            $ahliWaris->alamat = $request->txt_alamat_aw_pribadi;  
            $ahliWaris->save();  

            // // insert data REkening
            $ahliWaris = new \App\BorrowerRekening();  
            $ahliWaris->brw_id = $request->brw_id;
            $ahliWaris->brw_norek = $request->txt_no_rekening; 
            $ahliWaris->brw_nm_pemilik = $request->txt_nm_pemilik; 
            $ahliWaris->brw_kd_bank = $request->txt_bank; 
            $ahliWaris->total_plafon = 2000000000; 
            $ahliWaris->total_terpakai = 0; 
            $ahliWaris->total_sisa = 0; 
            $ahliWaris->save();
            
            // insert checked persyaratan
            for($i= 0; $i<$countPersyaratanChecked; $i++){
                $PendanaanChecked = new \App\BorrowerPersyaratanInsert();  
                $PendanaanChecked->brw_id = $request->brw_id;
                $PendanaanChecked->tipe_id = $request->type_pendanaan_select;
                $PendanaanChecked->user_type = 3; 
                $PendanaanChecked->persyaratan_id = $explodeChecked[$i];
                $PendanaanChecked->checked = 1;
                $PendanaanChecked->save();  
            }
            
            // insert unchecked persyaratan
            for($i= 0; $i<$countPersyaratanUnchecked; $i++){
                $PendanaanUnchecked = new \App\BorrowerPersyaratanInsert();  
                $PendanaanUnchecked->brw_id = $request->brw_id;
                $PendanaanUnchecked->tipe_id = $request->type_pendanaan_select;
                $PendanaanUnchecked->user_type = 3; 
                $PendanaanUnchecked->persyaratan_id = $explodeUnchecked[$i];
                $PendanaanUnchecked->checked = 0;
                $PendanaanUnchecked->save();  
               
            }
            
            // insert data pendanaan
            $Pendanaan = new \App\BorrowerPendanaan();  
            $Pendanaan->id_proyek = '' ;
            $Pendanaan->brw_id = $request->brw_id; 
            $Pendanaan->pendanaan_nama = $request->txt_nm_pendanaan;
            $Pendanaan->pendanaan_tipe = $request->type_pendanaan_select;
            $Pendanaan->pendanaan_akad = $request->txt_jenis_akad_pendanaan;
            $Pendanaan->pendanaan_dana_dibutuhkan = $request->txt_dana_pendanaan;
            $Pendanaan->estimasi_mulai = $request->txt_estimasi_proyek;
            $Pendanaan->mode_pembayaran = $request->txt_pembayaran_pendanaan;
            $Pendanaan->metode_pembayaran = $request->txt_metode_pembayaran_pendanaan;
            $Pendanaan->durasi_proyek = $request->txt_durasi_pendanaan;
            $Pendanaan->detail_pendanaan = $request->txt_detail_pendanaan;
            $Pendanaan->status = 0;
            $Pendanaan->save(); 

            break;

        }

        // insert jaminan
        $exjaminan = explode('^~', $request->jaminan);
        // dd(count($exjaminan));
        for($ex=0;$ex<count($exjaminan);$ex++){
            $datajaminan = explode('@,@',$exjaminan[$ex]);
                $brwjaminan = new \App\Borrowerjaminan();
                $brwjaminan->pendanaan_id = $Pendanaan->pendanaan_id;
                $brwjaminan->jaminan_nama = $datajaminan[0];
                $brwjaminan->jaminan_nomor = $datajaminan[1];
                $brwjaminan->jaminan_jenis = $datajaminan[2];
                $brwjaminan->jaminan_nilai = $datajaminan[3];
                $brwjaminan->jaminan_detail = $datajaminan[4];
                $brwjaminan->status = 0;
                $brwjaminan->save(); 
        }
        
        // insert log pendanaan
        $LogPendanaan = new \App\BorrowerLogPendanaan();  
        $LogPendanaan->pendanaan_id = $Pendanaan->pendanaan_id ;
        $LogPendanaan->brw_id = $request->brw_id;
        $LogPendanaan->status = '0';
        $LogPendanaan->keterangan = 'Pendanaan Anda Akan kami Proses'; 
        $LogPendanaan->save(); 

        // update status borrower
        $updateStatusBorrower = DB::table('brw_users')
                ->where('brw_id', $request->brw_id)
                ->update(['status' => "pending"]);

        $response = [
                        'status' => 'sukses',
                        'message' => 'borrower berhasil ditambahkan',
                        'data_borrower' => 'null'
            
                    ];
        
        return response()->json($response);
        
    }

    public function proses_pendanaan(Request $request){
        
        $type = $request->type_borrower;
        $brw_id = $request->brw_id;
        // dd($request->nilai_jaminan_arr);
        
        $explode = explode(',', $request->persyaratan_arr);
        $countPersyaratan =  count($explode);
        $array = array($request->persyaratan_arr);

        // dd($request->nilai_jaminan_arr);
        
        // insert data pendanaan
        $Pendanaan = new \App\BorrowerPendanaan();  
        $Pendanaan->id_proyek = '' ;
        $Pendanaan->brw_id = $brw_id; 
        $Pendanaan->pendanaan_nama = $request->txt_nm_pendanaan;
        if($type == 2){
            $Pendanaan->pendanaan_tipe = $request->type_pendanaan_select_bdn_hukum;
        }else{
            $Pendanaan->pendanaan_tipe = $request->type_pendanaan_select;
        }
        
        
        $Pendanaan->pendanaan_akad = $request->txt_jenis_akad_pendanaan;
        $Pendanaan->pendanaan_dana_dibutuhkan = $request->txt_dana_pendanaan;
        $Pendanaan->estimasi_mulai = $request->txt_estimasi_proyek;
        $Pendanaan->mode_pembayaran = $request->txt_pembayaran_pendanaan;
        $Pendanaan->metode_pembayaran = $request->txt_metode_pembayaran_pendanaan;
        $Pendanaan->durasi_proyek = $request->txt_durasi_pendanaan;
        $Pendanaan->detail_pendanaan = $request->txt_detail_pendanaan;
        $Pendanaan->status = 0;
        $Pendanaan->save();  
        
        
        
        // cek persyaratan insert
        $cekpersyaratan = BorrowerPersyaratanInsert::where('brw_id',$brw_id)->where('persyaratan_id',$explode[0])->get();
        if(count($cekpersyaratan) == 0){
                    $tipe_id = $request->type_pendanaan_select;
                    $user_type = $type;
                $getPersyaratan = BorrowerPersyaratanPendanaan::select('persyaratan_id','persyaratan_mandatory')->where('tipe_id',$tipe_id)->where('user_type',$user_type)->get();
                // echo count($getPersyaratan);
                // dd($getPersyaratan); 
                for($i=0;$i<count($getPersyaratan);$i++){
                    $masukanPersyaratan = new BorrowerPersyaratanInsert(); 
                    $masukanPersyaratan->brw_id = $brw_id;
                    $masukanPersyaratan->tipe_id = $tipe_id;
                    $masukanPersyaratan->user_type = $user_type;
                    $masukanPersyaratan->persyaratan_id = $getPersyaratan[$i]['persyaratan_id'];
                    $masukanPersyaratan->checked = $getPersyaratan[$i]['persyaratan_mandatory'];
                    $masukanPersyaratan->save();  
                }
               
        }
       // insert persyaratan
       for($i= 0; $i<$countPersyaratan; $i++){
            // $Pendanaan = new \App\BorrowerPersyaratanInsert();  
           if($type == 1 or $type == 3){
                $tipe_id = $request->type_pendanaan_select;
                $user_type = $type;
            }else{
                $tipe_id = $request->type_pendanaan_select_bdn_hukum;
                $user_type = 2;
            } 
            $persyaratan_id = $explode[$i];
            $Pengajuan = BorrowerPersyaratanInsert::where('brw_id',$brw_id)
            ->where('tipe_id',$tipe_id)
            ->where('user_type',$user_type)
            ->where('persyaratan_id',$persyaratan_id)
            ->update(['checked' => 1]);  
        }

        // insert jaminan
        $exjaminan = explode('^~', $request->jaminan);
        // dd(count($exjaminan));
        for($ex=0;$ex<count($exjaminan);$ex++){
            $datajaminan = explode('@,@',$exjaminan[$ex]);
                $brwjaminan = new \App\Borrowerjaminan();
                $brwjaminan->pendanaan_id = $Pendanaan->pendanaan_id;
                $brwjaminan->jaminan_nama = $datajaminan[0];
                $brwjaminan->jaminan_nomor = $datajaminan[1];
                $brwjaminan->jaminan_jenis = $datajaminan[2];
                $brwjaminan->jaminan_nilai = $datajaminan[3];
                $brwjaminan->jaminan_detail = $datajaminan[4];
                $brwjaminan->status = 0;
                $brwjaminan->save(); 
        }

        // insert log pendanaan
        $LogPendanaan = new \App\BorrowerLogPendanaan();  
        $LogPendanaan->pendanaan_id = $Pendanaan->pendanaan_id ;
        $LogPendanaan->brw_id = $request->brw_id;
        $LogPendanaan->status = '0';
        $LogPendanaan->keterangan = 'Pendanaan Anda Akan kami Proses'; 
        $LogPendanaan->save(); 

        $response = [
                        'status' => 'sukses',
                        'message' => 'borrower berhasil ditambahkan',
                        'data_borrower' => 'null'
            
                    ];
        
        return response()->json($response);
        
    }

    public function prosesOTP(Request $req)
    {
        $otp = rand(100000, 999999);
        $text =  'Kode OTP : '.$otp.' Silahkan masukan kode ini untuk melanjutkan proses melengkapi data anda.';

        //send to db
        $Borrower = Borrower::where('brw_id', $req->brw_id)->update(['otp' => $otp]);

        $pecah              = explode(",",$req->hp);
        $jumlah             = count($pecah);
        // $from               = "SMSVIRO"; //Sender ID or SMS Masking Name, if leave blank, it will use default from telco
        // $username           = "smsvirodemo";
        // $password           = "qwerty@123";
        $from               = "DANASYARIAH";
        $username           = "danasyariahpremium"; //your smsviro username
        $password           = "Dsi701@2019"; //your smsviro password
        $postUrl            = "http://107.20.199.106/restapi/sms/1/text/advanced"; # DO NOT CHANGE THIS
        
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

        if($Borrower){
            $data = ['status' => true, 'message' => 'Silahkan masukan kode ini untuk melanjutkan proses melengkapi data anda.'];
            return response()->json($data);
        }else{
          $data = ['status' => false, 'message' => 'Data Telepon tidak benar.'];
          return response()->json($data);
        }
    }

    public function cekOTP(Request $req)
    {
        $dataOTP = Borrower::where('brw_id',$req->brw_id)->first();
        $otpDB = $dataOTP->otp;
        if ($req->otp == $otpDB)
        {
            $data = ['status' => '00', 'message' => 'OTP match'];
            return response()->json($data);
        }
        else
        {
            $data = ['status' => '01', 'message' => 'OTP not match'];
            return response()->json($data);
        }
    }

    public function updateSP3(Request $req)
    {
        $getSP3 = LogSP3Borrower::where('brw_id',$req->brw_id)
                                ->where('id_proyek',$req->proyek_id)
                                ->first();
        $getSP3->status = 2;
        $getSP3->save();
        if ($getSP3)
        {
            $data = ['status' => '00', 'message' => 'Sukses'];
            return response()->json($data);
        }
        else
        {
            $data = ['status' => '01', 'message' => 'Gagal'];
            return response()->json($data);
        }
    }

    
}
