<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
// use Illuminate\Notifications\Notifiable;
// use Illuminate\Contracts\Auth\CanResetPassword;
// use Illuminate\Foundation\Auth\User as Authenticatable;

class BorrowerBuktiPembayran extends Model
{
    // use Notifiable;
    protected $table = 'brw_bukti_pembayaran';
    protected $guard = 'borrower';

    protected $fillable = [
        'invoice_id', 'brw_id', 'pendanaan_id', 'pic_pembayaran','tgl_pembayaran', 'status', 'keterangan'
    ];

}
