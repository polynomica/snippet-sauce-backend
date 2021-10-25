<?php

namespace App\Http\Controllers;

use DateTime;
use Exception;
use App\Models\Code;
use App\Models\Lang;
use App\Models\News;
use App\Models\Allot;
use Illuminate\Http\Request;

class CodeController extends Controller
{
    public function display()
    {
        $data = News::all();
        $data = $data[0]->latest;
        $data = array_reverse($data);
        return response()->json($data);
    }

    public function create_snippet(Request $request)
    {
        $input = $request->all();
        $languages = Lang::all();
        $languages = $languages[0]->Languages;
        if (in_array($input['snippet_language'], $languages)) {
            $snippet_thumbnail = base64_encode($languages[0]->thumbnail[$input['snippet_language']]);
        } else {
            $snippet_thumbnail = base64_encode($languages[0]->thumbnail['default']);
        }
        $valid = [];
        for ($i=0; $i <= 999999; $i++) {
            array_push($valid, $i);
        }
        //use mt_rand fun it is faster, also id array minus alloted id array then select randomn index from it and make it string using sprintf function
        $rand_value = rand(0, 999999);
        $rand_value = sprintf("%06s", $rand_value);
        // echo $rand_value;
        $occupied = Allot::where('Language', $input['snippet_language']);
        $occupied = $occupied[0]->allotted;
        $snippet_id = "";
        $data = [
            [
                'snippet_id' => $snippet_id,
                'snippet_language' => $input['snippet_language'],
                'snippet_title' => $input['snippet_title'],
                'snippet_code' => $input['snippet_code'],
                'snippet_description' => $input['snippet_description'],
                'snippet_tag' => $input['snippet_tag'],
                'snippet_thumbnail' => $snippet_thumbnail,
                'snippet_timestamp' => new DateTime(),
                'snippet_demo_url' => $input['snippet_demo_url'],
                'snippet_blog' => $input['snippet_blog'],
                'snippet_author' => $input['git_username'],
            ]
        ];
        if (in_array($input['snippet_language'], $languages)) {
            try {
                Code::where('Language', $input['snippet_language'])->push('Snippets', $data);
                $temp = News::all();
                $temp = $temp[0]->latest;
                if (count($temp) > 30) {
                    array_shift($temp);
                    array_push($temp, $data);
                    News::where('latest', 'exists', true)->update('latest', $temp);
                    return true;
                } else {
                    News::where('latest', 'exists', true)->push('latest', $data);
                    return true;
                }
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
                Lang::where('Languages', 'exists', true)->push($input['snippet_language']);
                News::where('latest', 'exists', true)->push($data);
                return true;
            } catch (Exception $error) {
                return response()->json(['alert' => 'Wrong Credentials, Please try again!']);
            }
        }
    }

    public function update_snippet(Request $request)
    {
        $input = $request->all();
        $snippet_id = "";
        $data = [
            [
                'snippet_id' => $snippet_id,
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
        try {
            Code::where('Language', $input['snippet_language'])->where('Snippets', [ ['snippet_id', $snippet_id] ])->update($data);
            return true;
        } catch (Exception $error) {
            return response()->json(['alert' => 'Wrong Credentials, Please try again!']);
        }
    }

    public function delete_snippet($language, $snippet_id)
    {
        try {
            Code::where('Language', $language)->pull('Snippets', [ ['snippet_id' => $snippet_id] ]);
            News::where('latest', 'exists', true)->pull('latest', [ ['snippet_id' => $snippet_id] ]);
            return true;
        } catch (Exception $error) {
            return response()->json(['alert' => 'Something went wrong, Please try again!']);
        }
    }

    // Merge short_form, thumbnail, etc. in one function. Basically make a new singular function to add a new language and remove redundant conditions from create_snippet for optimized and better performance
    public function short_form(Request $request)
    {
        $input = $request->all();
        Lang::where('short_form', [ $input['snippet_language'], $input['snippet_language'] ])->update([ $input['snippet_language'] => $input['short_form'] ]);
        return true;
    }

    public function thumbnail(Request $request)
    {
        $thumbnail = base64_encode(file_get_contents($request->file('snippet_thumbnail')));
        Lang::where('thumbnail', 'exists', true)->push($request->language, $thumbnail);
        return true;
    }
}
