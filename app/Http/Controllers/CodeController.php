<?php

namespace App\Http\Controllers;

use DateTime;
use Exception;
use App\Models\Code;
use App\Models\Lang;
use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Auth;
// use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class CodeController extends Controller
{
    public function display()
    {
        $data = [
            'Languages' => 'Hello',
            'Snippets' => [
                ['id' => '1'],
                ['id' => '2']
            ],
        ];
        // Code::where('Languages', 'Hello World')->push('Snippets', [$data]);
        // Code::where('Languages', 'Hello')->push('Snippets', [
        //     ['id' => '3'],
        //     ['id' => '4']
        // ]);
        // Code::create($data);
        // $data = Lang::all();
        // return $data[0]->Languages;
        return response()->json($data);
    }

    public function create_snippet(Request $request)
    {
        $input = $request->all();
        //Validation
        $validator = Validator::make($input, [
            'git_username' => 'required',
            'snippet_title' => 'required',
            'snippet_code' => 'required',
            'snippet_language' => 'required',
            'snippet_description' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['alert' => 'Please fill out empty fields']);
        } else {
            $languages = Lang::all();
            $languages = $languages[0]->Languages;
            $data = [
                [
                    'snippet_id' => 0, // Add logic for generating 6 digit id, has to do with assiging array index as id and when it gets deleted assign the updated array index
                    'snippet_language' => $input['snippet_language'],
                    'snippet_title' => $input['snippet_title'],
                    'snippet_code' => $input['snippet_code'],
                    'snippet_description' => $input['snippet_description'],
                    'snippet_tag' => $input['snippet_tag'],
                    'snippet_thumbnail' => base64_encode(file_get_contents($request->file('snippet_thumbnail'))),
                    'snippet_timestamp' => new DateTime(),
                    'snippet_demo_url' => $input['snippet_demo_url'],
                    'snippet_blog' => $input['snippet_blog'],
                    'snippet_author' => $input['git_username'],
                ]
            ];
            // $url_string = 'https://api.github.com/users/'.$snippet_author;
            // $response = Http::get($url_string);
            // $author_info = ["author_info" => array($response['login'], $response['bio'], $response['avatar_url'], $response['html_url'])];
            if (in_array($input['snippet_language'], $languages)) {
                try {
                    Code::where('Language', $input['snippet_language'])->push('Snippets', [$data]);
                    return true;
                } catch (Exception $error) {
                    return response()->json(['alert' => 'Wrong Credentials, Please try again!']);
                }
            } else {
                try {
                    $create_data = [
                        'Language' => $input['snippet_language'],
                        'Snippets' => [$data],
                    ];
                    Code::create($create_data);
                    return true;
                } catch (Exception $error) {
                    return response()->json(['alert' => 'Wrong Credentials, Please try again!']);
                }
            }
        }
    }

    public function update_snippet()
    {
        # code
    }

    public function insert_snippet()
    {
        # code...
    }

    public function delete_snippet($language, $snippet_id)
    {
        // $snippet_id = 1;
        // $language = 'Hello';
        // Fetch all id of the language into an array, then locate the snippet_id to be deleted in array and make its value -5 and decrement all the ids after it by 1 and update the respective ids
        // Code::where('Language', $language)->decrement('Snippets', $snippet_id + 5,[ ['id' => $snippet_id] ]);
        // $data = Code::where('Language', $language)->where('Snippets', [ ['u_id' => $snippet_id] ])->get();
        // return $data;
        try {
            Code::where('Language', $language)->pull('Snippets', [ ['snippet_id' => $snippet_id] ]);
            return true;
        } catch (Exception $error) {
            return response()->json(['alert' => 'Something went wrong, Please try again!']);
        }
    }

    // public function filter(Request $request)
    // {
    //     // $input = $request->all();
    //     $language = $request->language;
    //     $language = 'h';
    //     // $snippet_title = 'h';
    //     $snippet_title = $request->snippet_title;
    //     $data = Code::all();
    //     if (empty($language) && empty($snippet_title)) {
    //         return response()->json($data);
    //     } elseif (empty($snippet_title)) {
    //         $data = $data[0]->Languages;
    //         return response()->json($data);
    //     } elseif (empty($language)) {
    //         return response()->json($data);
    //     } else {
    //         return response()->json($data);
    //     }
    // }
}
