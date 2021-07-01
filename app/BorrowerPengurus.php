<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
// use Illuminate\Notifications\Notifiable;
// use Illuminate\Contracts\Auth\CanResetPassword;
// use Illuminate\Foundation\Auth\User as Authenticatable;

class BorrowerPengurus extends Model
{
    // use Notifiable;
    //protected $primaryKey = 'brw_id';
    protected $table = 'brw_pengurus';
    protected $guard = 'borrower';

    protected $fillable = [
        'brw_id', 'nm_pengurus', 'nik_pengurus', 'no_tlp','jabatan' 
    ];

}
