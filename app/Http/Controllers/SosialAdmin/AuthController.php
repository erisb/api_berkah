<?php


namespace App\Http\Controllers\SosialAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use App\UserDanaSosial;
use DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use JWTFactory;
use Illuminate\Support\Facades\Auth;
use App\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Crypt;
use App\Mail\ResetEmailSosial;


class AuthController extends Controller
{

    private function encrypt($value){
        return Crypt::encrypt($value);
      }
    
    private function decrypt($value){
        return Crypt::decrypt($value);
    }

    public function register_pendana(Request $request)
    {
        //validate incoming request 
        $username = $request->input('username');
        $email = $request->input('email');
        $no_hp = $request->input('telepon');

        try {
            $select_username = DB::connection('mysql2')->select("select count(name) as name from users where name = '$username'");
            $select_email = DB::connection('mysql2')->select("select count(email) as email from users where email = '$email'");
            $select_no_hp = DB::connection('mysql2')->select("select count(no_hp) as no_hp from users where no_hp = '$no_hp'");

            if($select_username[0]->name>0){
                return response()->json(['status'=>'failed', 'message' => 'Username sudah terdaftar']);
            }elseif($select_email[0]->email>0){
                return response()->json(['status'=>'failed', 'message' => 'Email sudah terdaftar']);
            }elseif($select_no_hp[0]->no_hp>0){
                return response()->json(['status'=>'failed', 'message' => 'No HP sudah terdaftar']);
            }else{
            
                $user = new User;
                $user->name = trim($request->input('username'));
                $user->email = trim($request->input('email'));
                $user->nama_lengkap = $request->input('nama_lengkap');
                $user->no_hp = trim($request->input('telepon'));
                $user->id_status_user = 2;
                $plainPassword = trim($request->input('password'));
                $user->password = app('hash')->make($plainPassword);

                $user->save();

                return response()->json(['status'=>'success', 'user' => $user, 'message' => 'Register Berhasil']);
            }

        } catch (\Exception $e) {
            // return $e;
            return response()->json(['status'=>'failed', 'message' => $e]);
        }

    }

    public function check_username_register($username){
        
        $select_procedure = DB::connection('mysql2')->select("CALL check_username_danasyariah_db('$username')");
        if($select_procedure[0]->v_out == 'username_exist'){
            $data=[
                'status'=>$select_procedure[0]->v_out,
                'email'=>$select_procedure[0]->email,
                'phone'=>$select_procedure[0]->phone_investor,
            ];
        }elseif ($select_procedure[0]->v_out == 'username_exist_without_detil') {
            $data=[
                'status'=>$select_procedure[0]->v_out,
                'email'=>$select_procedure[0]->email
            ];
        }
        else{
            $data=[
                'status'=>$select_procedure[0]->v_out
            ];
        }

        return response()->json($data);    
    }

    public function check_username_login($username){
        
        $select_procedure = DB::connection('mysql2')->select("CALL check_username_login_danasyariah_db('$username')");
        if($select_procedure[0]->v_out == 'username_exist'){
            $data=[
                'status'=>$select_procedure[0]->v_out
            ];
        }elseif ($select_procedure[0]->v_out == 'username_exist_without_detil') {
            $data=[
                'status'=>$select_procedure[0]->v_out,
                'email'=>$select_procedure[0]->email,
                'nama_lengkap'=>'',
                'no_hp'=>''
            ];
        }elseif($select_procedure[0]->v_out == 'email_sosial_registered'){
            $data=[
                'status'=>$select_procedure[0]->v_out,
                'nama_lengkap'=>$select_procedure[0]->v_nama_investor,
                'no_hp'=>$select_procedure[0]->v_phone_investor,
                'email'=>''
            ];
        }elseif($select_procedure[0]->v_out == 'no_hp_sosial_registered'){
            $data=[
                'status'=>$select_procedure[0]->v_out,
                'nama_lengkap'=>$select_procedure[0]->v_nama_investor,
                'email'=>$select_procedure[0]->v_email,
                'no_hp'=>''
            ];
        }else{
            $data=[
                'status'=>$select_procedure[0]->v_out
            ];
        }

        return response()->json($data);    
    }
	
	public function login(Request $request)
    {
        $status_user = $request->status_user;
        $select_id = DB::connection('mysql2')->select("select count(id) as id from users where name = '$request->name' and id_status_user = '$status_user' ");

        if($select_id[0]->id==0){
            return response()->json(['message' => 'Unauthorized']);
        }else{

            $credentials = $request->only(['name', 'password']);

            if (! $token = Auth::attempt($credentials)) {
                return response()->json(['message' => 'Unauthorized']);
            }

            $select_id = DB::connection('mysql2')->select("select id, id_status_user from users where name = '$request->name'");
            $id_user = $select_id[0]->id; 
            $id_status_user = $select_id[0]->id_status_user;

            return $this->respondWithToken($token, $id_user, $id_status_user);
        }
    }

    public function logout(Request $request)
    {

        $credentials = $request->only(['name', 'password']);

        if (! $token = Auth::attempt($credentials)) {
            return response()->json(['message' => 'Unauthorized']);
        }

        $select_id = DB::connection('mysql2')->select("select id from users where name = '$request->name'");
        $id_user = $select_id[0]->id; 
        
        return $this->respondWithToken($token, $id_user);
    }

