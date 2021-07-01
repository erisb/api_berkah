<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
// use Illuminate\Notifications\Notifiable;
// use Illuminate\Contracts\Auth\CanResetPassword;
// use Illuminate\Foundation\Auth\User as Authenticatable;

class BorrowerLogBuktiPembayaran extends Model
{
    // use Notifiable;
    //protected $primaryKey = 'brw_id';
    protected $table = 'brw_log_bukti_pembayaran';
    protected $guard = 'borrower';

    protected $fillable = [
        'pendanaan_id', 'brw_id', 'invoice_id'
    ];

}
