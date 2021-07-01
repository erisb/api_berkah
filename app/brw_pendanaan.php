<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Brw_pendanaan extends Model
{
    protected $table = 'brw_pendanaan';
    protected $fillable = [
        'brw_id', 'pendanaan_nama','pendanaan_tipe', 'pendanaan_akad', 'pendanaan_dana_dibutuhkan', 
        'estimasi_mulai', 'mode_pembayaran', 'durasi_proyek', 'detail_pendanaan','dana_dicairkan', 'status', 'status_dana', 'id_proyek'
    ];

}
