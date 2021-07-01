<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
// use Illuminate\Notifications\Notifiable;
// use Illuminate\Contracts\Auth\CanResetPassword;
// use Illuminate\Foundation\Auth\User as Authenticatable;

class Borrowerdeskripsiproyek extends Model
{
    // use Notifiable;
    protected $primaryKey = 'id';
    protected $table = 'deskripsi_proyeks';
    // protected $guard = 'borrower';

    protected $fillable = [
        'deskripsi'
    ];

}
