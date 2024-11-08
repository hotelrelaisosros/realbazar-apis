<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SocialLoginController extends Controller
{
    protected function googleLogin()
    {
        $validation = Validator::make(
            request()->all(),
            [
                'id' => 'required',
                'role' => 'required',
            ]
        );

        if ($validation->fails()) {
            return response(['status' => false, 'Message' => $validation->errors()]);
        }
        $role = Role::where('name', request()->role)->first();
        if (empty($role)) return response()->json(['status' => false, 'Message' => 'Role not found']);
        $existingAccount = User::where('account_type', 'google')->where('account_id', request()->id)->where('role_id', $role->id)->first();
        if ($existingAccount != null) {
            if (auth()->loginUsingId($existingAccount->id)) {
                $user = auth()->user()->load('role');
                $token = $user->createToken('token')->accessToken;
                $user->device_token = request()->token;
                // $user->email =  request()->email;
                // $user->account_id =  request()->id;
                // $user->family_name =  request()->familyName;
                // $user->given_name =  request()->givenName;
                // $user->first_name =  request()->first_name;
                // $user->last_name =  request()->last_name;
                // $user->username =  request()->name;
                // if (isset(request()->phone)) $user->phone =  request()->phone;
                if (isset(request()->photo)) {
                    $url = request()->photo;
                    $contents = file_get_contents($url);
                    $name = 'image/Image-' . time() . "-" . rand() . ".png";
                    Storage::disk('public')->put($name, $contents);
                    $user->image =  $name;
                }
                $user->save();
                return response()->json(["status" => true, "Message" => 'Login Successfull', 'token' => $token, 'user' => $user], 200);
            } else {
                return response()->json(['status' => false, "Message" => 'Invalid Credentials!']);
            }
        }

        $socailLogin = new User();
        $socailLogin->role_id =  $role->id;
        $socailLogin->account_type =  'google';
        $socailLogin->email =  request()->email;
        $socailLogin->account_id =  request()->id;
        $socailLogin->family_name =  request()->familyName;
        $socailLogin->given_name =  request()->givenName;
        $socailLogin->first_name =  request()->first_name;
        $socailLogin->last_name =  request()->last_name;
        $socailLogin->username =  request()->name;
        if (isset(request()->phone)) $socailLogin->phone =  request()->phone;
        if (isset(request()->photo)) {
            $url = request()->photo;
            $contents = file_get_contents($url);
            $name = 'image/Image-' . time() . "-" . rand() . ".png";
            Storage::disk('public')->put($name, $contents);
            $socailLogin->image =  $name;
        }
        if ($socailLogin->save()) {
            if (auth()->loginUsingId($socailLogin->id)) {
                $user = auth()->user()->load('role');
                $token = $user->createToken('token')->accessToken;
                $user->device_token = request()->token;
                $user->save();
                return response()->json(["status" => true, "Message" => 'Login Successfull',  'token' => $token, 'user' => $user], 200);
            } else {
                return response()->json(['status' => false, "Message" => 'Invalid Credentials!']);
            }
        }
    }

    protected function facebookLogin()
    {
        $validation = Validator::make(
            request()->all(),
            [
                'id' => 'required',
                'role' => 'required',
            ]
        );

        if ($validation->fails()) {
            return response(['status' => false, 'Message' => $validation->errors()]);
        }
        $role = Role::where('name', request()->role)->first();
        if (empty($role)) return response()->json(['status' => false, 'Message' => 'Role not found']);
        $existingAccount = User::where('account_type', 'facebook')->where('account_id', request()->id)->where('role_id', $role->id)->first();
        if ($existingAccount != null) {
            if (auth()->loginUsingId($existingAccount->id)) {
                $user = auth()->user()->load('role');
                $token = $user->createToken('token')->accessToken;
                $user->device_token = request()->token;
                // $user->email =  request()->email;
                // $user->account_id =  request()->id;
                // $user->family_name =  request()->familyName;
                // $user->given_name =  request()->givenName;
                // $user->first_name =  request()->first_name;
                // $user->last_name =  request()->last_name;
                // $user->username =  request()->name;
                // if (isset(request()->phone)) $user->phone =  request()->phone;
                if (isset(request()->photo)) {
                    $url = request()->photo;
                    $contents = file_get_contents($url);
                    $name = 'image/Image-' . time() . "-" . rand() . ".png";
                    Storage::disk('public')->put($name, $contents);
                    $user->image =  $name;
                }
                $user->save();
                return response()->json(["status" => true, "Message" => 'Login Successfull', 'token' => $token, 'user' => $user], 200);
            } else {
                return response()->json(['status' => false, "Message" => 'Invalid Credentials!']);
            }
        }

        $socailLogin = new User();
        $socailLogin->role_id =  $role->id;
        $socailLogin->account_type =  'facebook';
        $socailLogin->email =  request()->email;
        $socailLogin->account_id =  request()->id;
        $socailLogin->family_name =  request()->familyName;
        $socailLogin->given_name =  request()->givenName;
        $socailLogin->first_name =  request()->first_name;
        $socailLogin->last_name =  request()->last_name;
        $socailLogin->username =  request()->name;
        if (isset(request()->phone)) $socailLogin->phone =  request()->phone;
        if (isset(request()->photo)) {
            $url = request()->photo;
            $contents = file_get_contents($url);
            $name = 'image/Image-' . time() . "-" . rand() . ".png";
            Storage::disk('public')->put($name, $contents);
            $socailLogin->image =  $name;
        }
        if ($socailLogin->save()) {
            if (auth()->loginUsingId($socailLogin->id)) {
                $user = auth()->user()->load('role');
                $token = $user->createToken('token')->accessToken;
                $user->device_token = request()->token;
                $user->save();
                return response()->json(["status" => true, "message" => 'Login Successfull', 'token' => $token, 'user' => $user], 200);
            } else {
                return response()->json(['status' => false, "Message" => 'Invalid Credentials!']);
            }
        }
    }

    protected function appleLogin()
    {
        $validation = Validator::make(
            request()->all(),
            [
                'id' => 'required',
                'role' => 'required',
            ]
        );

        if ($validation->fails()) {
            return response(['status' => false, 'Message' => $validation->errors()]);
        }
        $role = Role::where('name', request()->role)->first();
        if (empty($role)) return response()->json(['status' => false, 'Message' => 'Role not found']);
        $existingAccount = User::where('account_type', 'apple')->where('account_id', request()->id)->where('role_id', $role->id)->first();
        if ($existingAccount != null) {
            if (auth()->loginUsingId($existingAccount->id)) {
                $user = auth()->user()->load('role');
                $token = $user->createToken('token')->accessToken;
                $user->device_token = request()->token;
                // $user->email =  request()->email;
                // $user->account_id =  request()->id;
                // $user->family_name =  request()->familyName;
                // $user->given_name =  request()->givenName;
                // $user->first_name =  request()->first_name;
                // $user->last_name =  request()->last_name;
                // $user->username =  request()->name;
                // if (isset(request()->phone)) $user->phone =  request()->phone;
                if (isset(request()->photo)) {
                    $url = request()->photo;
                    $contents = file_get_contents($url);
                    $name = 'image/Image-' . time() . "-" . rand() . ".png";
                    Storage::disk('public')->put($name, $contents);
                    $user->image =  $name;
                }
                $user->save();
                return response()->json(["status" => true, "Message" => 'Login Successfull', 'token' => $token, 'user' => $user], 200);
            } else {
                return response()->json(['status' => false, "Message" => 'Invalid Credentials!']);
            }
        }

        $socailLogin = new User();
        $socailLogin->role_id =  $role->id;
        $socailLogin->account_type =  'apple';
        $socailLogin->email =  request()->email;
        $socailLogin->account_id =  request()->id;
        $socailLogin->family_name =  request()->familyName;
        $socailLogin->given_name =  request()->givenName;
        $socailLogin->first_name =  request()->first_name;
        $socailLogin->last_name =  request()->last_name;
        $socailLogin->username =  request()->name;
        if (isset(request()->phone)) $socailLogin->phone =  request()->phone;
        if (isset(request()->photo)) {
            $url = request()->photo;
            $contents = file_get_contents($url);
            $name = 'image/Image-' . time() . "-" . rand() . ".png";
            Storage::disk('public')->put($name, $contents);
            $socailLogin->image =  $name;
        }
        if ($socailLogin->save()) {
            if (auth()->loginUsingId($socailLogin->id)) {
                $user = auth()->user()->load('role');
                $token = $user->createToken('token')->accessToken;
                $user->device_token = request()->token;
                $user->save();
                return response()->json(["status" => true, "message" => 'Login Successfull', 'token' => $token, 'user' => $user], 200);
            } else {
                return response()->json(['status' => false, "Message" => 'Invalid Credentials!']);
            }
        }
    }
}
