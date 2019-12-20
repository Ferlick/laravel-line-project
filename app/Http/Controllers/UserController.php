<?php
namespace App\Http\Controllers;

header('Access-Control-Allow-Origin: *');
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\Models\Users;
use App\Models\Follow;
use Illuminate\Support\Facades\Auth;
use Validator;

class UserController extends Controller
{
    /**
     * teachersList
     */
    public function teacherList(Request $request){
        $page = isset($request->page)?$request->page:1;
        $pageSize = isset($request->pageSize)?$request->pageSize:20;
        $offset = ($page - 1)*$pageSize;
        $count = Users::select('name','email')->where(['user_role' => 2])->count();
        $data = Users::select('id','name','email')->where(['user_role' => 2])->offset($offset)->limit($pageSize)->get();
        return response()->json([
            'status'  => true,
            'data' => $data,
            'count' => $count,
            'msg' => 'success'
        ]);
    }

    /**
     * studentsList
     */
    public function studentsList(Request $request){
        $page = isset($request->page)?$request->page:1;
        $pageSize = isset($request->pageSize)?$request->pageSize:20;
        $offset = ($page - 1)*$pageSize;
        $count = Users::select('name','email')->where(['user_role' => 1])->count();
        $data = Users::select('id','name','email')->where(['user_role' => 1])->offset($offset)->limit($pageSize)->get();
        return response()->json([
            'status'  => true,
            'data' => $data,
            'count' => $count,
            'msg' => 'success'
        ]);
    }

    /**
     * teacher => who follow me
     * students => who that I follow
     */
    public function myFollow(Request $request){
        $user = Auth::user();
        $page = isset($request->page)?$request->page:1;
        $pageSize = isset($request->pageSize)?$request->pageSize:20;
        $offset = ($page - 1)*$pageSize;
        if($user['user_role'] == 1){
            $count = Follow::select('id')->where(['s_uid' => $user['id']])->count();
            $data = Follow::from('follow as f')
                ->select('u.id','u.name','u.email')
                ->join('users as u', 'u.id', '=', 'f.t_uid')
                ->where('f.s_uid' , $user['id'])
                ->offset($offset)
                ->limit($pageSize)
                ->get();
        }else if($user['user_role'] == 2){
            $count = Follow::select('id')->where(['t_uid' => $user['id']])->count();
            $data = Follow::from('follow as f')
                ->select('u.id','u.name','u.email')
                ->join('users as u', 'u.id', '=', 'f.s_uid')
                ->where('f.t_uid' , $user['id'])
                ->offset($offset)
                ->limit($pageSize)
                ->get();
        }else{
            return response()->json([
                'status'  => false,
                'msg' => 'fail,error user role'
            ]);
        }
        return response()->json([
            'status'  => true,
            'data' => $data,
            'count' => $count,
            'msg' => 'success'
        ]);
    }

    /**
     * follow or cancel follow teacher
     */
    public function checkFollow(Request $request){
        $user = Auth::user();
        if($user['user_role'] == 2){
            return response()->json([
                'status'  => false,
                'msg' => "teacher can't follow other role"
            ]);
        }else{
            if(!isset($request->t_uid) || empty($request->t_uid)){
                return response()->json([
                    'status'  => false,
                    'msg' => "teacher can't be null"
                ]);
            }
            $t_uid = $request->t_uid;
            $user_info = Users::select('user_role')->where('id',$t_uid)->first();
            if(empty($user_info) || $user_info->user_role != 2){
                return response()->json([
                    'status'  => false,
                    'msg' => "user_role has error"
                ]);
            }
            $followInfo = Follow::select('id')->where(['s_uid' => $user['id'],'t_uid' => $request->t_uid])->first();
            $msg = '';
            if(!empty($followInfo)){
                Follow::where('id', $followInfo->id)->delete();
                $msg = 'You cancel the follow';
            }else{
                $id = Follow::insertGetId(['s_uid' => $user['id'],'t_uid' => $t_uid,'created_at' =>time()]);
                if(!$id){
                    return response()->json([
                        'status'  => false,
                        'msg' => "something get error"
                    ]);
                }
                $msg = 'You follow the teacher';
            }
            return response()->json([
                'status'  => true,
                'msg' => $msg
            ]);
        }
    }
}