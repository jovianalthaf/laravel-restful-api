<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserLoginRequest;
use App\Http\Requests\UserRegisterRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{

    // IF METHOD POST DEFAULT STATUS RESPONSE IS 201 (CREATED)
    // IF WANT TO MANIPULATE STATUS RESPONSE USE JsonResponse,use Illuminate\Http\JsonResponse;
    // JsonResponse can change any status code response
    public function register(UserRegisterRequest $request): UserResource //memakai JsonResponse karena status response untuk create data 201 harus dimanipulasi
    {
        $data = $request->validated();

        // cek username exists
        if (User::where('username', $data['username'])->count() == 1) {
            throw new HttpResponseException(response([
                "errors" => [
                    "username" => [
                        "username already registered"
                    ]
                ]
            ], 400));
        }
        $data['password'] = Hash::make($data['password']);
        $user = User::create($data);
        // $user = new User($data);
        // $user->password = Hash::make($data['password']);
        // $user->save();

        // return (new UserResource($user))->response()->setStatusCode(200);
        return new UserResource($user); // default 201 cause using User::create new data
    }

    public function login(UserLoginRequest $request): UserResource //memakai UserResource karena status response secara default 200
    {
        $data = $request->validated();
        $user = User::where('username', $data['username'])->first();
        if (!$user || !Hash::check($data['password'], $user->password)) {
            throw new HttpResponseException(response([
                "errors" => [
                    "message" => [
                        "username or password wrong"
                    ]
                ]
            ], 401));
        }

        $user->token = Str::uuid()->toString();
        $user->save();
        // return (new UserResource($user))->response()->setStatusCode(201);
        return new UserResource($user); //output response code 200 cause getting data from database using User::where
    }

    public function get(Request $request): UserResource
    {
        // get user current login
        $user = Auth::user();
        return new UserResource($user);
    }
    public function update(UserUpdateRequest $request): UserResource
    {
        $request = $request->validated();

        $user = Auth::user();
        // if request has data to send
        //example,request has data so name changed, but if request is empty will be error so need use isset
        // {
        //     "name" : "baru" 
        // }
        if (isset($request['name'])) {
            $user->name = $request['name'];
        }
        if (isset($request['password'])) {
            $user->password = Hash::make($request['password']);
        }



        $user->save();
        return new UserResource($user);
    }
}
