<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BorrowerPersyaratanPendanaan extends Model
{
    protected $table = 'brw_persyaratan_pendanaan';
    // protected $primaryKey = 'persyaratan_id';
    protected $guard = 'borrower';
	protected $fillable = [
        'persyaratan_id','tipe_id', 'user_type', 'persyaratan_nama', 'persyaratan_mandatory'
    ];
}
