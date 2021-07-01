<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
// use Illuminate\Notifications\Notifiable;
// use Illuminate\Contracts\Auth\CanResetPassword;
// use Illuminate\Foundation\Auth\User as Authenticatable;

class BorrowerRekening extends Model
{
    // use Notifiable;
    protected $primaryKey = 'brw_id';
    protected $table = 'brw_rekening';
    protected $guard = 'borrower';

    protected $fillable = [
        'brw_id', 'va_number','brw_norek','brw_nm_pemilik','brw_kd_bank', 'total_plafon', 'total_terpakai','total_sisa'
    ];

}
