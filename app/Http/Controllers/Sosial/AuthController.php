<?php


namespace App\Http\Controllers\Sosial;

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


class AuthController extends Controller
{
    public function register_pendana(Request $request)
    {
        //validate incoming request 
        

        try {

            $user = new User;
            $user->name = trim($request->input('username'));
            $user->email = trim($request->input('email'));
            $user->nama_lengkap = trim($request->input('nama_lengkap'));
            $user->no_hp = trim($request->input('telepon'));
            $user->id_status_user = 2;
            $plainPassword = trim($request->input('password'));
            $user->password = app('hash')->make($plainPassword);

            $user->save();

            //return successful response
            return response()->json(['user' => $user, 'message' => 'CREATED'], 201);

        } catch (\Exception $e) {
            //return error message
            return response()->json(['message' => 'User Registration Failed!'], 409);
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
	
	public function login(Request $request)
    {
        //echo "radi";die;
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

}