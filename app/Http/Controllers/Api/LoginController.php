<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;


class LoginController extends Controller {
    public function login(Request $request) {
        $validator = Validator::make($request->all(), [
            'avatar' => 'required',
            'name' => 'required',
            'type' => 'required',
            'open_id' => 'required',
            'email' => 'max:50',
            'phone' => 'max:30',
          ]);
        //   if ($validator->fails()) {
        //     return ["code" => -1, "data" => "no valid data", "msg" => $validator->errors()->first()];
        //   } else {
        //     return ["code" => 1, "data" => "valid data", "msg" => "success"];
        //   }

          $validated = $validator->validated();

          $map=[];
          $map["type"] = $validated["type"];
          $map["open_id"] = $validated["open_id"];
    
          $result = DB::table("users")->select("avatar","name","description","type","token","access_token","online")->where($map)->first();
          if(empty($result)){
            $validated["token"] = md5(uniqid().rand(10000,99999));
            $validated["created_at"] = Carbon::now();
            $validated["access_token"] = md5(uniqid().rand(1000000,9999999));
            $validated["expire_date"] = Carbon::now()->addDays(30);
            $user_id = DB::table("users")->insertGetId($validated);
            $user_result = DB::table("users")->select("avatar","name","description","type","access_token","token","online")->where("id","=",$user_id)->first();
            return ["code" => 0, "data" => $user_result, "msg" => "user has been created"];
          } else {
            $access_token = md5(uniqid().rand(1000000,9999999));
            $expire_date = Carbon::now()->addDays(30);
            DB::table("users")->where($map)->update(
                [
                    "access_token"=>$access_token,
                    "expire_date"=>$expire_date
                ]
            );
            $result->access_token= $access_token;
            return ["code" => 1, "data" =>$result, "msg" => "user information updated"];
          }
    }
}