<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LogAkadDigiSignBorrower extends Model
{
    protected $table = 'log_akad_digisign_borrower';
    protected $primaryKey = 'id_log_akad_borrower';
	
	 protected $fillable = [
        'brw_id', 'id_proyek', 
        'provider_id', 'total_pendanaan',
        'document_id', 'status', 
        'tgl_sign'
    ];
}