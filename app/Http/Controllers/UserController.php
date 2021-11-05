<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class UserController extends Controller
{
    public function author_details($git_username)
    {
        //Fetch author details using github REST API
        $url_string = 'https://api.github.com/users/'.$git_username;
        $response = Http::get($url_string);
        $author_info = [
            $response['login'],
            $response['bio'],
            $response['avatar_url'],
            $response['html_url']
        ];

        try {
            return response()->json([
                'status' => true,
                'author_username' => $author_info[0],
                'author_bio' => $author_info[1],
                'author_avatar' => $author_info[2],
                'author_url' => $author_info[3]
            ]);
        } catch (Exception $error) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong, Please try again!'
            ]);
        }
    }

    public function admin_login(Request $request)
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
            $admin_info = [
                $response['login'],
                $response['bio'],
                $response['avatar_url'],
                $response['html_url']
            ];
            return response()->json([
                'status' => true,
                'admin_username' => $admin_info[0],
                'admin_bio' => $admin_info[1],
                'admin_avatar' => $admin_info[2],
                'admin_url' => $admin_info[3],
                'logged_in' => true,
                'role' => 'admin'
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Wrong Credentials, Please try again!'
            ]);
        }
    }
}
