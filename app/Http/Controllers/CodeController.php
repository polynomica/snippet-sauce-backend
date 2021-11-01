<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use App\Models\Code;
use App\Models\Lang;
use App\Models\News;
use App\Models\Allot;
use Illuminate\Http\Request;

class CodeController extends Controller
{
    public function create_snippet(Request $request)
    {
        $input = $request->all();
        $languages = Lang::all();
        $short_form = $languages[0]->short_form;
        $thumbnail = $languages[0]->thumbnail;
        $languages = $languages[0]->Languages;
        $timestamp = Carbon::now();
        $timestamp = $timestamp->toISOString();
        $input['snippet_language'] = "Python";
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
            $occupied = Allot::where('Language', $input['snippet_language'])->get();
            $occupied = $occupied[0]->allotted;
            $free_id = array_values(array_diff($valid, $occupied));
            if ($free_id == null) {
                return response()->json(['status' => false, 'message' => 'IDs have run out.']);
            }
            $rand_value = $free_id[mt_rand(0, (count($free_id) - 1))];
            Allot::where('Language', $input['snippet_language'])->push('allotted', $rand_value);
            $snippet_id = $string.sprintf("%06s", $rand_value);
            // $data = [
            //     [
            //         'snippet_id' => $snippet_id,
            //         'snippet_number' => $rand_value,
            //         'snippet_language' => 'snippet_language',
            //         'snippet_title' => 'snippet_title',
            //         'snippet_code' => 'snippet_code',
            //         'snippet_description' => 'snippet_description',
            //         'snippet_tag' => [1,2,3],
            //         'snippet_thumbnail' => $snippet_thumbnail,
            //         'snippet_timestamp' => $timestamp,
            //         'snippet_demo_url' => 'snippet_demo_url',
            //         'snippet_blog' => 'snippet_blog',
            //         'snippet_author' => 'git_username',
            //     ]
            // ];
            $data = [
                [
                    'snippet_id' => $snippet_id,
                    'snippet_number' => $rand_value,
                    'snippet_language' => $input['snippet_language'],
                    'snippet_title' => $input['snippet_title'],
                    'snippet_code' => $input['snippet_code'],
                    'snippet_description' => $input['snippet_description'],
                    'snippet_tag' => $input['snippet_tag'],
                    'snippet_thumbnail' => $snippet_thumbnail,
                    'snippet_timestamp' => $timestamp,
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
                    return response()->json(['status' => true, 'message' => 'Snippet added successfully.']);
                } else {
                    News::where('latest', 'exists', true)->push('latest', $data);
                    return response()->json(['status' => true, 'message' => 'Snippet added successfully.']);
                }
            } catch (Exception $error) {
                return response()->json(['status' => false, 'message' => 'Something went wrong, Please try again!']);
            }
        } else {
            return response()->json(['status' => false, 'message' => 'Language does not exists.']);
        }
    }

    public function update_snippet(Request $request)
    {
        $input = $request->all();
        $timestamp = Carbon::now();
        $timestamp = $timestamp->toISOString();
        $data = [
            [
                'snippet_id' => $input['snippet_id'],
                'snippet_language' => $input['snippet_language'],
                'snippet_title' => $input['snippet_title'],
                'snippet_code' => $input['snippet_code'],
                'snippet_description' => $input['snippet_description'],
                'snippet_tag' => $input['snippet_tag'],
                'snippet_thumbnail' => $input['snippet_thumbnail'],
                'snippet_timestamp' => $timestamp,
                'snippet_demo_url' => $input['snippet_demo_url'],
                'snippet_blog' => $input['snippet_blog'],
                'snippet_author' => $input['git_username'],
            ]
        ];
        try {
            Code::where('Language', $input['snippet_language'])->where('Snippets', [ ['snippet_id', $input['snippet_id']] ])->update($data);
            return response()->json(['status' => true, 'message' => 'Snippet updated successfully.']);
        } catch (Exception $error) {
            return response()->json(['status' => false, 'message' => 'Something went wrong, Please try again!']);
        }
    }

    public function delete_snippet($snippet_id)
    {
        $lang_code = substr($snippet_id, 0, 3);
        $id = (int)substr($snippet_id, 3);
        $code_language = '';
        $snippet_index = '';
        $data = Lang::all();
        $latest = News::all();
        $lang = $data[0]->short_form;
        $latest = $latest[0]->latest;
        for ($i=0; $i < count($lang); $i++) {
            foreach ($lang[$i] as $key => $value) {
                if ($value == $lang_code) {
                    $code_language = $key;
                }
            }
        }
        $search_response = Code::where('Language', $code_language)->get();
        $temp = 0;
        if (count($search_response) == 0) {
            return response()->json(['alert' => 'No snippet found, check your sauce!']);
        } else {
            $search_response = $search_response[0]->Snippets;
            for ($i=0; $i < count($search_response); $i++) {
                foreach ($search_response[$i] as $key => $value) {
                    if ($key == 'snippet_number') {
                        if ($value == $id) {
                            $snippet_index = $i;
                            $temp = 1;
                            break;
                        }
                    }
                }
                if ($temp == 1) {
                    break;
                }
            }
            unset($search_response[$snippet_index]);
            $search_response = array_values($search_response);
            Code::where('Language', $code_language)->update(['Snippets' => $search_response]);
        }
        for ($i=0; $i < count($latest); $i++) {
            if ($latest[$i]['snippet_number'] == $id) {
                unset($latest[$i]);
                $latest = array_values($latest);
                News::where('latest', 'exists', true)->update(['latest' => $latest]);
                break;
            }
        }
        try {
            Allot::where('Language', $code_language)->pull('allotted', $id);
            return response()->json(['status' => true, 'message' => 'Snippet deleted successfully.']);
        } catch (Exception $error) {
            return response()->json(['status' => false, 'message' => 'Something went wrong, Please try again!']);
        }
    }
}
