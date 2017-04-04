<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    /**
     * 客户端用户注册
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $data = $request->all();
        //todo 电话格式验证,密码格式验证等
        $validator = $this->validate($request, [
            'account' => 'required|max:32',
            'password' => 'required|min:6',
            'company' => 'required',
            'mobile' => 'required|mobile',
        ]);
        if($validator) return response()->json($this->error('vaildFail',$validator));

        //todo 用户名验证
        $result = \App\Models\User::getByAccount($data['account']);
        if(!$result) return response()->json($this->error('createExist'));

        //todo 判断此公司名下是否已经有注册过的手机号
        $result = \App\Models\User::getByCompany($data['company']);
        if($result)
        {
            foreach ($result as $key => $value)
            {
                if($value->mobile == $data['mobile'])
                {
                    return response()->json($this->error('mobileExist'));
                }
            }
        }

        //todo 如果有关联专属运维则进行关联逻辑
        if($data['admin_id'])
        {
            $admin = \App\Models\Admin::getByIDOrAccount($data['admin_id']);
            if (!$admin)
            {
                return response()->json($this->error('notFoundAdmin'));
            }
            $data['admin_id'] = $admin->id;
        }
        $lastInsertID =  \App\Models\User::create($data);
        $user = \App\Models\User::getByID($lastInsertID);
        if(!$user) return response()->json($this->error('registerFail'));
        return response()->json($this->success($user,'注册成功'));

    }

    public function getByID($id)
    {
        $result = \App\Models\User::getByID($id);
        if(!$result) return response()->json($this->error('notFound'));
        return response()->json($this->success($result));
    }

    public function getList()
    {
        $pageID = 2;
        $pageSize = 10;
        $users = \DB::table(TABLE_USER)->skip(($pageID-1)* $pageSize)->take($pageSize)->get();
        print_r($users);echo '<br>';

    }


    public function show($id)
    {
        //select
        $user = app('db')->select("SELECT * FROM user where id = $id");
//        $results = \DB::select('SELECT * FROM user where id = ? ', [2]);
//        $results = \DB::select('select * from user where id = :id', ['id' => 1]);

        //insert
//        $result = \DB::insert('insert into user (name, age) values (?, ?)', [ 'Dayle',15]);

        //update
//        $affected = \DB::update('update user set name = "lisi123" where id = ?', [24]);

        //delete
//        $deleted = \DB::delete('delete from user where id = ?',[$id]);

        $user = \DB::table('user')->get();

        if (!$user) {
            return response()->json('NOT FOUND');
        }
        return response()->json($user);
    }

//    public function create(Request $request,$id,$name)
//    {
//        $input = $request->all();   //获取全部输入
//        print_r($input);
//        $user_name = $request->input('user_name','李四');  //获取指定的输入值
//        if (!$request->has('user_name')) {
//            echo 'user_name 为空';
//        }
//
//        // 不包含请求字串
////        $url = $request->url();
//
//        // 包含请求字串（请求字串如：`?id=2`）
////        $fullUrl = $request->fullUrl();
//
////        print_r($url);
//    }
}