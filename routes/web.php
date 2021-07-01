<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/


$router->get('/', function () use ($router) {
    return 'Hayo Mau ngapain bro..';
});

//prefix
$router->group(['prefix' => 'borrower'], function () use ($router) {
    /******** LOGIN & REGISTER */
    
    $router->post('login','Borrower\LoginController@Login');
    $router->post('register','Borrower\RegisterController@Register');
    $router->post('resetPassword','Borrower\ResetController@sendEmail');
    $router->post('resetPasswordProses','Borrower\ResetController@changePassword');
    /******** END LOGIN & REGISTER */
    
    /******** GET DATA */
    $router->get('data_pendidikan', 'Borrower\DataController@DataPendidikan');
    $router->get('test', 'Borrower\DataController@Test');
    $router->get('check_nik/{nik}', 'Borrower\DataController@CheckNIK');
    $router->get('check_nik_bh/{nik}', 'Borrower\DataController@CheckNIKBH');
    $router->get('check_no_tlp/{noTLP}', 'Borrower\DataController@CheckNOTLP');
    $router->get('data_provinsi', 'Borrower\DataController@DataProvinsi');
    $router->get('data_kota/{id}', 'Borrower\DataController@DataKota');
    $router->get('data_pekerjaan', 'Borrower\DataController@DataPekerjaan');
    $router->get('data_pengalaman_pekerjaan', 'Borrower\DataController@DataPengalamanPekerjaan');
    $router->get('data_pendapatan', 'Borrower\DataController@DataPendapatan');
    $router->get('data_bank', 'Borrower\DataController@DataBank');
    $router->get('tipe_pendanaan', 'Borrower\DataController@TipePendanaan');
    $router->get('jenis_jaminan', 'Borrower\DataController@JenisJaminan');
    $router->get('persyaratan_pendanaan/{status_id}/{tipe_id}', 'Borrower\DataController@PersyaratanPendanaan');
    $router->get('persyaratan_pendanaan_proses_pengajuan/{brw_id}/{user_type}/{tipe_id}', 'Borrower\DataController@PersyaratanPendanaanProsesPengajuan');
    $router->get('jenis_kelamin', 'Borrower\DataController@JenisKelamin');
    $router->get('agama', 'Borrower\DataController@Agama');
    $router->get('status_perkawinan', 'Borrower\DataController@StatusPerkawinan');
    $router->get('status_rumah', 'Borrower\DataController@KepemilikanRumah');
    $router->get('bidang_pekerjaan_online', 'Borrower\DataController@BidangPekerjaanOnline');
    $router->get('cek_password/{id}', 'Borrower\DataController@cek_password');
    $router->get('getProfileBrw/{id}', 'Borrower\DataController@getProfileBrw');

    
    /******** END GET DATA */

    /******** ACTION PROSES DATA */
    $router->post('proses_lengkapi_profile','Borrower\ProsesController@proses_lengkapi_profile');
    $router->post('proses_pendanaan','Borrower\ProsesController@proses_pendanaan');
    /******** END ACTION PROSES DATA */

    /*********Pendanaan */
    $router->get('dashPendanaan', 'PendanaanController@showDashboard');
    $router->get('brwByid/{brw_id}', 'PendanaanController@allPendanaanById');
    $router->get('brwBerjalanById/{brw_id}','PendanaanController@pendanaanBerjalanByid');
    $router->get('brwPengajuanById/{brw_id}','PendanaanController@pendanaanPengajuanByid');
    $router->get('brwSelesaiById/{brw_id}','PendanaanController@pendanaanSelesaiByid');
    $router->get('listPro/{brw_id}','PendanaanController@listproyek');
    $router->get('plaf/{brw_id}','PendanaanController@plafon');  
    $router->get('pykid/{pyk_id}','PendanaanController@pykid');
    $router->get('danaTerkumpul/{pyk_id}','PendanaanController@danaTerkumpul');
    //$router->get('danatbProyekTerkumpul/{pyk_id}','PendanaanController@danatbProyekTerkumpul');
    $router->get('danatbProyekTerkumpul/{brw_id}','PendanaanController@danatbProyekTerkumpul');
    $router->get('totalImbalHasil/{brw_id}','PendanaanController@totalImbalHasil');
    $router->get('pykGambar/{pyk_id}','PendanaanController@pykGambar');
    $router->get('pykDesk/{pyk_id}','PendanaanController@pykDesk');
    $router->get('updateSession/{brw_id}','PendanaanController@updateSession');
    $router->get('getlastproyekapp/{brw_id}','PendanaanController@getlastproyekapp');

    //status
    $router->get('statusbrw/{brw_id}','Borrower\DataController@statusbrw');

    $router->post('proses_otp','Borrower\ProsesController@prosesOTP');
    $router->post('cek_otp','Borrower\ProsesController@cekOTP');
    $router->post('updateSP3','Borrower\ProsesController@updateSP3');

    //verifikasi Pembayaran
    $router->get('dataCair','verifikasiPembayaranController@getdataPendanaanCair');

});

