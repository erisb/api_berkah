<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
// use Illuminate\Notifications\Notifiable;
// use Illuminate\Contracts\Auth\CanResetPassword;
// use Illuminate\Foundation\Auth\User as Authenticatable;

class BorrowerPendanaan extends Model
{
    // use Notifiable;
    protected $primaryKey = 'pendanaan_id';
    protected $table = 'brw_pendanaan';
    protected $guard = 'borrower';

    protected $fillable = [
        'id_proyek', 'brw_id', 
        'pendanaan_nama', 'pendanaan_tipe',
        'pendanaan_akad', 'pendanaan_dana_dibutuhkan', 
        'estimasi_mulai', 'mode_pembayaran', 
        'durasi_proyek', 'detail_pendanaan','dana_dicairkan', 'status', 'status_dana', 'va_number'
    ];

}
