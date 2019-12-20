<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Support\Facades\Auth;
use Validator;

class PassportController extends Controller
{

    public $successStatus = 200;

    /**
     * login api
     *
     * @return \Illuminate\Http\Response
     */
    public function login(){
        if(Auth::attempt(['email' => request('email'), 'password' => request('password')])){
            $user = Auth::user();
            $success['token'] =  $user->createToken('MyApp')->accessToken;
            $success['user'] = [
                'name' => $user['name'],
                'email' => $user['email'],
                'user_role' => $user['user_role'],
                'line_id' => $user['line_id'],
                'remember_token' => $user['remember_token']
            ];
            return response()->json([
                'status'  => true,
                'data' => $success,
                'msg' => 'success'
            ]);
        }
        else{
            return response()->json([
                'status'  => false,
                'msg' => 'error'
            ]);
        }
    }

    /**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
            'c_password' => 'required|same:password',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'msg' => 'error'
            ]);       
        }

        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
	$input['created_at'] = time();
	$input['updated_at'] = time();
        $user = User::create($input);
        $success['token'] =  $user->createToken('MyApp')->accessToken;
        $success['user'] =  [
	    'name' => $user->name,
	    'user_role' => 1,
            'line_id' => '',
	    'email' => $user['email']
	];

        return response()->json([
            'status'  => true,
            'data' => $success,
            'msg' => 'success'
        ]);
    }

    /**
     * details api
     *
     * @return \Illuminate\Http\Response
     */
    public function getDetails()
    {
        $user = Auth::user();
        return response()->json([
            'status'  => true,
            'data' => [
                'name' => $user['name'],
                'email' => $user['email'],
                'user_role' => $user['user_role'],
                'line_id' => $user['line_id'],
                'remember_token' => $user['remember_token']
            ],
            'msg' => 'success'
        ]);
    }
}