// Borrower Admin Start Here
$router->group(['prefix' => 'borrower-admin'], function () use ($router) { 

  // Borrower Admin Client Side Data 
  $router->group(['prefix' => 'client-side'], function () use ($router) { 
        $router->get('jenisPendanaanPage', 'BorrowerAdmin\ClientController@getTableJenis');
        $router->get('tableBorrowerData','BorrowerAdmin\ClientController@tableBorrowerData');  
        $router->get('DataBorrower','BorrowerAdmin\ClientController@DataBorrower');
        $router->get('DataPendidikan','BorrowerAdmin\ClientController@DataPendidikan');
        $router->get('DataJenisKelamin','BorrowerAdmin\ClientController@DataJenisKelamin'); 
        $router->get('DataAgama','BorrowerAdmin\ClientController@DataAgama');    
        $router->get('DataNikah','BorrowerAdmin\ClientController@DataNikah');
        $router->get('DataProvinsi','BorrowerAdmin\ClientController@DataProvinsi');
        $router->get('DataKota/{kota}','BorrowerAdmin\ClientController@DataKota');
        $router->get('GantiDataKota/{kota}','BorrowerAdmin\ClientController@GantiDataKota');
        $router->get('DataBank','BorrowerAdmin\ClientController@DataBank');
        $router->get('DataPekerjaan','BorrowerAdmin\ClientController@DataPekerjaan');
        $router->get('DataBidangPekerjaan','BorrowerAdmin\ClientController@DataBidangPekerjaan');
        $router->get('DataBidangOnline','BorrowerAdmin\ClientController@DataBidangOnline');
        $router->get('DataPengalaman','BorrowerAdmin\ClientController@DataPengalaman');
        $router->get('DataPendapatan','BorrowerAdmin\ClientController@DataPendapatan');
        $router->get('getDetailsBorrower/{borrower_id}','BorrowerAdmin\ClientController@DetailsDataBorrower');
        $router->get('DataDokumenBorrower','BorrowerAdmin\ClientController@DataDokumenBorrower');
  });

  // Borrower Server Side Data
  $router->group(['prefix' => 'server-side'], function () use ($router) { 
      $router->post('postJenisPendanaan', 'BorrowerAdmin\ProsessController@postJenisPendanaan'); 
      $router->post('updateJenisPendanaan','BorrowerAdmin\ProsessController@updateJenisPendanaan');
      $router->post('updateTipeJenis','BorrowerAdmin\ProsessController@updateTipeJenis');
      $router->post('postDeleteJenis', 'BorrowerAdmin\ProsessController@postDeleteJenis');
      $router->post('newPostTipeJenis','BorrowerAdmin\ProsessController@newPostTipeJenis'); 
      
      $router->post('getListJenis','BorrowerAdmin\ProsessController@getListJenis');
      $router->post('getListJenisA','BorrowerAdmin\ProsessController@getListJenisA');
      $router->post('getListJenisB','BorrowerAdmin\ProsessController@getListJenisB');

      $router->post('postTotalScoring','BorrowerAdmin\ProsessController@postTotalScoring');
      $router->post('rejectScorringBorrower','BorrowerAdmin\ProsessController@rejectScorringBorrower');

      $router->get('genVA_borrower/{username}/{id_proyek}','BorrowerAdmin\ProsessController@generateVABNI_Borrower');
    //   $router->get('genVA_borrower','BorrowerAdmin\ProsessController@generateVABNI_Borrower');
    
  });
    
});
// Borrower Admin End Here


  // START PENDANAAN SOSIAL

  // ROUTE USERS
  $router->group(['prefix' => 'user_sosial'], function () use ($router){

    /********************** GET DATA ***********************************/

    $router->get('countPendanaan/{id}', 'Sosial\UserController@countPendanaan');
    $router->get('list_pendanaan/{id}', 'Sosial\UserController@list_pendanaan'); // list pendanaan user
    $router->get('list_pendanaan_pembayaran_proses/{id_user}/{id_pendanaan}', 'Sosial\UserController@list_pendanaan_pembayaran_proses'); // list pendanaan user 
    $router->get('list_pendanaan_pembayaran_selesai/{id_user}/{id_pendanaan}', 'Sosial\UserController@list_pendanaan_pembayaran_selesai'); // list pendanaan user
    $router->get('SelectDashboardUser/{id_user}', 'Sosial\UserController@select_dashboard_user');
    
    

    /********************** ACTION **************************************/


    /********************** Login & REGISTER **************************************/
    $router->get('checkUsernameExistingRegister/{username}', 'SosialAdmin\AuthController@check_username_register');
    $router->get('checkUsernameExistingLogin/{username}', 'SosialAdmin\AuthController@check_username_login');
    $router->post('AddPendana', 'SosialAdmin\AuthController@register_pendana');
    $router->post('login', 'SosialAdmin\AuthController@login');

  });

  // ROUTE ADMIN
  $router->group(['prefix' => 'admin_sosial'], function () use ($router){


    /********************** GET DATA ***********************************/
    $router->get('SelectTindikator', 'SosialAdmin\AdminController@select_landing_t_indikator');
    $router->get('SelectUrlZakat', 'SosialAdmin\AdminController@select_url_zakat');
    $router->get('SelectPendanaanLanding', 'SosialAdmin\AdminController@select_pendanaan_landing');
    $router->get('GetDetailPendanaanLanding/{id_pendanaan}', 'SosialAdmin\AdminController@get_pendanaan_landing');
    $router->get('SelectPagePendanaan', 'SosialAdmin\AdminController@select_page_pendanaan_sosial'); // select all pendanaan status 2
    $router->get('SelectPagePendanaanSelesai', 'SosialAdmin\AdminController@select_page_pendanaan_sosial_selesai'); // select all pendanaan status ! = 2
    
    $router->get('SelectPageZiswaf', 'SosialAdmin\AdminController@select_page_ziswaf');
    $router->get('SelectPendanaanAdmin', 'SosialAdmin\AdminController@select_pendanaan_admin');
    $router->get('SelectYayasan', 'SosialAdmin\AdminController@select_m_yayasan');
    $router->get('fetchAddPendanaan', 'SosialAdmin\AdminController@fetch_add_pendanaan');
    $router->get('tampilPoto/{id}', 'SosialAdmin\AdminController@tampilPoto');
    $router->get('tampilPotoYayasan/{nama_yayasan}', 'SosialAdmin\AdminController@tampilPotoYayasan');
    $router->get('SelectDashboardAdmin', 'SosialAdmin\AdminController@select_dashboard_admin');
    $router->get('SelectMutasiAdmin', 'SosialAdmin\AdminController@select_riwayat_mutasi_admin');
    $router->get('SelectMutasiAdminDetail/{id}', 'SosialAdmin\AdminController@select_riwayat_mutasi_admin_detail');
    $router->post('GetTempDonasi', 'SosialAdmin\TransferController@get_temp_donasi');
    $router->get('getDataPendanaan/{id_pendanaan}', 'SosialAdmin\AdminController@select_data_pendanaan'); 
    $router->get('getYayasan/{id}', 'SosialAdmin\AdminController@select_edit_m_yayasan');

    $router->get('getMenuAdmin', 'SosialAdmin\AdminController@select_menu_dashboard_admin');
    $router->get('getRoleAdmin', 'SosialAdmin\AdminController@select_role_dashboard_admin');
    $router->get('getEditRole/{id}', 'SosialAdmin\AdminController@select_edit_user_role_menu');
    $router->get('getEditAdmin/{id}', 'SosialAdmin\AdminController@select_edit_user_admin');

    /********************** ACTION **************************************/
    $router->post('AddPendanaan', 'SosialAdmin\AdminController@add_pendanaan');
    $router->post('UploadGambarPendanaan', 'SosialAdmin\AdminController@upload_gambar_pendanaan');  
    $router->post('UploadGambarCampaigner', 'SosialAdmin\AdminController@upload_gambar_campaigner');
    $router->get('ActionEditProyek', 'SosialAdmin\AdminController@action_edit_pendanaan');
    $router->get('DeletePendanaan/{id_pendanaan}', 'SosialAdmin\AdminController@delete_pendanaan'); 
    $router->post('EditDataPendanaan', 'SosialAdmin\AdminController@save_edit_pendanaan'); 
    $router->post('AddYayasan', 'SosialAdmin\AdminController@add_yayasan');
    $router->post('EditYayasan', 'SosialAdmin\AdminController@edit_yayasan');
    $router->get('DeleteYayasan/{id}', 'SosialAdmin\AdminController@delete_yayasan'); 

    $router->post('AddRoleMenu', 'SosialAdmin\AdminController@add_user_role_menu');
    $router->post('EditRoleMenu', 'SosialAdmin\AdminController@edit_user_role_menu');
    $router->get('DeleteRole/{id}', 'SosialAdmin\AdminController@delete_user_role_menu');

    /********************** PEMBAYARAN **************************************/
    $router->post('CheckDonasiTemp', 'SosialAdmin\TransferController@check_donasi_temp');
    $router->post('AddDonasiTemp', 'SosialAdmin\TransferController@add_donasi_temp');
    $router->post('downloadInvoice', 'SosialAdmin\TransferController@download_invoice');


    /********************** Login & REGISTER **************************************/
    $router->get('checkUsernameExistingRegister/{username}', 'SosialAdmin\AuthController@check_username_register');
    $router->get('checkUsernameExistingLogin/{username}', 'SosialAdmin\AuthController@check_username_login');
    $router->post('AddPendana', 'SosialAdmin\AuthController@register_pendana');
    $router->post('login', 'SosialAdmin\AuthController@login');
    $router->post('resetPassword','SosialAdmin\AuthController@sendEmail');
    $router->post('resetPasswordProses','SosialAdmin\AuthController@changePassword');
    $router->post('checkKode','SosialAdmin\AuthController@check_kode');
    $router->post('AddAdmin', 'SosialAdmin\AuthController@add_admin');
    $router->post('EditAdmin', 'SosialAdmin\AuthController@edit_admin');
    $router->get('DeleteUserAdmin/{id}', 'SosialAdmin\AuthController@delete_user_admin');

    /********************** Dashboard Donatur **************************************/
    $router->get('getUser/{id}', 'SosialAdmin\AdminController@select_edit_user');
    $router->post('editUser', 'SosialAdmin\AdminController@edit_user');


    /********************** BNI, BASNAZ, SMS **************************************/
    $router->get('va_bni/{id_user}', 'SosialAdmin\IntegrationController@generateVA_BNI_Sosial');
    $router->post('BNISTransferResponse', 'SosialAdmin\IntegrationController@bnis_response_transfer');

    $router->post('updateBillingBNI', 'SosialAdmin\IntegrationController@updateBillingBNI');
    $router->post('inquiryBNI', 'SosialAdmin\IntegrationController@inquiry_bni');

    $router->get('RegisterBasnaz/{va_number}', 'SosialAdmin\IntegrationController@register_basnaz');
    $router->get('TransferBasnaz/{va_number}', 'SosialAdmin\IntegrationController@transfer_basnaz');
    $router->post('KalkulatorBasnaz', 'SosialAdmin\IntegrationController@kalkulator_basnaz');
    $router->get('SuccessTransferSms/{va_number}/{customer_name}/{amount}', 'SosialAdmin\IntegrationController@success_transfer_sms');
  });
  
	
	// END PENDANAAN SOSIAL