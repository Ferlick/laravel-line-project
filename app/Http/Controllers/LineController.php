<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\UsersAction;
use App\Models\Users;
use App\User;

use Validator;

class LineController extends Controller
{
    /**
     * get Line return message
     */
    //protected $first_url = 'https://access.line.me/oauth2/v2.1/authorize';
    //protected $second_url = 'https://api.line.me/oauth2/v2.1/token';
    //get line login(can't get the email,I don't how can do It)

    public function getStart(){
        $state = time().$this->getName(8);
        $nonce = $this->getName(8);
        $url = config('app.line_v2').'authorize?scope=openid%20email%20profile&response_type=code&state='.$state.'&redirect_uri='.config('app.line_redirect_url').'&nonce='.$nonce.'&client_id='.config('app.client_id');
        return $url;
    }

    public function returnLine(Request $request){
        $info = $request->all();
        $token = '';
        if(!empty($info['code'])){
            $arr = [
                'grant_type' => 'authorization_code',
                'code' => $info['code'],
                'redirect_uri' => config('app.line_redirect_url'),
                'client_id' => config('app.line_client_id'),
                'client_secret' => config('app.line_client_secret'),
                'state' => $info['state']
            ];
            $data = $this->send_curl(config('app.line_v2').'token','POST',$arr,['Content-Type'=>'application/x-www-form-urlencoded'],0,2);
            $_user = [];
            if($data){
                $req = json_decode($data,true);
                if(isset($data['access_token']) && !empty($data['access_token'])){
                    $req_q = $this->vaild($data['id_token']);
                    if(isset($req_q[1]['name']) && !empty($req_q[1]['name'])){
                        //check the user in the database
                        $user = Users::select('id')->where(['name' => $req_q[1]['name']])->first();
                        if(!empty($user)){
                            $user = User::where('id',$user->id)->update([
                                'line_access_token' => $data['access_token'],
                                'line_id' => $req_q[1]['sub'],
                                'updated_at' => time(),
                                'line_token_expires' => time() + $data['expires_in']
                            ]);
                        }else{
                            $input = [
                                'line_access_token' => $data['access_token'],
                                'line_id' => $req_q[1]['sub'],
                                'name' => $req_q[1]['name'],
                                'created_at' => time(),
                                'updated_at' => time(),
                                'password' => bcrypt(config('app.default_password')),
                                'line_token_expires' => time() + $data['expires_in']
                            ];
                            $user = User::create($input); 
                        }
                        $token = $user->createToken('MyApp')->accessToken;
                    }else{
                        return ['msg' => "can't get the line name",'state' => false];
                    }
                }else{
                    return ['msg' => "can't get the line access_token",'state' => false];
                }
            }else{
                return ['msg' => "can't get the line url",'state' => false];
            }
        }else{
            //tell the line . I do this all
            echo 'success';
            return;
        }
        return ['msg' => 'success','state'=>true,'data'=>['token'=>$token]];
    }

    private function vaild($id_token){
        $token_arr = explode('.',$id_token);
        $str = [];
        foreach($token_arr as $k => $v){
            $str[$k] = json_decode(base64_decode($v),true);
        }
        return $str;
    }
}