    public function sendEmail(Request $request){
        $email = $request->email;
        $cekUser = User::where('email',$email)->count();

        if ($cekUser > 0)
        {
            $count_kode = DB::connection('mysql2')->select("select kode_reset_password from users where email = '$email'");
            $kode_sms = empty($count_kode[0]->kode_reset_password) ? rand(100000, 999999) : $count_kode[0]->kode_reset_password;
            $update = User::where('email', $email)->update(['kode_reset_password' => $kode_sms]);
            $dataUser = User::where('email',$email)->first();
            Mail::to($email)->send(new ResetEmailSosial($dataUser));
            $response = ['status' => '00', 'msg' => 'Berhasil Kirim', 'encrypt'=> $kode_sms];
        }
        else
        {
            $response = ['status' => '01', 'msg' => 'Gagal Kirim' , 'encrypt'=> ''];
        }
        
        return response()->json($response);
    }

    public function check_kode(Request $request){
        $kode = $request->kode;
        $email = $request->email;

        $kode_db = DB::connection('mysql2')->select("select kode_reset_password from users where email = '$email'");


        if ($kode_db[0]->kode_reset_password == $kode)
        {
            $response = ['status' => 'success'];
        }
        else
        {
            $response = ['status' => 'failed'];
        }
        
        return response()->json($response);
    }

    public function changePassword(Request $request){
        $email = $request->email;
        $password = $request->password;
        $kSX = $request->kSX;
        
        $kode = DB::connection('mysql2')->select("select name, kode_reset_password from users where email = '$email'");
        $username = $kode[0]->name;
        if($kode[0]->kode_reset_password!==$kSX){
            $response = ['status' => 'failed_kode'];
        }else{

            $plainPassword = app('hash')->make(trim($password));
            $update = DB::connection('mysql2')->select("CALL edit_password('$username', '$email', '$plainPassword')");

            if($update[0]->v_out=='Sukses'){
                $response = ['status' => 'success'];
            }else{
                $response = ['status' => 'failed'];
            }
        }
        
        return response()->json($response);
    }

    public function add_admin(Request $request){
      
        $username = $request->username;
        $email = $request->email;
        $no_hp = $request->telepon;
        $password = $request->password;
        $tipe_role = $request->tipe_role;

        try {
            $select_username = DB::connection('mysql2')->select("select count(name) as name from users where name = '$username'");
            $select_email = DB::connection('mysql2')->select("select count(email) as email from users where email = '$email'");
            $select_no_hp = DB::connection('mysql2')->select("select count(no_hp) as no_hp from users where no_hp = '$no_hp'");

            if($select_username[0]->name>0){
                return response()->json(['status'=>'failed', 'message' => 'Username sudah terdaftar']);
            }elseif($select_email[0]->email>0){
                return response()->json(['status'=>'failed', 'message' => 'Email sudah terdaftar']);
            }elseif($select_no_hp[0]->no_hp>0){
                return response()->json(['status'=>'failed', 'message' => 'No HP sudah terdaftar']);
            }else{
            
                $user = new User;
                $user->name = trim($username);
                $user->email = trim($email);
                $user->no_hp = trim($no_hp);
                $user->id_status_user = 1;
                $user->id_role_user = $tipe_role;
                $plainPassword = trim($password);
                $user->password = app('hash')->make($plainPassword);

                $user->save();

                return response()->json(['status'=>'success', 'user' => $user, 'message' => 'Register Berhasil']);
            }

        } catch (\Exception $e) {
            // return $e;
            return response()->json(['status'=>'failed', 'message' => $e]);
        }
    }

    public function edit_admin(Request $request){
      
        $id = $request->id;
        $username = $request->username;
        $email = $request->email;
        $no_hp = $request->telepon;
        $password = $request->password;
        $tipe_role = $request->tipe_role;

        try {
            $select_username = DB::connection('mysql2')->select("select count(name) as name from users where name = '$username'  and id not in ('$id') ");
            $select_email = DB::connection('mysql2')->select("select count(email) as email from users where email = '$email' and id not in ('$id')");
            $select_no_hp = DB::connection('mysql2')->select("select count(no_hp) as no_hp from users where no_hp = '$no_hp'  and id not in ('$id') ");

            if($select_username[0]->name>0){
                return response()->json(['status'=>'failed', 'message' => 'Username sudah terdaftar']);
            }elseif($select_email[0]->email>0){
                return response()->json(['status'=>'failed', 'message' => 'Email sudah terdaftar']);
            }elseif($select_no_hp[0]->no_hp>0){
                return response()->json(['status'=>'failed', 'message' => 'No HP sudah terdaftar']);
            }else{

                if($password!==''){
                    $update = User::where('id', $id)->update(['password' => app('hash')->make(trim($password))]);
                }
            
                $update = User::where('id', $id)->update(['name' => $username, 'email'=>$email, 'no_hp'=>$no_hp, 'id_role_user'=>$tipe_role]);

                if($update){
                    $response = ['status' => 'success'];
                }else{
                    $response = ['status' => 'failed'];
                }

                return response()->json($response);
            }

        } catch (\Exception $e) {
            // return $e;
            return response()->json(['status'=>'failed', 'message' => $e]);
        }
    }

    public function delete_user_admin($id){
      
        $delete = DB::connection('mysql2')->table('users')->where('id', $id)->delete();
  
        if($delete){
          $response='sukses';
        }else{
          $response='gagal';
        }
          return response()->json($response);
    }

}