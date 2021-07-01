<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
// use Illuminate\Notifications\Notifiable;
// use Illuminate\Contracts\Auth\CanResetPassword;
// use Illuminate\Foundation\Auth\User as Authenticatable;

class BorrowerRiwayatMutasi extends Model
{
    // use Notifiable;
    //protected $primaryKey = 'brw_id';
    protected $table = 'brw_riwayat_mutasi';
    protected $guard = 'borrower';

    protected $fillable = [
        'brw_id', 'nominal', 'tipe', 'status'
    ];

}
