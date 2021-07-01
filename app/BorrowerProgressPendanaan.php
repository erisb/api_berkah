<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
// use Illuminate\Notifications\Notifiable;
// use Illuminate\Contracts\Auth\CanResetPassword;
// use Illuminate\Foundation\Auth\User as Authenticatable;

class BorrowerProgressPendanaan extends Model
{
    // use Notifiable;
    //protected $primaryKey = 'brw_id';
    protected $table = 'brw_progress_pendanaan';
    protected $guard = 'borrower';

    protected $fillable = [
        'pendanaan_id', 'image_1', 'image_2', 'image_3','image_4', 'status', 'keterangan'
    ];

}
