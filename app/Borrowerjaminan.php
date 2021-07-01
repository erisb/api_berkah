<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
// use Illuminate\Notifications\Notifiable;
// use Illuminate\Contracts\Auth\CanResetPassword;
// use Illuminate\Foundation\Auth\User as Authenticatable;

class Borrowerjaminan extends Model
{
    // use Notifiable;
    protected $primaryKey = 'jaminan_id';
    protected $table = 'brw_jaminan';
    protected $guard = 'borrower';

    protected $fillable = [
        'pendanaan_id', 'jaminan_nama', 
        'jaminan_nomor', 'jaminan_jenis',
        'jaminan_nilai', 'jaminan_detail', 
        'status'
    ];

}
