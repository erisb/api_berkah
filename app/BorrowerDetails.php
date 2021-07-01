<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
// use Illuminate\Notifications\Notifiable;
// use Illuminate\Contracts\Auth\CanResetPassword;
// use Illuminate\Foundation\Auth\User as Authenticatable;

class BorrowerDetails extends Model
{
    // use Notifiable;
    
    protected $primaryKey = 'brw_id';
    protected $table = 'brw_users_details';
    
    protected $fillable = [
        'brw_id','nama', 'nm_bdn_hukum', 'jabatan', 'brw_type', 'nm_ibu', 
        'ktp','npwp', 'tgl_lahir', 'jns_kelamin', 'status_kawin', 'status_rumah', 
        'alamat','domisili_alamat', 'domisili_provinsi','domisili_kota','domisili_kecamatan','domisili_kelurahan','domisili_kelurahan','domisili_kd_pos',  'provinsi', 'kota','kecamatan','kelurahan', 'kode_pos', 'agama', 'tempat_lahir','pendidikan_terakhir','pekerjaan', 
        'bidang_perusahaan', 'bidang_pekerjaan','bidang_online','pengalaman_pekerjaan','pendapatan','total_aset',
        'kewarganegaraan', 'brw_online', 'brw_pic','brw_pic_ktp','brw_pic_user_ktp','brw_pic_npwp' 
    ];

}
