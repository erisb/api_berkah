<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
// use Illuminate\Notifications\Notifiable;
// use Illuminate\Contracts\Auth\CanResetPassword;
// use Illuminate\Foundation\Auth\User as Authenticatable;

class BorrowerPersyaratanInsert extends Model
{
    // use Notifiable;
    //protected $primaryKey = 'brw_id';
    protected $table = 'brw_persyaratan_insert';
    protected $guard = 'borrower';

    protected $fillable = [
        'persyaratan_insert_id','brw_id', 'tipe_id','user_type', 'persyaratan_id', 'checked'
    ];

}
