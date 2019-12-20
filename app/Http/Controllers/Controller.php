<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Cache;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    
    /*
	 * 	curl
	 * 入参
	 * 	  url     请求路径
	 * 	  type    POST || GET
	 * 	  body    请求数据数组
	 * 	  headers 请求头数组
	 * 	  $timeout 超时时间
	 */
	public function send_curl($url,$type = 'POST',$body = array(),$headers = array(), $timeout = 0, $post_string = 1){
		$send = $body;
		if($post_string == 2){
			$send = http_build_query($body);
		}
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $type);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$send);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		
		//curl超时设置 0 表示无限等待
		if($timeout > 0){
			curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		}
		$output = curl_exec($ch);
		curl_close($ch);
		return $output;
	}

	function getName($n) { 
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'; 
		$randomString = ''; 
	  
		for ($i = 0; $i < $n; $i++) { 
			$index = rand(0, strlen($characters) - 1); 
			$randomString .= $characters[$index]; 
		} 
		return $randomString; 
	} 

	/*
	 * 	get Token
	 */
	public function getClient_token(){
        $arr = [
            'grand_type' => 'client_credentials',
            'client_id' => config('app.line_msg_client_id'),
            'client_secret' => config('app.line_msg_client_id')
        ];
        $data = $this->send_curl(config('app.line_bot').'token','POST',$arr,['Content-Type'=>'application/x-www-form-urlencoded'],0,2);
        if($data){
            $re = json_decode($data,true);
            if(isset($re['access_token']) && !empty($re['access_token'])){
                Cache::put('line_client_token', $re['access_token'], $re['expires_in'] / 60);
                return ['state'=>true,'data'=>$re,'msg'=>'success'];
            }
        }
        return ['state'=>false,'msg'=>"can't get access token"];
    }
}
