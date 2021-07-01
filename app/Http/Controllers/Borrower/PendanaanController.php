<?php

// use DB;
namespace App\Http\Controllers;
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

class PendanaanController extends Controller
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

    public function showDashboard(){
        $semuaData = Brw_pendanaan::All();
        $status = Brw_pendanaan::where('status', 5)->get();
        $kirim = ['semuaData' => $semuaData, 'status' => $status];
        return response()->json($kirim); 
    }

    public function allPendanaanById($brw_id){
        return response()->json(Brw_pendanaan::where('brw_id',$brw_id)->whereIn('status',array(1,2,3,4,0,6))->get());
    }

    public function pendanaanBerjalanByid($brw_id){
        return response()->json(Brw_pendanaan::where('brw_id',$brw_id)->whereIn('status',array(1,2,3,6))->get());
    }

    public function pendanaanPengajuanByid($brw_id){
        return response()->json(Brw_pendanaan::where('brw_id',$brw_id)->where('status',0)->get());
    }

    public function pendanaanSelesaiByid($brw_id){
        return response()->json(Brw_pendanaan::where('brw_id',$brw_id)->where('status',4)->get());
    }

    public function listproyek($brw_id){
        $listProyek = Brw_pendanaan::where('brw_id',$brw_id)
        ->whereIn('brw_pendanaan.status', array(0,1,2,3,4,6))
        ->leftjoin('proyek','proyek.id','=','brw_pendanaan.id_proyek')
        ->get();
        // $listProyek = Proyek::wherein('brw_pendanaan.status', array(0,1,2,3,4,6))
        //                 ->where('brw_pendanaan.brw_id',$brw_id)
        //                 ->join('brw_pendanaan','brw_pendanaan.id_proyek','=','proyek.id')
        //                 ->get();
        return response()->json($listProyek);
    }

    public function plafon($brw_id){
        return response()->json(Brw_rekening::where('brw_id',$brw_id)->first());
    }

    public function pykid($pyk_id){
       $pykid = Proyek::where('proyek.id', $pyk_id)
                        ->join('brw_pendanaan','brw_pendanaan.id_proyek','=','proyek.id')
                        ->first([
                            'proyek.*',
                            'brw_pendanaan.pendanaan_id',
                            'brw_pendanaan.status as status_pendanaan',
                            'brw_pendanaan.status as status_dana',
                            'brw_pendanaan.pendanaan_dana_dibutuhkan',
                            'brw_pendanaan.mode_pembayaran',
                            'brw_pendanaan.metode_pembayaran',
                            'brw_pendanaan.dana_dicairkan',
                            'brw_pendanaan.pendanaan_akad'
                        ]);
                        
        return response()->json($pykid);
    }

    public function danaTerkumpul($pyk_id){
        $datadanaTerkumpul = BorrowerDanaTerkumpul::where('proyek_id', $pyk_id)->sum('nominal_awal');
        $danaTerkumpul = array("nominal_awal" => $datadanaTerkumpul);
        return response()->json($danaTerkumpul);
    }

    public function danatbProyekTerkumpul($brw_id){

        $pendanaan = DB::table('brw_pendanaan as a')
            ->selectRaw('a.brw_id, a.id_proyek, a.status as status_pendanaan, a.status_dana')
		    ->where('a.brw_id', '=',$brw_id)
            ->where('a.status', '=', 7)
            ->where('a.status_dana', '=', 1)
            ->get();

        $nilaiTerkumpul = 0;
        $nilaiPersen    = 0;
        
        // get plafon borrower
        $getplafon = Brw_rekening::where('brw_id', $brw_id)->first();
        $total_plafon   = $getplafon->total_plafon;
        $total_terpakai = $getplafon->total_terpakai;
        $total_sisa     = $getplafon->total_sisa;

        
        
            
            $count = count($pendanaan);
            
            $sumDanaInves   = "";
            
            for( $a = 0; $a<$count;$a++){

                $statusTTD = DB::table('log_akad_digisign_borrower')->where('status', 'complete')->where('id_proyek', $pendanaan[$a]->id_proyek)->first();
                

                $pendanaanAktif = DB::table('pendanaan_aktif')
                    ->whereIn('proyek_id',[$statusTTD->id_proyek])->sum('total_dana');

                $nilaiTerkumpul += $pendanaanAktif;

            }

            
            
            $dataPersen = ($nilaiTerkumpul === 0 ? 0 : $nilaiTerkumpul/$total_plafon)*100;
            
            
            
            $dataterkumpul = array("danaDiterima"=>$nilaiTerkumpul,"dataPersen"=>$dataPersen);
            
            return response()->json($dataterkumpul);

        
        
    }

    public function totalImbalHasil($brw_id){

        $pendanaan = DB::table('brw_pendanaan as a')
            ->selectRaw('a.brw_id, a.id_proyek, a.status as status_pendanaan, a.status_dana')
		    ->where('a.brw_id', '=',$brw_id)
            ->where('a.status', '=', 7)
            ->where('a.status_dana', '=', 1)
            ->get();
        
        $count = count($pendanaan);
        $nilaiTerkumpul     = 0;
        $nilaiPersen        = 0;
        $proftiDSI          = 5;
        $nilaiImbalHasil    = 0;

        // get plafon borrower
        $getplafon = Brw_rekening::where('brw_id', $brw_id)->first();
        $total_plafon   = $getplafon->total_plafon;
        $total_terpakai = $getplafon->total_terpakai;
        $total_sisa     = $getplafon->total_sisa;
        
        
        for( $i = 0; $i<$count;$i++){

            $statusTTD = DB::table('log_akad_digisign_borrower')->where('status', 'complete')->where('id_proyek', $pendanaan[$i]->id_proyek)->first();
            

            //$proyek = DB::table('proyek')->where('id', $pendanaan[$a]->id_proyek)->get();
            // $statusTTD = DB::table('log_akad_digisign_borrower')->where('id_proyek', $pendanaan[$a]->id_proyek)->first();
            
            $proyekPfrofit = DB::table('proyek')
            ->where('id', [$statusTTD->id_proyek])->get();
            
            
            $pendanaanAktif = DB::table('pendanaan_aktif')
                ->whereIn('proyek_id',[$pendanaan[$i]->id_proyek])->sum('total_dana');
            
            $nilaiTerkumpul += $pendanaanAktif;
            
            $nilaiImbalHasil = (($proyekPfrofit[0]->profit_margin+$proftiDSI) / 100) * $nilaiTerkumpul;

        }

        $dataPersen = ($nilaiImbalHasil === 0 ? 0 : $nilaiImbalHasil/$total_plafon)*100;
        
        
        $dataterkumpul = array("danaImbalHasil"=>$nilaiImbalHasil,"dataPersenImbalHasil"=>$dataPersen);
        
        return response()->json($dataterkumpul);
    }

    public function pykGambar($pyk_id){
        $gmbid = gambarProyek::where('proyek_id', $pyk_id)->get();
        return response()->json($gmbid);
    }

    public function pykDesk($pyk_id){
        $dskPyk = Proyek::where('proyek.id', $pyk_id)
                        ->join('deskripsi_proyeks', 'deskripsi_proyeks.id','=','proyek.id_deskripsi')
                        ->first();
                        $deskripsi = $dskPyk['deskripsi'];
        return response()->json($deskripsi);
    }

    public function updateSession($brw_id){
            $borrowerDetails = BorrowerDetails::where('brw_id', $brw_id)->first();
            $plafon = Brw_rekening::where('brw_id',$brw_id)->first();
            $pendanaan = Brw_pendanaan::select('brw_pendanaan.*', 'proyek.nama','brw_invoice.tgl_jatuh_tempo')->leftjoin('brw_invoice','brw_pendanaan.pendanaan_id', '=' ,'brw_invoice.pendanaan_id')->leftjoin('proyek','brw_pendanaan.id_proyek', '=' ,'proyek.id')->where('brw_pendanaan.brw_id',$brw_id)->whereIn('brw_pendanaan.status',array(1,2,3,6))->get();
            $pnd = array();
            if(empty($pendanaan)){$pnd['nama'] = "tidak ada Proyek";$pnd['tgl_jatuh_tempo'] = "tidak tersedia";}else{$pnd = $pendanaan;}
            $response = [
                'brw_nama' => $borrowerDetails === null ? null : $borrowerDetails->nama,
                'brw_type' => $borrowerDetails === null ? null : $borrowerDetails->brw_type,
                'brw_ptotal' => $plafon === null ? null : $plafon->total_plafon,
                'brw_ppake' => $plafon === null ? null : $plafon->total_terpakai,
                'brw_psisa' => $plafon === null ? null : $plafon->total_sisa,
                'data_pendanaan' => $pnd
            ];
            return response()->json($response);
    }

    public function getlastproyekapp ($brw_id){
        $pendanaan = Brw_pendanaan::where('brw_pendanaan.brw_id', $brw_id)->leftjoin('proyek','brw_pendanaan.id_proyek', '=' ,'proyek.id')->where('brw_pendanaan.status',1)->whereNotIn('brw_pendanaan.status',[0])->whereNotIn('brw_pendanaan.id_proyek',[0])->orderby('brw_pendanaan.updated_at', 'desc')->first();
        $status = array(0,5);
        if(empty($pendanaan)){
            $pendanaan = Brw_pendanaan::where('brw_pendanaan.brw_id', $brw_id)->leftjoin('proyek','brw_pendanaan.id_proyek', '=' ,'proyek.id')->whereNotIn('brw_pendanaan.status',[0,5])->whereNotIn('brw_pendanaan.id_proyek',[0])->orderby('brw_pendanaan.updated_at', 'desc')->first();
        }

        // var_dump($pendanaan);die();
        $pnd = array();
        if(empty($pendanaan)){$pnd = NULL;}else{$pnd = $pendanaan;}
        return response()->json($pnd);
    }
}
