<?php

namespace App\Http\Controllers;

use Throwable;
use Carbon\Carbon;
use App\Models\Code;
use App\Models\Lang;
use App\Models\News;
use App\Models\Allot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CodeController extends Controller
{
    public function extract_info($snippet_id)
    {
        // Extracting language short form and id from input
        $lang_code = substr($snippet_id, 0, 3);
        $id = (int)substr($snippet_id, 3);
        $code_language = '';
        $return_data = [];

        // Finding Language from its short form
        $data = Lang::select('short_form')->first();
        $lang = $data->short_form;
        dd(Lang::all()->toArray());
        foreach ($lang as $key => $value) {
            if ($value == $lang_code) {
                $code_language = $key;
                dd($key, $value);
            }
        }
        for ($i = 0; $i < count($lang); $i++) {
            foreach ($lang[$i] as $key => $value) {
            }
        }
        $return_data[0] = $id;
        $return_data[1] = $code_language;
        return $return_data;
    }

    public function update_latest($array)
    {
        $temp = News::all();
        $temp = $temp[0]->latest;
        $data = $array;

        // Adding in recently added data array
        if (count($temp) > 30) {
            array_shift($temp);
            array_push($temp, $data[0]);    // data = [ {} ], so to avoid adding nested object array we take only the object from the array
            try {
                News::where('latest', 'exists', true)->update([
                    'latest' => $temp
                ]);
                return response()->json([
                    'status' => true,
                    'message' => 'Snippet added successfully.'
                ]);
            } catch (Throwable $error) {
                return response()->json([
                    'status' => false,
                    'message' => 'Something went wrong, Please try again!'
                ]);
            }
        } else {
            try {
                News::where('latest', 'exists', true)->push('latest', $data);
                return response()->json([
                    'status' => true,
                    'message' => 'Snippet added successfully.'
                ]);
            } catch (Throwable $error) {
                return response()->json([
                    'status' => false,
                    'message' => 'Something went wrong, Please try again!'
                ]);
            }
        }
    }

    public function delete_latest($snippet_id)
    {
        // Find and delete the snippet, if it exists in the recently added array
        $latest = News::all();
        $latest = $latest[0]->latest;
        for ($i = 0; $i < count($latest); $i++) {
            if ($latest[$i]['snippet_id'] == $snippet_id) {
                unset($latest[$i]);
                $latest = array_values($latest);
                try {
                    News::where('latest', 'exists', true)->update([
                        'latest' => $latest
                    ]);
                    return response()->json([
                        'status' => true,
                        'message' => 'Snippet removed successfully.'
                    ]);
                } catch (Throwable $error) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Something went wrong, Please try again!'
                    ]);
                }
                break;
            }
        }
    }

    public function create_snippet(Request $request)
    {
        // Preparing input data
        $input = $request->all();
        $languages = Lang::select('short_form', 'thumbnail', 'Languages')->first();
        $short_form = $languages->short_form;
        $thumbnail = $languages->thumbnail;
        $languages = $languages->Languages;

        // Checking if language exists or not
        if (in_array($input['snippet_language'], $languages)) {
            $snippet_thumbnail = '';
            $string = '';

            foreach ($thumbnail as $key => $value) {
                if (array_key_first($value) == $input['snippet_language']) {
                    $snippet_thumbnail = $value[$input['snippet_language']];
                }
            }

            foreach ($short_form as $key => $value) {
                if (array_key_first($value) == $input['snippet_language']) {
                    $string = $value[$input['snippet_language']];
                }
            }

            $occupied = Allot::select('allotted')->where('Language', $input['snippet_language'])->first();
            $occupied = $occupied->allotted;
            $valid = range(0, 999999);
            $free_id = array_values(array_diff($valid, $occupied));

            // Checking if all IDs are allotted or not
            if ($free_id == null) {
                return response()->json(
                    [
                        'status' => false,
                        'message' => 'IDs have run out.'
                    ]
                );
            }

            // Preparing the input data
            $rand_value = $free_id[mt_rand(0, (count($free_id) - 1))];
            $snippet_id = $string . sprintf("%06s", $rand_value);
            $response = app(UserController::class)->author_details($input['snippet_author']);
            $response = $response->getData();
            if (!($response->status)) {
                return response()->json(
                    [
                        'status' => false,
                        'message' => 'Check github username.'
                    ]
                );
            }
            $author_pic = $response->author_avatar;
            $author_bio = $response->author_bio;
            $data = [
                'snippet_id' => $snippet_id,
                'snippet_language' => $input['snippet_language'],
                'snippet_title' => $input['snippet_title'],
                'snippet_code' => $input['snippet_code'],
                'snippet_description' => $input['snippet_description'],
                'snippet_tag' => $input['snippet_tag'],
                'snippet_seo' => $input['snippet_seo'],
                'snippet_thumbnail' => $snippet_thumbnail,
                'snippet_demo_url' => $input['snippet_demo_url'],
                'snippet_blog' => $input['snippet_blog'],
                'snippet_author' => $input['snippet_author'],
                'author_pic' => $author_pic,
                'author_bio' => $author_bio,
            ];

            try {
                Code::create($data);
                Allot::where('Language', $input['snippet_language'])->push('allotted', $rand_value);
                $status = ($this->update_latest($data))->getData();
                if ($status->status) {
                    return response()->json(
                        [
                            'status' => true,
                            'message' => 'Snippet added successfully.'
                        ]
                    );
                } else {
                    return response()->json(
                        [
                            'status' => false,
                            'message' => 'Something went wrong, Please try again!'
                        ]
                    );
                }
            } catch (Throwable $error) {
                return response()->json(
                    [
                        'status' => false,
                        'message' => 'Something went wrong, Please try again!'
                    ]
                );
            }
        } else {
            return response()->json(
                [
                    'status' => false,
                    'message' => 'Language does not exists.'
                ]
            );
        }
    }

    public function update_snippet(Request $request, $snippet_id)
    {
        $input = $request->all();
        $extract_data = $this->extract_info($snippet_id);
        $id = $extract_data[0];
        $code_language = $extract_data[1];

        // Checking if updated language is same as previous language
        if ($code_language != $input['snippet_language']) {
            // If updated language is not equal to previous language then port the updated snippet to new language after deleting its instance from previous language
            try {
                $data = ($this->create_snippet($request))->getData();    // getData() is used to get contents of json response
                if ($data->status) {
                    $this->delete_snippet($snippet_id);
                    return response()->json(
                        [
                            'status' => true,
                            'message' => 'Snippet updated successfully.'
                        ]
                    );
                } else {
                    return response()->json(
                        [
                            'status' => false,
                            'message' => 'Something went wrong, Please try again!'
                        ]
                    );
                }
            } catch (Throwable $error) {
                return response()->json(
                    [
                        'status' => false,
                        'message' => 'Something went wrong, Please try again!'
                    ]
                );
            }
        } else {
            // Preparing updated data
            $info = Code::where('Language', $input['snippet_language'])->get();
            $info = $info[0]->Snippets;
            $timestamp = Carbon::now();
            $timestamp = $timestamp->toISOString();
            $response = app(UserController::class)->author_details($input['snippet_author']);
            $response = $response->getData();
            if (!($response->status)) {
                return response()->json(
                    [
                        'status' => false,
                        'message' => 'Check github username.'
                    ]
                );
            }
            $thumb_response = app(LangController::class)->language_details($input['snippet_language']);
            $thumb_response = $thumb_response->getData();
            if (!($thumb_response->status)) {
                return response()->json(
                    [
                        'status' => false,
                        'message' => 'Check github username.'
                    ]
                );
            }
            $author_pic = $response->author_avatar;
            $author_bio = $response->author_bio;
            $snippet_thumbnail = $thumb_response->thumbnail;
            $updated_data = [
                [
                    'snippet_id' => $snippet_id,
                    'snippet_number' => $id,
                    'snippet_language' => $input['snippet_language'],
                    'snippet_title' => $input['snippet_title'],
                    'snippet_code' => $input['snippet_code'],
                    'snippet_description' => $input['snippet_description'],
                    'snippet_tag' => $input['snippet_tag'],
                    'snippet_seo' => $input['snippet_seo'],
                    'snippet_thumbnail' => $snippet_thumbnail,
                    'snippet_timestamp' => $timestamp,
                    'snippet_demo_url' => $input['snippet_demo_url'],
                    'snippet_blog' => $input['snippet_blog'],
                    'snippet_author' => $input['snippet_author'],
                    'author_pic' => $author_pic,
                    'author_bio' => $author_bio,
                ]
            ];

            $temp = 0;
            for ($i = 0; $i < count($info); $i++) {
                foreach ($info[$i] as $key => $value) {
                    if ($info[$i]['snippet_number'] == $id) {
                        unset($info[$i]);
                        $info[$i] = $updated_data[0];
                        $temp = 1;
                        break;
                    }
                }

                if ($temp == 1) {
                    break;
                }
            }

            try {
                Code::where('Language', $input['snippet_language'])->update(
                    [
                        'Snippets' => $info
                    ]
                );
                $status_1 = ($this->delete_latest($snippet_id))->getData();
                $status_2 = ($this->update_latest($updated_data))->getData();
                if ($status_1->status == false or $status_2->status == false) {
                    return response()->json(
                        [
                            'status' => false,
                            'message' => 'Something went wrong, Please try again!'
                        ]
                    );
                }
                return response()->json(
                    [
                        'status' => true,
                        'message' => 'Snippet updated successfully.'
                    ]
                );
            } catch (Throwable $error) {
                return response()->json(
                    [
                        'status' => false,
                        'message' => 'Something went wrong, Please try again!'
                    ]
                );
            }
        }
    }

    public function delete_snippet($snippet_id)
    {
        $this->extract_info($snippet_id = 'ccs854822');
        dd(123);
        try {
            $data = $this->extract_info($snippet_id);
            $id = $data[0];
            $code_language = $data[1];
            $status = ($this->delete_latest($snippet_id))->getData();
            if ($status->status == false) {
                return response()->json(
                    [
                        'status' => false,
                        'message' => 'Something went wrong, Please try again!'
                    ]
                );
            }

            // Free up the unique Id of the deleted snippet
            Allot::where('Language', $code_language)->pull('allotted', $id);
            Code::where('snippet_id', $snippet_id)->delete();
            return response()->json(
                [
                    'status' => true,
                    'message' => 'Snippet deleted successfully.'
                ]
            );
        } catch (Throwable $error) {
            return response()->json(
                [
                    'status' => false,
                    'message' => 'Something went wrong, Please try again!'
                ]
            );
        }
    }
}
