<?php

namespace App\Http\Controllers;

use App\Models\Code;
use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;

class CodeController extends Controller
{
    public function display()
    {
        // Code::create(['Languages' => [['Python' => [['id' => 1, 'code' => 'code1']]]]]);
        $data = Code::all();
        // $data = $data[0]->Languages[0]['Python'];
        // array_push($data, [
        //     'id' => 3,
        //     'code' => 'code3'
        // ]);
        return response()->json($data);
    }

    public function filter(Request $request)
    {
        // $input = $request->all();
        $language = $request->language;
        $language = 'h';
        // $snippet_title = 'h';
        $snippet_title = $request->snippet_title;
        $data = Code::all();
        if (empty($language) && empty($snippet_title)) {
            return response()->json($data);
        } elseif (empty($snippet_title)) {
            $data = $data[0]->Languages;
            return response()->json($data);
        } elseif (empty($language)) {
            return response()->json($data);
        } else {
            return response()->json($data);
        }
    }

    public function author_details($git_username)
    {
        $url_string = 'https://api.github.com/users/'.$git_username;
        $response = Http::get($url_string);
        $author_info = ["author_info" => array($response['login'], $response['bio'], $response['avatar_url'], $response['html_url'])];
        return response()->json($author_info);
    }

    public function author_login(Request $request)
    {
        $input = $request->all();

        //Validation
        $validator = Validator::make($input, [
            'git_username' => 'required',
            'password' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['alert' => 'Please fill out empty fields']);
        } else {
            $user_data = Users::all();
            $user_data = $user_data[0];
            $git_username = $input['git_username'];
            $password = $input['password'];
            if ($user_data->username == $git_username && $user_data->password == $password) {
                $url_string = 'https://api.github.com/users/'.$git_username;
                $response = Http::get($url_string);
                $author_info = ["author_info" => array($response['login'], $response['bio'], $response['avatar_url'], $response['html_url'])];
                return response()->json([$author_info]);
            } else {
                return response()->json(['alert' => 'Wrong Credentials, Please try again!']);
            }
        }
    }
}
