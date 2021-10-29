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

    public function add_language(Request $request)
    {
        $input = $request->all();
        $lang_thumbnail = base64_encode(file_get_contents($request->file('thumbnail')));
        $create_data = [
            'Language' => $input['language_name'],
            'Snippets' => [],
        ];
        $allotted_id = [
            'Language' => $input['language_name'],
            'allotted' => [],
        ];
        $short_form = [
            $input['language_name'] => $input['short_form'],
        ];
        $thumbnail = [
            $input['language_name'] => $lang_thumbnail,
        ];
        try {
            Lang::where('Languages', 'exists', true)->push('Languages', $input['language_name']);
            Lang::where('Languages', 'exists', true)->push('short_form', $short_form);
            Lang::where('Languages', 'exists', true)->push('thumbnail', $thumbnail);
            Code::create($create_data);
            Allot::create($allotted_id);
            return true;
        } catch (Exception $error) {
            return response()->json(['alert' => 'Something went wrong, Please try again!']);
        }
    }

    public function create_snippet(Request $request)
    {
        $input = $request->all();
        $languages = Lang::all();
        $short_form = $languages[0]->short_form;
        $thumbnail = $languages[0]->thumbnail;
        $languages = $languages[0]->Languages;
        $valid = [];
        for ($i=0; $i <= 999999; $i++) {
            array_push($valid, $i);
        }
        if (in_array($input['snippet_language'], $languages)) {
            $snippet_thumbnail = '';
            $string = '';
            for ($i=0; $i < count($thumbnail); $i++) {
                foreach ($thumbnail[$i] as $key => $value) {
                    if ($key == $input['snippet_language']) {
                        $snippet_thumbnail = $value;
                    }
                }
            }
            for ($i=0; $i < count($short_form); $i++) {
                foreach ($short_form[$i] as $key => $value) {
                    if ($key == $input['snippet_language']) {
                        $string = $value;
                    }
                }
            }
            $occupied = Allot::where('Language', $input['snippet_language']);
            $occupied = $occupied[0]->allotted;
            $free_id = array_values(array_diff($valid, $occupied));
            if ($free_id == null) {
                return response()->json(['alert' => 'IDs have run out.']);
            }
            $rand_value = $free_id[mt_rand(0, (count($free_id) - 1))];
            Allot::where('Language', $input['snippet_language'])->push('allotted', $rand_value);
            $snippet_id = $string.sprintf("%06s", $rand_value);
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
                return response()->json(['alert' => 'Something went wrong, Please try again!']);
            }
        } else {
            return response()->json(['alert' => 'Language does not exists.']);
        }
    }

    public function update_snippet(Request $request)
    {
        $input = $request->all();
        $data = [
            [
                'snippet_id' => $input['snippet_id'],
                'snippet_language' => $input['snippet_language'],
                'snippet_title' => $input['snippet_title'],
                'snippet_code' => $input['snippet_code'],
                'snippet_description' => $input['snippet_description'],
                'snippet_tag' => $input['snippet_tag'],
                'snippet_thumbnail' => $input['snippet_thumbnail'],
                'snippet_timestamp' => new DateTime(),
                'snippet_demo_url' => $input['snippet_demo_url'],
                'snippet_blog' => $input['snippet_blog'],
                'snippet_author' => $input['git_username'],
            ]
        ];
        try {
            Code::where('Language', $input['snippet_language'])->where('Snippets', [ ['snippet_id', $input['snippet_id']] ])->update($data);
            return true;
        } catch (Exception $error) {
            return response()->json(['alert' => 'Something went wrong, Please try again!']);
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
}
