<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BorrowerScorringTotal extends Model
{
    protected $primaryKey = 'brw_id';
    protected $table = 'brw_scorring_total';
    protected $guard = 'borrower';

    protected $fillable = [
        'pendanaan_id','brw_id','scorring_total','scorring_grade','scorrinng_keterangan'
    ];
}
