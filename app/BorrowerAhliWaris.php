<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
// use Illuminate\Notifications\Notifiable;
// use Illuminate\Contracts\Auth\CanResetPassword;
// use Illuminate\Foundation\Auth\User as Authenticatable;

class BorrowerAhliWaris extends Model
{
    // use Notifiable;
    protected $primaryKey = 'brw_id';
    protected $table = 'brw_ahli_waris';
    protected $guard = 'borrower';

    protected $fillable = [
        'brw_id', 'nama_ahli_waris', 'nik', 'no_tlp','email','provinisi','kota','kecamatan','kelurahan','kd_pos','alamat'
    ];
    
}
