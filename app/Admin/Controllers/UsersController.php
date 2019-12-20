<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Controllers\Dashboard;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Row;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Cache;
use Encore\Admin\Show;
use App\Models\Users;
use Illuminate\Http\Request;



class UsersController extends Controller
{
    use HasResourceActions;

    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->header('Index')
            ->description('description')
            ->body(\view('Users.SendMsg'))
            ->body($this->grid());
    }

    /**
     * Show interface.
     *
     * @param mixed   $id
     * @param Content $content
     * @return Content
     */
    public function show($id, Content $content)
    {
        return $content
            ->header('Detail')
            ->description('description')
            ->body($this->detail($id));
    }

    /**
     * Edit interface.
     *
     * @param mixed   $id
     * @param Content $content
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return $content
            ->header('Edit')
            ->description('description')
            ->body($this->form($id)->edit($id));
    }

    /**
     * Create interface.
     *
     * @param Content $content
     * @return Content
     */
    public function create(Content $content)
    {
        return $content
            ->header('Create')
            ->description('description')
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Users);

        $grid->id('ID')->sortable();
        $grid->created_at('Created at');
        $grid->name('Name');
        $grid->email('Email');
        $grid->line_id('Line Id');
        $grid->user_role('User Role')->display(function ($val){
            if($val == 1){
                return 'Students';
            }else if($val == 2){
                return 'Teachers';
            }else{
                return 'Error Role';
            }
        });
        $grid->login_at('Login At')->display(function ($val){
            return date('Y-m-d H:i:s',$val);
        });
        $grid->login_ip('Login Ip');
        $grid->line_token_expires('Line Expires')->display(function ($val){
            return date('Y-m-d H:i:s',$val);
        });

        $grid->filter(function($filter){
            $filter->disableIdFilter();
            $filter->like('name', 'name');
            $filter->like('email', 'email');
        });
        $grid->actions(function (Grid\Displayers\Actions $actions){
            $actions->append('<a ><i title="PushMsg" class="fa fa-send" onclick="send('.$actions->getKey().')"></i></a> ');
        });

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed   $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Users::findOrFail($id));

        $show->id('ID');
        $show->created_at('Created at');
        $show->name('Name');
        $show->email('Email');
        $show->line_id('Line Id');
        $show->user_role('User Role')->display(function ($val){
            if($val == 1){
                return 'Students';
            }else if($val == 2){
                return 'Teachers';
            }else{
                return 'Error Role';
            }
        });;
        $show->line_token_expires('Line Expires')->display(function ($val){
            return date('Y-m-d H:i:s',$val);
        });

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form($id = 0)
    {
        $form = new Form(new Users);
        $form->display('id', 'ID');

        $form->text('name', 'Name')->rules('required');
        $form->text('email', 'Email')->rules('required');
        $form->password('password', 'Password')->rules('confirmed|required|min:6')->attribute('maxlength','20');
        $form->password('password_confirmation', 'password_confirmation')->rules('required')->default(function ($form) {
            return $form->model()->password;
        });

        $form->ignore(['password_confirmation']);
        $form->select('user_role', 'User Role')->options([1 =>'Students',2 => 'Teachers'])->default(1)->rules('required|in:1,2');
        if($id == 0){
            $form->input('created_at',date('Y-m-d H:i:s'));
        }
        $form->saving(function (Form $form){
            $name = empty($form->model()->name) ? 0 : $form->model()->name;

            if ($form->password && $form->model()->password != $form->password) {
                $form->password = bcrypt($form->password);
            }

            $count = Users::query()->where('name','<>',$name)->where(function($query) use ($form) {
                $query->Orwhere('email',$form->email);
            })->count();
            if( $count > 0 ){
                return back()->withInput()->withErrors([ "email" => ["email can't repeat"] ]);
            }

            if( request()->getMethod() == 'POST' ){
                $form->input('updated_at',date('Y-m-d H:i:s'));
            }
        });
        return $form;
    }

    /**
     * send msg
     * @param $user array   user's info
     * @param $text mixed 
     * @param $state string   What are you want to send? text   now just do that
     */
    public function sendmsg(Request $request){
        //send($id)
        //$user,$text,
        $id = $request->id;
        $text = $request->text;
        $user = Users::findOrFail($id);
        $state = "/message/push";
        if(empty($user['line_id'])){
            return ['state' => false,'msg' => 'User Has No line_id'];
        }
        $access_token = Cache::get('line_client_token');
        if(empty($access_token)){
            $re = $this->getClient_token();
            if(!$re['state']){
                return $re;
            }else{
                $access_token = $re['data']['access_token'];
            }
        }
        if(count($text) > 5){
            return ['state' => false,'msg' => 'Limit Exceeded'];
        }
        $msg = [];
        if(is_array($text)){
            foreach($text as $k => $v){
                $msg[] = [
                    'type' => "text",
                    'text' => $v,
                ];
            }
        }else{
            $msg[] = [
                'type' => "text",
                'text' => $text,
            ];
        }
        $data = $this->send_curl(config('app.line_bot').$state,'POST',json_encode(['to' => $user['line_id'],'msg'=>$msg]),['Content-Type'=>'application/json','Authorization'],0,1);
    }

}
