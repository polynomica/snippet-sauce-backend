<?php

namespace App\Http\Controllers;

// use App\Models\Code;
// use App\Models\Users;
// use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function author_details($git_username)
    {
        //Fetch author details using github REST API
        $url_string = 'https://api.github.com/users/'.$git_username;
        $response = Http::get($url_string);
        $author_info = ["author_info" => array($response['login'], $response['bio'], $response['avatar_url'], $response['html_url'])];
        return response()->json($author_info);
    }

    public function author_login(Request $request)
    {
        $input = $request->all();
        $git_username = $input['git_username'];
        $password = $input['password'];

        //Authenciate user credentials
        $data = array(
            'username' => $git_username,
            'password' => $password
        );
        if (Auth::attempt($data)) {
            $url_string = 'https://api.github.com/users/'.$git_username;
            $response = Http::get($url_string);
            $author_info = ["author_info" => array($response['login'], $response['bio'], $response['avatar_url'], $response['html_url'])];
            return response()->json([$author_info]);
        } else {
            return response()->json(['alert' => 'Wrong Credentials, Please try again!']);
        }
    }
}
