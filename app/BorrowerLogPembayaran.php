<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
// use Illuminate\Notifications\Notifiable;
// use Illuminate\Contracts\Auth\CanResetPassword;
// use Illuminate\Foundation\Auth\User as Authenticatable;

class BorrowerLogPembayaran extends Model
{
    // use Notifiable;
    //protected $primaryKey = 'brw_id';
    protected $table = 'brw_log_pembayaran';
    protected $guard = 'borrower';

    protected $fillable = [
        'invoice_id', 'brw_id', 'pendanaan_id','tipe_pembayaran','tipe_percepataan','pic_pembayaran','nilai_pelunasan', 'tgl_pembayaran','status'
    ];

}
