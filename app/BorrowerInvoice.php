<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
// use Illuminate\Notifications\Notifiable;
// use Illuminate\Contracts\Auth\CanResetPassword;
// use Illuminate\Foundation\Auth\User as Authenticatable;

class BorrowerInvoice extends Model
{
    // use Notifiable;
    protected $table = 'brw_invoice';
    protected $guard = 'borrower';

    protected $fillable = [
        'invoice_id', 'brw_id', 'proyek_id', 'pendanaan_nama','dana_pokok',
        'imbal_hasil','total_bayar', 'tgl_jatuh_tempo', 'tgl_bayar', 'status_pembayaran' 
    ];

}
