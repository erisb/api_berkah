<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
// use Illuminate\Notifications\Notifiable;
// use Illuminate\Contracts\Auth\CanResetPassword;
// use Illuminate\Foundation\Auth\User as Authenticatable;

class BorrowerScorringPendanaan extends Model
{
    // use Notifiable;
    //protected $primaryKey = 'brw_id';
    protected $table = 'brw_scorring_pendanaan';
    protected $guard = 'borrower';

    protected $fillable = [
        'pendanaan_id', 'scorring_judul', 'scorring_nilai', 'user_create'
    ];

}
